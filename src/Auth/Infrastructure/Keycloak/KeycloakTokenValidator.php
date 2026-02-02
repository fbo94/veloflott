<?php

declare(strict_types=1);

namespace Auth\Infrastructure\Keycloak;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Exception;

/**
 * Valide les tokens JWT émis par Keycloak.
 */
final class KeycloakTokenValidator
{
    private const JWKS_CACHE_KEY = 'keycloak_jwks';
    private const JWKS_CACHE_TTL = 3600; // 1 heure

    public function __construct(
        private readonly string $keycloakUrl,
        private readonly string $realm,
    ) {}

    /**
     * Valide le token et retourne le payload décodé.
     *
     * @return object|null Payload du token ou null si invalide
     */
    public function validate(string $token): ?object
    {
        try {
            $keys = $this->getPublicKeys();
            $payload = JWT::decode($token, $keys);

            // Vérifications supplémentaires
            $this->validateClaims($payload);

            return $payload;
        } catch (Exception $e) {
            // Log pour debug, mais ne pas exposer l'erreur
            report($e);
            return null;
        }
    }

    /**
     * Récupère les clés publiques JWKS depuis Keycloak.
     * Les clés sont mises en cache pour éviter les appels répétés.
     *
     * @return array<string, Key>
     */
    private function getPublicKeys(): array
    {
        return Cache::remember(
            self::JWKS_CACHE_KEY,
            self::JWKS_CACHE_TTL,
            function () {
                $url = "{$this->keycloakUrl}/realms/{$this->realm}/protocol/openid-connect/certs";

                $response = Http::timeout(10)->get($url);

                if (!$response->successful()) {
                    throw new Exception("Failed to fetch Keycloak JWKS: {$response->status()}");
                }

                return JWK::parseKeySet($response->json());
            }
        );
    }

    /**
     * Valide les claims du token.
     */
    private function validateClaims(object $payload): void
    {
        // Vérifier l'expiration (déjà fait par JWT::decode, mais double check)
        if (isset($payload->exp) && $payload->exp < time()) {
            throw new Exception('Token expired');
        }

        // Vérifier l'issuer
        $expectedIssuer = "{$this->keycloakUrl}/realms/{$this->realm}";
        if (!isset($payload->iss) || $payload->iss !== $expectedIssuer) {
            throw new Exception('Invalid issuer');
        }
    }

    /**
     * Invalide le cache des clés JWKS.
     * Utile si les clés ont été rotées côté Keycloak.
     */
    public function clearCache(): void
    {
        Cache::forget(self::JWKS_CACHE_KEY);
    }
}
