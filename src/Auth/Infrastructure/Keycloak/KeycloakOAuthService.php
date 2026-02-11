<?php

declare(strict_types=1);

namespace Auth\Infrastructure\Keycloak;

use Exception;
use Illuminate\Support\Facades\Http;

/**
 * Service pour gérer le flow OAuth2 avec Keycloak.
 */
final class KeycloakOAuthService
{
    public function __construct(
        private readonly string $keycloakUrl,
        private readonly string $keycloakUrlPrivate,
        private readonly string $realm,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
    ) {
    }

    /**
     * Génère l'URL d'autorisation Keycloak pour démarrer le flow OAuth2 avec PKCE.
     */
    public function getAuthorizationUrl(string $state, string $codeChallenge, string $codeChallengeMethod): string
    {
        $params = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => $codeChallengeMethod,
        ]);

        return "{$this->keycloakUrl}/realms/{$this->realm}/protocol/openid-connect/auth?{$params}";
    }

    /**
     * Échange le code d'autorisation contre un access token avec PKCE.
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int, token_type: string}
     *
     * @throws Exception
     */
    public function exchangeCodeForToken(string $code, string $codeVerifier): array
    {
        $url = "{$this->keycloakUrlPrivate}/realms/{$this->realm}/protocol/openid-connect/token";

        $payload = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'code_verifier' => $codeVerifier,
        ];

        \Log::debug('Exchanging OAuth2 code for token with PKCE', [
            'url' => $url,
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
        ]);

        $response = Http::asForm()->post($url, $payload);

        if (!$response->successful()) {
            \Log::error('Failed to exchange code for token', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $url,
            ]);

            throw new Exception("Failed to exchange code for token: {$response->body()}");
        }

        $data = $response->json();

        return [
            'access_token' => $data['access_token'] ?? '',
            'refresh_token' => $data['refresh_token'] ?? '',
            'expires_in' => $data['expires_in'] ?? 0,
            'token_type' => $data['token_type'] ?? 'Bearer',
        ];
    }

    /**
     * Rafraîchit un access token à partir d'un refresh token.
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int, token_type: string}
     *
     * @throws Exception
     */
    public function refreshToken(string $refreshToken): array
    {
        $url = "{$this->keycloakUrl}/realms/{$this->realm}/protocol/openid-connect/token";

        $response = Http::asForm()->post($url, [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
        ]);

        if (!$response->successful()) {
            throw new Exception("Failed to refresh token: {$response->body()}");
        }

        $data = $response->json();

        return [
            'access_token' => $data['access_token'] ?? '',
            'refresh_token' => $data['refresh_token'] ?? '',
            'expires_in' => $data['expires_in'] ?? 0,
            'token_type' => $data['token_type'] ?? 'Bearer',
        ];
    }

    /**
     * Révoque un refresh token (logout côté Keycloak).
     *
     * Utilise l'endpoint de révocation OAuth2 (RFC 7009) avec l'URL interne.
     *
     * @throws Exception
     */
    public function logout(string $refreshToken): void
    {
        // Utiliser l'endpoint de révocation (fonctionne avec l'URL interne)
        $url = "{$this->keycloakUrlPrivate}/realms/{$this->realm}/protocol/openid-connect/revoke";

        \Log::debug('Revoking token from Keycloak', [
            'url' => $url,
            'client_id' => $this->clientId,
        ]);

        $response = Http::asForm()->post($url, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'token' => $refreshToken,
            'token_type_hint' => 'refresh_token',
        ]);

        if (!$response->successful()) {
            \Log::error('Failed to revoke token from Keycloak', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new Exception("Failed to logout: {$response->body()}");
        }

        \Log::info('Successfully revoked token from Keycloak');
    }
}
