<?php

declare(strict_types=1);

namespace Tenant\Interface\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use OpenApi\Attributes as OA;
use Tenant\Application\ChangeSiteStatus\ChangeSiteStatusCommand;
use Tenant\Application\ChangeSiteStatus\ChangeSiteStatusHandler;
use Tenant\Application\CreateSite\CreateSiteCommand;
use Tenant\Application\CreateSite\CreateSiteHandler;
use Tenant\Application\DeleteSite\DeleteSiteCommand;
use Tenant\Application\DeleteSite\DeleteSiteHandler;
use Tenant\Application\GetSite\GetSiteHandler;
use Tenant\Application\GetSite\GetSiteQuery;
use Tenant\Application\ListSites\ListSitesHandler;
use Tenant\Application\ListSites\ListSitesQuery;
use Tenant\Application\TenantContext;
use Tenant\Application\UpdateSite\UpdateSiteCommand;
use Tenant\Application\UpdateSite\UpdateSiteHandler;
use Tenant\Domain\Site;
use Tenant\Domain\SiteStatus;

#[OA\Tag(name: 'Sites', description: 'Site management endpoints')]
final class SiteController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly CreateSiteHandler $createSiteHandler,
        private readonly UpdateSiteHandler $updateSiteHandler,
        private readonly ChangeSiteStatusHandler $changeSiteStatusHandler,
        private readonly GetSiteHandler $getSiteHandler,
        private readonly ListSitesHandler $listSitesHandler,
        private readonly DeleteSiteHandler $deleteSiteHandler,
    ) {}

    #[OA\Get(
        path: '/api/sites',
        summary: 'List all sites for current tenant',
        security: [['bearerAuth' => []]],
        tags: ['Sites'],
        parameters: [
            new OA\Parameter(
                name: 'active_only',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean'),
                description: 'Filter to show only active sites'
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of sites'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->tenantContext->requireTenantId();
        $activeOnly = $request->boolean('active_only', false);

        $sites = $this->listSitesHandler->handle(
            new ListSitesQuery($tenantId, $activeOnly)
        );

        return response()->json([
            'data' => array_map(fn (Site $site) => $this->formatSite($site), $sites),
            'count' => count($sites),
        ]);
    }

    #[OA\Get(
        path: '/api/sites/{id}',
        summary: 'Get site details',
        security: [['bearerAuth' => []]],
        tags: ['Sites'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Site details'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Site not found'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $site = $this->getSiteHandler->handle(new GetSiteQuery($id));

        if ($site === null) {
            return response()->json(['error' => 'Site not found'], 404);
        }

        // Vérifier que le site appartient au tenant courant
        if (!$this->tenantContext->belongsToCurrentTenant($site->tenantId())) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json($this->formatSite($site));
    }

    #[OA\Post(
        path: '/api/sites',
        summary: 'Create a new site',
        security: [['bearerAuth' => []]],
        tags: ['Sites'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'slug'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Paris Centre'),
                    new OA\Property(property: 'slug', type: 'string', example: 'paris-centre'),
                    new OA\Property(property: 'address', type: 'string', nullable: true),
                    new OA\Property(property: 'city', type: 'string', nullable: true),
                    new OA\Property(property: 'postal_code', type: 'string', nullable: true),
                    new OA\Property(property: 'country', type: 'string', example: 'FR'),
                    new OA\Property(property: 'phone', type: 'string', nullable: true),
                    new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true),
                    new OA\Property(property: 'opening_hours', type: 'object', nullable: true),
                    new OA\Property(property: 'settings', type: 'object', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Site created successfully'),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|regex:/^[a-z0-9-]+$/',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:2',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'opening_hours' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        try {
            $site = $this->createSiteHandler->handle(new CreateSiteCommand(
                tenantId: $this->tenantContext->requireTenantId(),
                name: $validated['name'],
                slug: $validated['slug'],
                address: $validated['address'] ?? null,
                city: $validated['city'] ?? null,
                postalCode: $validated['postal_code'] ?? null,
                country: $validated['country'] ?? 'FR',
                phone: $validated['phone'] ?? null,
                email: $validated['email'] ?? null,
                openingHours: $validated['opening_hours'] ?? null,
                settings: $validated['settings'] ?? null,
                latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
                longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            ));

            return response()->json([
                'message' => 'Site created successfully',
                'site' => $this->formatSite($site),
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Put(
        path: '/api/sites/{id}',
        summary: 'Update a site',
        security: [['bearerAuth' => []]],
        tags: ['Sites'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Paris Centre Updated'),
                    new OA\Property(property: 'address', type: 'string', nullable: true),
                    new OA\Property(property: 'city', type: 'string', nullable: true),
                    new OA\Property(property: 'postal_code', type: 'string', nullable: true),
                    new OA\Property(property: 'country', type: 'string', example: 'FR'),
                    new OA\Property(property: 'phone', type: 'string', nullable: true),
                    new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true),
                    new OA\Property(property: 'opening_hours', type: 'object', nullable: true),
                    new OA\Property(property: 'settings', type: 'object', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Site updated successfully'),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Site not found'),
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        // Vérifier que le site appartient au tenant
        $existingSite = $this->getSiteHandler->handle(new GetSiteQuery($id));
        if ($existingSite === null) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        if (!$this->tenantContext->belongsToCurrentTenant($existingSite->tenantId())) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:2',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'opening_hours' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        try {
            $site = $this->updateSiteHandler->handle(new UpdateSiteCommand(
                siteId: $id,
                name: $validated['name'],
                address: $validated['address'] ?? null,
                city: $validated['city'] ?? null,
                postalCode: $validated['postal_code'] ?? null,
                country: $validated['country'] ?? null,
                phone: $validated['phone'] ?? null,
                email: $validated['email'] ?? null,
                openingHours: $validated['opening_hours'] ?? null,
                settings: $validated['settings'] ?? null,
                latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
                longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            ));

            return response()->json([
                'message' => 'Site updated successfully',
                'site' => $this->formatSite($site),
            ]);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Put(
        path: '/api/sites/{id}/status',
        summary: 'Change site status',
        security: [['bearerAuth' => []]],
        tags: ['Sites'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'suspended', 'closed'],
                        example: 'suspended'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Site status changed successfully'),
            new OA\Response(response: 400, description: 'Invalid status'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Site not found'),
        ]
    )]
    public function changeStatus(Request $request, string $id): JsonResponse
    {
        // Vérifier que le site appartient au tenant
        $existingSite = $this->getSiteHandler->handle(new GetSiteQuery($id));
        if ($existingSite === null) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        if (!$this->tenantContext->belongsToCurrentTenant($existingSite->tenantId())) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:active,suspended,closed',
        ]);

        try {
            $status = SiteStatus::from($validated['status']);

            $site = $this->changeSiteStatusHandler->handle(
                new ChangeSiteStatusCommand($id, $status)
            );

            return response()->json([
                'message' => 'Site status changed successfully',
                'site' => $this->formatSite($site),
            ]);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Delete(
        path: '/api/sites/{id}',
        summary: 'Delete a site',
        security: [['bearerAuth' => []]],
        tags: ['Sites'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Site deleted successfully'),
            new OA\Response(response: 400, description: 'Cannot delete site'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Site not found'),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        // Vérifier que le site appartient au tenant
        $existingSite = $this->getSiteHandler->handle(new GetSiteQuery($id));
        if ($existingSite === null) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        if (!$this->tenantContext->belongsToCurrentTenant($existingSite->tenantId())) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        try {
            $this->deleteSiteHandler->handle(new DeleteSiteCommand($id));

            return response()->json(null, 204);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formatSite(Site $site): array
    {
        return [
            'id' => $site->id(),
            'tenant_id' => $site->tenantId(),
            'name' => $site->name(),
            'slug' => $site->slug(),
            'address' => $site->address(),
            'city' => $site->city(),
            'postal_code' => $site->postalCode(),
            'country' => $site->country(),
            'full_address' => $site->fullAddress(),
            'phone' => $site->phone(),
            'email' => $site->email(),
            'status' => $site->status()->value,
            'status_label' => $site->status()->label(),
            'can_accept_rentals' => $site->canAcceptRentals(),
            'opening_hours' => $site->openingHours(),
            'settings' => $site->settings(),
            'latitude' => $site->latitude(),
            'longitude' => $site->longitude(),
            'has_geolocation' => $site->hasGeolocation(),
            'created_at' => $site->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => $site->updatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
