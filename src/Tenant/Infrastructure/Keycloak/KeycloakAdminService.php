<?php

declare(strict_types=1);

namespace Tenant\Infrastructure\Keycloak;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

final class KeycloakAdminService
{
    private ?string $accessToken = null;
    private ?int $tokenExpiry = null;

    public function __construct(
        private readonly Client $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $keycloakUrl,
        private readonly string $realm,
        private readonly string $adminUsername,
        private readonly string $adminPassword,
    ) {
    }

    /**
     * Créer une organization dans Keycloak
     *
     * @param array<string, mixed> $attributes
     */
    public function createOrganization(
        string $name,
        string $alias,
        array $attributes = []
    ): ?string {
        try {
            $this->ensureAuthenticated();

            $response = $this->httpClient->post(
                "{$this->keycloakUrl}/admin/realms/{$this->realm}/organizations",
                [
                    'headers' => [
                        'Authorization' => "Bearer {$this->accessToken}",
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'name' => $name,
                        'alias' => $alias,  // Alias explicite (sans espaces)
                        'enabled' => true,
                        'domains' => [
                            [
                                'name' => $alias,
                                'verified' => true,
                            ],
                        ],
                        'attributes' => array_merge($attributes, [
                            'slug' => [$alias],  // Stocker l'alias dans les attributs
                        ]),
                    ],
                ]
            );

            // Debug: Logger la réponse complète
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $headers = $response->getHeaders();

            $this->logger->info('Keycloak organization creation response', [
                'status' => $statusCode,
                'body' => $body,
                'location_header' => $headers['Location'] ?? null,
            ]);

            // Keycloak retourne l'URL de la ressource créée dans le header Location
            $location = $response->getHeader('Location')[0] ?? null;
            if ($location !== null) {
                // Extraire l'ID depuis l'URL: /admin/realms/{realm}/organizations/{id}
                $parts = explode('/', $location);
                return end($parts);
            }

            // Si pas de Location, peut-être l'ID est dans le body
            if (!empty($body)) {
                $data = json_decode($body, true);
                if (isset($data['id'])) {
                    return $data['id'];
                }
            }

            return null;
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to create Keycloak organization', [
                'name' => $name,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Mettre à jour une organization
     *
     * @param array<string, mixed> $attributes
     */
    public function updateOrganization(
        string $organizationId,
        string $name,
        array $attributes = [],
        ?string $domainSlug = null
    ): bool {
        try {
            $this->ensureAuthenticated();

            // Construire le payload
            $payload = [
                'name' => $name,
                'enabled' => true,
                'attributes' => $attributes,
            ];

            // Ajouter le domaine si fourni
            if ($domainSlug !== null) {
                $payload['domains'] = [
                    [
                        'name' => $domainSlug,
                        'verified' => true,
                    ],
                ];
            }

            $this->httpClient->put(
                "{$this->keycloakUrl}/admin/realms/{$this->realm}/organizations/{$organizationId}",
                [
                    'headers' => [
                        'Authorization' => "Bearer {$this->accessToken}",
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $payload,
                ]
            );

            return true;
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to update Keycloak organization', [
                'id' => $organizationId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Supprimer une organization
     */
    public function deleteOrganization(string $organizationId): bool
    {
        try {
            $this->ensureAuthenticated();

            $this->httpClient->delete(
                "{$this->keycloakUrl}/admin/realms/{$this->realm}/organizations/{$organizationId}",
                [
                    'headers' => [
                        'Authorization' => "Bearer {$this->accessToken}",
                    ],
                ]
            );

            return true;
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to delete Keycloak organization', [
                'id' => $organizationId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Récupérer une organization par son ID
     *
     * @return array<string, mixed>|null
     */
    public function getOrganization(string $organizationId): ?array
    {
        try {
            $this->ensureAuthenticated();

            $response = $this->httpClient->get(
                "{$this->keycloakUrl}/admin/realms/{$this->realm}/organizations/{$organizationId}",
                [
                    'headers' => [
                        'Authorization' => "Bearer {$this->accessToken}",
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to get Keycloak organization', [
                'id' => $organizationId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Lister toutes les organizations
     *
     * @return array<int, array<string, mixed>>
     */
    public function listOrganizations(): array
    {
        try {
            $this->ensureAuthenticated();

            $response = $this->httpClient->get(
                "{$this->keycloakUrl}/admin/realms/{$this->realm}/organizations",
                [
                    'headers' => [
                        'Authorization' => "Bearer {$this->accessToken}",
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to list Keycloak organizations', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Ajouter un utilisateur à une organization
     *
     * @param array<string, mixed> $membershipAttributes
     */
    public function addMember(
        string $organizationId,
        string $userId,
        array $membershipAttributes = []
    ): bool {
        try {
            $this->ensureAuthenticated();

            $this->httpClient->put(
                "{$this->keycloakUrl}/admin/realms/{$this->realm}/organizations/{$organizationId}/members/{$userId}",
                [
                    'headers' => [
                        'Authorization' => "Bearer {$this->accessToken}",
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'membershipAttributes' => $membershipAttributes,
                    ],
                ]
            );

            return true;
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to add member to Keycloak organization', [
                'organization_id' => $organizationId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Supprimer un membre d'une organization
     */
    public function removeMember(string $organizationId, string $userId): bool
    {
        try {
            $this->ensureAuthenticated();

            $this->httpClient->delete(
                "{$this->keycloakUrl}/admin/realms/{$this->realm}/organizations/{$organizationId}/members/{$userId}",
                [
                    'headers' => [
                        'Authorization' => "Bearer {$this->accessToken}",
                    ],
                ]
            );

            return true;
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to remove member from Keycloak organization', [
                'organization_id' => $organizationId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * S'assurer que le token d'accès est valide
     */
    private function ensureAuthenticated(): void
    {
        // Si on a un token valide, pas besoin de re-authentifier
        if ($this->accessToken !== null && $this->tokenExpiry !== null && time() < $this->tokenExpiry) {
            return;
        }

        $this->authenticate();
    }

    /**
     * S'authentifier auprès de Keycloak pour obtenir un token admin
     */
    private function authenticate(): void
    {
        try {
            $response = $this->httpClient->post(
                "{$this->keycloakUrl}/realms/master/protocol/openid-connect/token",
                [
                    'form_params' => [
                        'client_id' => 'admin-cli',
                        'username' => $this->adminUsername,
                        'password' => $this->adminPassword,
                        'grant_type' => 'password',
                    ],
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);
            $this->accessToken = $data['access_token'] ?? null;
            $this->tokenExpiry = time() + ($data['expires_in'] ?? 300) - 10; // 10s de marge

            if ($this->accessToken === null) {
                throw new \RuntimeException('Failed to obtain Keycloak admin token');
            }
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to authenticate with Keycloak', [
                'url' => $this->keycloakUrl,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw new \RuntimeException('Keycloak authentication failed: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error during Keycloak authentication', [
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Keycloak authentication failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
