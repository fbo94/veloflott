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
        // Cache uniquement les données JSON brutes, pas les objets Key
        $jwksData = Cache::remember(
            self::JWKS_CACHE_KEY,
            self::JWKS_CACHE_TTL,
            function () {
                $url = "{$this->keycloakUrl}/realms/{$this->realm}/protocol/openid-connect/certs";

                try {
                    $response = Http::timeout(10)
                        ->retry(2, 200)
                        ->withOptions(['verify' => $this->shouldVerifyTls()])
                        ->get($url);
                } catch (\Throwable $e) {
                    throw new Exception("Failed to fetch Keycloak JWKS: {$e->getMessage()}", 0, $e);
                }

                if (!$response->successful()) {
                    throw new Exception("Failed to fetch Keycloak JWKS: {$response->status()}");
                }

                return $response->json();
            }
        );

        // Parse le JSON en objets Key après récupération du cache
        // Les objets Key contiennent des ressources OpenSSL non-sérialisables
        return JWK::parseKeySet($jwksData);
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

        // Vérifier l'issuer - accepter plusieurs variantes d'URL
        $internalIssuer = "{$this->keycloakUrl}/realms/{$this->realm}";
        $publicUrl = env('KEYCLOAK_PUBLIC_URL', $this->keycloakUrl);
        $publicIssuer = "{$publicUrl}/realms/{$this->realm}";

        // Accepter aussi la variante sans port (Keycloak peut omettre le port standard 443)
        $publicIssuerNoPort = "https://keycloak.localhost/realms/{$this->realm}";

        $validIssuers = [$internalIssuer, $publicIssuer, $publicIssuerNoPort];

        if (!isset($payload->iss) || !in_array($payload->iss, $validIssuers, true)) {
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

    /**
     * Indique si la vérification TLS doit être effectuée pour Keycloak.
     * Permet de désactiver la vérification en environnement de dev (certificat auto-signé).
     */
    private function shouldVerifyTls(): bool
    {
        // KEYCLOAK_TLS_VERIFY=false pour désactiver la vérification en dev
        $verify = env('KEYCLOAK_TLS_VERIFY', true);

        // Si l'URL Keycloak est en HTTP, aucune vérification TLS n'est nécessaire
        if (str_starts_with(strtolower($this->keycloakUrl), 'http://')) {
            return false;
        }

        return (bool) $verify;
    }
}
