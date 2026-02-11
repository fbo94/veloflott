<?php

declare(strict_types=1);

namespace Auth\Infrastructure\Keycloak;

use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Valide les tokens JWT émis par Keycloak.
 */
final class KeycloakTokenValidator
{
    private const string JWKS_CACHE_KEY = 'keycloak_jwks';

    private const int JWKS_CACHE_TTL = 3600; // 1 heure

    public function __construct(
        private readonly string $keycloakUrl,
        private readonly string $keycloakUrlInternal,
        private readonly string $realm,
    ) {
    }

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
            \Log::error('JWT token validation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
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
                // Utiliser l'URL interne pour récupérer les JWKS depuis le backend
                $url = "{$this->keycloakUrlInternal}/realms/{$this->realm}/protocol/openid-connect/certs";

                \Log::debug('Fetching Keycloak JWKS', ['url' => $url]);

                try {
                    $response = Http::timeout(10)
                        ->retry(2, 200)
                        ->withOptions(['verify' => $this->shouldVerifyTls($this->keycloakUrlInternal)])
                        ->get($url);
                } catch (\Throwable $e) {
                    \Log::error('Failed to fetch Keycloak JWKS', [
                        'url' => $url,
                        'error' => $e->getMessage(),
                    ]);

                    throw new Exception("Failed to fetch Keycloak JWKS: {$e->getMessage()}", 0, $e);
                }

                if (!$response->successful()) {
                    \Log::error('Failed to fetch Keycloak JWKS - HTTP error', [
                        'url' => $url,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

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
        $validIssuers = [];

        // URL interne (depuis config)
        $validIssuers[] = "{$this->keycloakUrl}/realms/{$this->realm}";

        // URL publique (depuis .env KEYCLOAK_PUBLIC_URL)
        $publicUrl = env('KEYCLOAK_PUBLIC_URL');
        if ($publicUrl) {
            $validIssuers[] = "{$publicUrl}/realms/{$this->realm}";
        }

        // URL interne Docker (depuis .env KEYCLOAK_INTERNAL_URL)
        $internalUrl = env('KEYCLOAK_INTERNAL_URL');
        if ($internalUrl) {
            $validIssuers[] = "{$internalUrl}/realms/{$this->realm}";
        }

        // Variantes sans port (Keycloak peut omettre le port standard 443)
        $baseUrl = parse_url($this->keycloakUrl, PHP_URL_SCHEME) . '://' . parse_url($this->keycloakUrl, PHP_URL_HOST);
        $validIssuers[] = "{$baseUrl}/realms/{$this->realm}";

        // Dédupliquer les issuers
        $validIssuers = array_unique($validIssuers);

        if (!isset($payload->iss)) {
            throw new Exception('Missing issuer claim');
        }

        if (!in_array($payload->iss, $validIssuers, true)) {
            \Log::error('Invalid JWT issuer', [
                'received' => $payload->iss,
                'expected' => $validIssuers,
            ]);

            throw new Exception("Invalid issuer: {$payload->iss}");
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
    private function shouldVerifyTls(string $url): bool
    {
        // KEYCLOAK_TLS_VERIFY=false pour désactiver la vérification en dev
        $verify = env('KEYCLOAK_TLS_VERIFY', true);

        // Si l'URL Keycloak est en HTTP, aucune vérification TLS n'est nécessaire
        if (str_starts_with(strtolower($url), 'http://')) {
            return false;
        }

        return (bool) $verify;
    }
}
