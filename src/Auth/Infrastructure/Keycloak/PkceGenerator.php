<?php

declare(strict_types=1);

namespace Auth\Infrastructure\Keycloak;

use Random\RandomException;

/**
 * Générateur PKCE (Proof Key for Code Exchange) pour OAuth2.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc7636
 */
final class PkceGenerator
{
    private const int CODE_VERIFIER_LENGTH = 64;

    /**
     * Génère un code_verifier aléatoire.
     *
     * @throws RandomException
     */
    public function generateCodeVerifier(): string
    {
        $bytes = random_bytes(self::CODE_VERIFIER_LENGTH);

        return $this->base64UrlEncode($bytes);
    }

    /**
     * Génère le code_challenge à partir du code_verifier (méthode S256).
     */
    public function generateCodeChallenge(string $codeVerifier): string
    {
        $hash = hash('sha256', $codeVerifier, true);

        return $this->base64UrlEncode($hash);
    }

    /**
     * Retourne la méthode de challenge utilisée.
     */
    public function getChallengeMethod(): string
    {
        return 'S256';
    }

    /**
     * Encode en base64 URL-safe (sans padding).
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
