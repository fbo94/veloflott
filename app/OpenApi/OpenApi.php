<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Veloflott API',
    description: 'OpenAPI documentation for all available endpoints.'
)]
#[OA\Server(url: 'http://localhost', description: 'Local server')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
class OpenApi
{
}

// ------------------------------ PATHS ------------------------------

// GET /api/me
#[OA\Get(
    path: '/api/me',
    summary: 'Get current authenticated user',
    security: [['bearerAuth' => []]],
    tags: ['Auth'],
    responses: [
        new OA\Response(response: 200, description: 'Current user returned'),
        new OA\Response(response: 401, description: 'Unauthorized'),
    ]
)]
class MeEndpoint
{
}

// GET /api/users
#[OA\Get(
    path: '/api/users',
    summary: 'List users (admin only)',
    security: [['bearerAuth' => []]],
    tags: ['Users'],
    parameters: [
        new OA\Parameter(name: 'role', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Filter by role'),
        new OA\Parameter(name: 'is_active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean'), description: 'Filter by active status'),
    ],
    responses: [
        new OA\Response(response: 200, description: 'List of users'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden'),
    ]
)]
class ListUsersEndpoint
{
}

// PUT /api/users/{id}/role
#[OA\Put(
    path: '/api/users/{id}/role',
    summary: 'Update user role (admin only)',
    security: [['bearerAuth' => []]],
    tags: ['Users'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
    ],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(
        required: ['role'],
        properties: [
            new OA\Property(property: 'role', type: 'string', example: 'ADMIN'),
        ],
        type: 'object'
    ))),
    responses: [
        new OA\Response(response: 200, description: 'Role updated'),
        new OA\Response(response: 400, description: 'Bad request'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden'),
    ]
)]
class UpdateUserRoleEndpoint
{
}

// POST /api/users/{id}/toggle-status
#[OA\Post(
    path: '/api/users/{id}/toggle-status',
    summary: 'Toggle user active status (admin only)',
    security: [['bearerAuth' => []]],
    tags: ['Users'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Status toggled'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden'),
    ]
)]
class ToggleUserStatusEndpoint
{
}

// GET /api/user (Sanctum protected default route)
#[OA\Get(
    path: '/api/user',
    summary: 'Get authenticated user (Sanctum)',
    security: [['bearerAuth' => []]],
    tags: ['Auth'],
    responses: [
        new OA\Response(response: 200, description: 'Current user returned'),
        new OA\Response(response: 401, description: 'Unauthorized'),
    ]
)]
class LaravelUserEndpoint
{
}

// GET /api/login (public example route)
#[OA\Get(
    path: '/api/login',
    summary: 'Login endpoint (placeholder)',
    tags: ['Auth'],
    responses: [
        new OA\Response(response: 200, description: 'Message returned'),
    ]
)]
class LoginEndpoint
{
}

// GET /api/auth/authorization-url (public)
#[OA\Get(
    path: '/api/auth/authorization-url',
    summary: 'Get OAuth2 authorization URL (public)',
    tags: ['Auth'],
    parameters: [
        new OA\Parameter(
            name: 'redirect_url',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', format: 'uri'),
            description: 'Optional client redirect URL to include in the authorization flow'
        ),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Authorization URL payload returned'),
    ]
)]
class GetAuthorizationUrlEndpoint
{
}

// POST /api/auth/authorize (public)
#[OA\Post(
    path: '/api/auth/authorize',
    summary: 'OAuth2 authorization callback (public)',
    tags: ['Auth'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['code', 'state'],
                properties: [
                    new OA\Property(property: 'code', type: 'string', example: 'abc123'),
                    new OA\Property(property: 'state', type: 'string', example: 'opaque-state'),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Tokens/user info returned'),
        new OA\Response(response: 400, description: 'Invalid payload or state mismatch'),
    ]
)]
class AuthorizeEndpoint
{
}

// ------------------------------ CUSTOMERS ------------------------------

// POST /api/customers
#[OA\Post(
    path: '/api/customers',
    summary: 'Create a new customer',
    security: [['bearerAuth' => []]],
    tags: ['Customers'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['first_name', 'last_name'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'Jean'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Dupont'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jean.dupont@example.com', nullable: true),
                    new OA\Property(property: 'phone', type: 'string', example: '+33612345678', nullable: true),
                    new OA\Property(property: 'identity_document_type', type: 'string', example: 'Carte d\'identité', nullable: true),
                    new OA\Property(property: 'identity_document_number', type: 'string', example: 'AB123456', nullable: true),
                    new OA\Property(property: 'height', type: 'integer', example: 175, description: 'Height in cm', nullable: true),
                    new OA\Property(property: 'weight', type: 'integer', example: 75, description: 'Weight in kg', nullable: true),
                    new OA\Property(property: 'address', type: 'string', example: '123 Rue de la Paix, 75001 Paris', nullable: true),
                    new OA\Property(property: 'notes', type: 'string', example: 'Client régulier', nullable: true),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Customer created successfully'),
        new OA\Response(response: 400, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_customers permission'),
    ]
)]
class CreateCustomerEndpoint
{
}

// GET /api/customers/search
#[OA\Get(
    path: '/api/customers/search',
    summary: 'Search customers by name, email, or phone',
    security: [['bearerAuth' => []]],
    tags: ['Customers'],
    parameters: [
        new OA\Parameter(
            name: 'query',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string'),
            description: 'Search query to match against first name, last name, email, or phone'
        ),
    ],
    responses: [
        new OA\Response(response: 200, description: 'List of matching customers'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_customers permission'),
    ]
)]
class SearchCustomersEndpoint
{
}

// GET /api/customers/{id}
#[OA\Get(
    path: '/api/customers/{id}',
    summary: 'Get customer details with rental history and statistics',
    security: [['bearerAuth' => []]],
    tags: ['Customers'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Customer details including rental history and statistics'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_customers permission'),
        new OA\Response(response: 404, description: 'Customer not found'),
    ]
)]
class GetCustomerDetailEndpoint
{
}

// PUT /api/customers/{id}
#[OA\Put(
    path: '/api/customers/{id}',
    summary: 'Update customer information',
    security: [['bearerAuth' => []]],
    tags: ['Customers'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['first_name', 'last_name'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'Jean'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Dupont'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jean.dupont@example.com', nullable: true),
                    new OA\Property(property: 'phone', type: 'string', example: '+33612345678', nullable: true),
                    new OA\Property(property: 'identity_document_type', type: 'string', example: 'Passeport', nullable: true),
                    new OA\Property(property: 'identity_document_number', type: 'string', example: 'CD789012', nullable: true),
                    new OA\Property(property: 'height', type: 'integer', example: 180, description: 'Height in cm', nullable: true),
                    new OA\Property(property: 'weight', type: 'integer', example: 80, description: 'Weight in kg', nullable: true),
                    new OA\Property(property: 'address', type: 'string', example: '456 Avenue des Champs, 75008 Paris', nullable: true),
                    new OA\Property(property: 'notes', type: 'string', example: 'Préfère les vélos de route', nullable: true),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Customer updated successfully'),
        new OA\Response(response: 400, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_customers permission'),
        new OA\Response(response: 404, description: 'Customer not found'),
    ]
)]
class UpdateCustomerEndpoint
{
}

// POST /api/customers/{id}/toggle-risky
#[OA\Post(
    path: '/api/customers/{id}/toggle-risky',
    summary: 'Toggle customer risky flag',
    security: [['bearerAuth' => []]],
    tags: ['Customers'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Risky flag toggled successfully'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_customers permission'),
        new OA\Response(response: 404, description: 'Customer not found'),
    ]
)]
class ToggleRiskyFlagEndpoint
{
}

// ------------------------------ FLEET - BRANDS ------------------------------

// GET /api/fleet/brands
#[OA\Get(
    path: '/api/fleet/brands',
    summary: 'List all bike brands',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Brands'],
    responses: [
        new OA\Response(response: 200, description: 'List of bike brands'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_bikes permission'),
    ]
)]
class ListBrandsEndpoint
{
}

// POST /api/fleet/brands
#[OA\Post(
    path: '/api/fleet/brands',
    summary: 'Create a new bike brand',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Brands'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Specialized', maxLength: 100),
                    new OA\Property(property: 'logo_url', type: 'string', format: 'uri', example: 'https://example.com/logo.png', nullable: true, maxLength: 500),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Brand created successfully'),
        new OA\Response(response: 400, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_bikes permission'),
    ]
)]
class CreateBrandEndpoint
{
}

// PUT /api/fleet/brands/{id}
#[OA\Put(
    path: '/api/fleet/brands/{id}',
    summary: 'Update a bike brand',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Brands'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Specialized', maxLength: 100),
                    new OA\Property(property: 'logo_url', type: 'string', format: 'uri', example: 'https://example.com/logo.png', nullable: true, maxLength: 500),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Brand updated successfully'),
        new OA\Response(response: 400, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_bikes permission'),
        new OA\Response(response: 404, description: 'Brand not found'),
    ]
)]
class UpdateBrandEndpoint
{
}

// DELETE /api/fleet/brands/{id}
#[OA\Delete(
    path: '/api/fleet/brands/{id}',
    summary: 'Delete a bike brand',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Brands'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    responses: [
        new OA\Response(response: 204, description: 'Brand deleted successfully'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_bikes permission'),
        new OA\Response(response: 404, description: 'Brand not found'),
    ]
)]
class DeleteBrandEndpoint
{
}

// ------------------------------ FLEET - MODELS ------------------------------

// GET /api/fleet/models
#[OA\Get(
    path: '/api/fleet/models',
    summary: 'List all bike models with optional brand filter',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Models'],
    parameters: [
        new OA\Parameter(
            name: 'brand_id',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', format: 'uuid'),
            description: 'Filter models by brand ID'
        ),
    ],
    responses: [
        new OA\Response(response: 200, description: 'List of bike models'),
        new OA\Response(response: 400, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_bikes permission'),
    ]
)]
class ListModelsEndpoint
{
}

// GET /api/fleet/models/{id}
#[OA\Get(
    path: '/api/fleet/models/{id}',
    summary: 'Get bike model details',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Models'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Model details',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'name', type: 'string', example: 'Tarmac SL7'),
                        new OA\Property(property: 'brand_id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'brand_name', type: 'string', example: 'Specialized'),
                        new OA\Property(property: 'brand_logo_url', type: 'string', format: 'uri', nullable: true),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_bikes permission'),
        new OA\Response(response: 404, description: 'Model not found'),
    ]
)]
class GetModelDetailEndpoint
{
}

// POST /api/fleet/models
#[OA\Post(
    path: '/api/fleet/models',
    summary: 'Create a new bike model',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Models'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['name', 'brand_id'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Tarmac SL7', maxLength: 100),
                    new OA\Property(property: 'brand_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Model created successfully'),
        new OA\Response(response: 400, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_bikes permission'),
    ]
)]
class CreateModelEndpoint
{
}

// PUT /api/fleet/models/{id}
#[OA\Put(
    path: '/api/fleet/models/{id}',
    summary: 'Update a bike model',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Models'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['name', 'brand_id'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Tarmac SL7', maxLength: 100),
                    new OA\Property(property: 'brand_id', type: 'string', format: 'uuid'),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Model updated successfully'),
        new OA\Response(response: 400, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_bikes permission'),
        new OA\Response(response: 404, description: 'Model not found'),
    ]
)]
class UpdateModelEndpoint
{
}

// DELETE /api/fleet/models/{id}
#[OA\Delete(
    path: '/api/fleet/models/{id}',
    summary: 'Delete a bike model',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Models'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    responses: [
        new OA\Response(response: 204, description: 'Model deleted successfully'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_bikes permission'),
        new OA\Response(response: 404, description: 'Model not found'),
    ]
)]
class DeleteModelEndpoint
{
}

// ------------------------------ FLEET - BIKES ------------------------------

// POST /api/fleet/bikes
#[OA\Post(
    path: '/api/fleet/bikes',
    summary: 'Create a new bike',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Bikes'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['brand_id', 'model_id', 'category_id', 'serial_number', 'frame_number'],
                properties: [
                    new OA\Property(property: 'brand_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
                    new OA\Property(property: 'model_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440001'),
                    new OA\Property(property: 'category_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440002'),
                    new OA\Property(property: 'serial_number', type: 'string', example: 'SN-2024-001'),
                    new OA\Property(property: 'frame_number', type: 'string', example: 'FN-2024-001'),
                    new OA\Property(property: 'purchase_date', type: 'string', format: 'date', example: '2024-01-15', nullable: true),
                    new OA\Property(property: 'purchase_price', type: 'number', format: 'float', example: 1500.00, nullable: true),
                    new OA\Property(property: 'notes', type: 'string', example: 'VTT électrique neuf', nullable: true),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Bike created successfully'),
        new OA\Response(response: 400, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_bikes permission'),
    ]
)]
class CreateBikeEndpoint
{
}

// GET /api/fleet/bikes
#[OA\Get(
    path: '/api/fleet/bikes',
    summary: 'List all bikes with filters',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Bikes'],
    parameters: [
        new OA\Parameter(
            name: 'status',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['available', 'rented', 'maintenance', 'retired']),
            description: 'Filter by status'
        ),
        new OA\Parameter(
            name: 'category_id',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', format: 'uuid'),
            description: 'Filter by category'
        ),
        new OA\Parameter(
            name: 'brand_id',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', format: 'uuid'),
            description: 'Filter by brand'
        ),
        new OA\Parameter(
            name: 'model_id',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', format: 'uuid'),
            description: 'Filter by model'
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of bikes with photos',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'internal_number', type: 'string'),
                            new OA\Property(property: 'status', type: 'string'),
                            new OA\Property(property: 'brand', type: 'object'),
                            new OA\Property(property: 'model', type: 'object'),
                            new OA\Property(property: 'category', type: 'object'),
                            new OA\Property(property: 'frame_size', type: 'object'),
                            new OA\Property(property: 'year', type: 'integer', nullable: true),
                            new OA\Property(property: 'color', type: 'string', nullable: true),
                            new OA\Property(
                                property: 'photos',
                                type: 'array',
                                items: new OA\Items(type: 'string', format: 'uri'),
                                description: 'Array of photo URLs'
                            ),
                        ],
                        type: 'object'
                    )
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_bikes permission'),
    ]
)]
class ListBikesEndpoint
{
}

// GET /api/fleet/bikes/{id}
#[OA\Get(
    path: '/api/fleet/bikes/{id}',
    summary: 'Get bike details',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Bikes'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Bike details with photos',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'internal_number', type: 'string'),
                        new OA\Property(property: 'status', type: 'string'),
                        new OA\Property(property: 'brand', type: 'string'),
                        new OA\Property(property: 'model', type: 'string'),
                        new OA\Property(property: 'category', type: 'string'),
                        new OA\Property(property: 'frame_size', type: 'object'),
                        new OA\Property(property: 'year', type: 'integer', nullable: true),
                        new OA\Property(property: 'serial_number', type: 'string', nullable: true),
                        new OA\Property(property: 'color', type: 'string', nullable: true),
                        new OA\Property(property: 'wheel_size', type: 'string', nullable: true),
                        new OA\Property(property: 'purchase_price', type: 'number', nullable: true),
                        new OA\Property(property: 'purchase_date', type: 'string', nullable: true),
                        new OA\Property(property: 'notes', type: 'string', nullable: true),
                        new OA\Property(
                            property: 'photos',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'uri'),
                            description: 'Array of photo URLs'
                        ),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_bikes permission'),
        new OA\Response(response: 404, description: 'Bike not found'),
    ]
)]
class GetBikeDetailEndpoint
{
}

// PUT /api/fleet/bikes/{id}
#[OA\Put(
    path: '/api/fleet/bikes/{id}',
    summary: 'Update a bike',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Bikes'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['model_id', 'category_id', 'frame_size_unit'],
                properties: [
                    new OA\Property(property: 'model_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440001'),
                    new OA\Property(property: 'category_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440002'),
                    new OA\Property(property: 'frame_size_unit', type: 'string', enum: ['cm', 'inch', 'letter'], example: 'cm'),
                    new OA\Property(property: 'frame_size_numeric', type: 'number', example: 54, nullable: true),
                    new OA\Property(property: 'frame_size_letter', type: 'string', enum: ['XS', 'S', 'M', 'L', 'XL', 'XXL'], nullable: true),
                    new OA\Property(property: 'year', type: 'integer', example: 2024, nullable: true),
                    new OA\Property(property: 'serial_number', type: 'string', example: 'SN-2024-001', nullable: true),
                    new OA\Property(property: 'color', type: 'string', example: 'Noir', nullable: true),
                    new OA\Property(property: 'wheel_size', type: 'string', enum: ['26', '27.5', '29', '700c'], nullable: true),
                    new OA\Property(property: 'front_suspension', type: 'integer', example: 120, nullable: true, description: 'Front suspension travel in mm'),
                    new OA\Property(property: 'rear_suspension', type: 'integer', example: 110, nullable: true, description: 'Rear suspension travel in mm'),
                    new OA\Property(property: 'brake_type', type: 'string', enum: ['hydraulic_disc', 'mechanical_disc', 'rim'], nullable: true),
                    new OA\Property(property: 'purchase_price', type: 'number', format: 'float', example: 1500.00, nullable: true),
                    new OA\Property(property: 'purchase_date', type: 'string', format: 'date', example: '2024-01-15', nullable: true),
                    new OA\Property(property: 'notes', type: 'string', example: 'VTT électrique neuf', nullable: true),
                    new OA\Property(
                        property: 'photos',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        nullable: true,
                        description: 'Array of photos: existing URLs (to keep) or base64 encoded images (to upload). Format: "data:image/jpeg;base64,..." or "http://..."'
                    ),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Bike updated successfully',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'message', type: 'string', example: 'Bike updated successfully'),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 400, description: 'Validation error or bike cannot be modified'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_bikes permission'),
        new OA\Response(response: 404, description: 'Bike not found'),
    ]
)]
class UpdateBikeEndpoint
{
}

// POST /api/fleet/bikes/{id}/photos
#[OA\Post(
    path: '/api/fleet/bikes/{id}/photos',
    summary: 'Upload a photo for a bike',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Bikes'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['photo'],
                properties: [
                    new OA\Property(property: 'photo', type: 'string', format: 'binary', description: 'Photo file (max 5MB)'),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Photo uploaded successfully',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'bike_id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'photo_url', type: 'string', format: 'uri'),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 400, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_bikes permission'),
        new OA\Response(response: 404, description: 'Bike not found'),
    ]
)]
class UploadBikePhotoEndpoint
{
}

// DELETE /api/fleet/bikes/{id}/photos
#[OA\Delete(
    path: '/api/fleet/bikes/{id}/photos',
    summary: 'Delete a photo from a bike',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Bikes'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['photo_url'],
                properties: [
                    new OA\Property(property: 'photo_url', type: 'string', format: 'uri', example: 'http://localhost/storage/bikes/{id}/{filename}'),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 204, description: 'Photo deleted successfully'),
        new OA\Response(response: 400, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_bikes permission'),
        new OA\Response(response: 404, description: 'Bike not found'),
    ]
)]
class DeleteBikePhotoEndpoint
{
}

// POST /api/fleet/bikes/{id}/retire
#[OA\Post(
    path: '/api/fleet/bikes/{id}/retire',
    summary: 'Retire a bike from the fleet',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Bikes'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['reason'],
                properties: [
                    new OA\Property(property: 'reason', type: 'string', enum: ['sold', 'stolen', 'permanently_out_of_service', 'other'], example: 'sold'),
                    new OA\Property(property: 'comment', type: 'string', maxLength: 1000, example: 'Sold to customer'),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Bike retired successfully'),
        new OA\Response(response: 400, description: 'Cannot retire a bike that is currently rented'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_bikes permission'),
        new OA\Response(response: 404, description: 'Bike not found'),
        new OA\Response(response: 422, description: 'Validation error'),
    ]
)]
class RetireBikeEndpoint
{
}

// PUT /api/fleet/bikes/{id}/status
#[OA\Put(
    path: '/api/fleet/bikes/{id}/status',
    summary: 'Change bike status',
    description: 'Change the status of a bike (available, maintenance, unavailable). Rented bikes cannot be changed manually.',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Bikes'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['available', 'maintenance', 'unavailable'], example: 'unavailable', description: 'New bike status'),
                    new OA\Property(property: 'unavailability_reason', type: 'string', enum: ['reserved', 'loaned', 'other'], example: 'reserved', description: 'Required when status is unavailable'),
                    new OA\Property(property: 'unavailability_comment', type: 'string', maxLength: 1000, example: 'Reserved for VIP client', description: 'Optional comment'),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Bike status changed successfully'),
        new OA\Response(response: 400, description: 'Domain rule violation (e.g., cannot change status of rented bike)'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_bikes permission'),
        new OA\Response(response: 404, description: 'Bike not found'),
        new OA\Response(response: 422, description: 'Validation error'),
    ]
)]
class ChangeBikeStatusEndpoint
{
}

// GET /api/fleet/bikes/{id}/status-history
#[OA\Get(
    path: '/api/fleet/bikes/{id}/status-history',
    summary: 'Get bike status change history',
    description: 'Retrieve the complete history of status changes for a specific bike',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Bikes'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Status history retrieved successfully',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'old_status', type: 'string', example: 'available'),
                                    new OA\Property(property: 'old_status_label', type: 'string', example: 'Disponible'),
                                    new OA\Property(property: 'new_status', type: 'string', example: 'unavailable'),
                                    new OA\Property(property: 'new_status_label', type: 'string', example: 'Indisponible'),
                                    new OA\Property(property: 'unavailability_reason', type: 'string', nullable: true, example: 'reserved'),
                                    new OA\Property(property: 'unavailability_reason_label', type: 'string', nullable: true, example: 'Réservé'),
                                    new OA\Property(property: 'unavailability_comment', type: 'string', nullable: true, example: 'Reserved for VIP'),
                                    new OA\Property(property: 'changed_at', type: 'string', format: 'date-time', example: '2026-02-06 10:30:00'),
                                ],
                                type: 'object'
                            )
                        ),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_bikes permission'),
        new OA\Response(response: 404, description: 'Bike not found'),
    ]
)]
class GetBikeStatusHistoryEndpoint
{
}

// ------------------------------ FLEET - CATEGORIES ------------------------------

// POST /api/fleet/categories
#[OA\Post(
    path: '/api/fleet/categories',
    summary: 'Create a new bike category',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Categories'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'XC'),
                    new OA\Property(property: 'slug', type: 'string', example: 'xc', nullable: true, description: 'URL-friendly identifier (lowercase, alphanumeric and dashes only)'),
                    new OA\Property(property: 'description', type: 'string', example: 'Vélos cross-country', nullable: true),
                    new OA\Property(property: 'parent_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440003', nullable: true, description: 'ID of parent category for hierarchical organization'),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Category created successfully'),
        new OA\Response(response: 400, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_fleet permission'),
    ]
)]
class CreateCategoryEndpoint
{
}

// GET /api/fleet/categories
#[OA\Get(
    path: '/api/fleet/categories',
    summary: 'List all bike categories',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Categories'],
    responses: [
        new OA\Response(response: 200, description: 'List of categories'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_fleet permission'),
    ]
)]
class ListCategoriesEndpoint
{
}

// PUT /api/fleet/categories/{id}
#[OA\Put(
    path: '/api/fleet/categories/{id}',
    summary: 'Update a bike category',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Categories'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Enduro'),
                    new OA\Property(property: 'slug', type: 'string', example: 'enduro', nullable: true, description: 'URL-friendly identifier'),
                    new OA\Property(property: 'description', type: 'string', example: 'Vélos enduro all-mountain', nullable: true),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Category updated successfully'),
        new OA\Response(response: 400, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_fleet permission'),
        new OA\Response(response: 404, description: 'Category not found'),
    ]
)]
class UpdateCategoryEndpoint
{
}

// DELETE /api/fleet/categories/{id}
#[OA\Delete(
    path: '/api/fleet/categories/{id}',
    summary: 'Delete a bike category',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Categories'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    responses: [
        new OA\Response(response: 204, description: 'Category deleted successfully'),
        new OA\Response(response: 400, description: 'Cannot delete category with associated bikes'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_fleet permission'),
        new OA\Response(response: 404, description: 'Category not found'),
    ]
)]
class DeleteCategoryEndpoint
{
}

// ------------------------------ FLEET - RATES ------------------------------

// POST /api/fleet/categories/{categoryId}/rates
#[OA\Post(
    path: '/api/fleet/categories/{categoryId}/rates',
    summary: 'Create or update rate for a category',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Rates'],
    parameters: [
        new OA\Parameter(name: 'categoryId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['duration', 'price'],
                properties: [
                    new OA\Property(property: 'duration', type: 'string', enum: ['half_day', 'full_day', 'two_days', 'three_days', 'week', 'custom'], example: 'full_day'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 25.00),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Rate created or updated successfully'),
        new OA\Response(response: 400, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_fleet permission'),
        new OA\Response(response: 404, description: 'Category not found'),
    ]
)]
class SetCategoryRateEndpoint
{
}

// GET /api/fleet/rates
#[OA\Get(
    path: '/api/fleet/rates',
    summary: 'List all rates',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Rates'],
    parameters: [
        new OA\Parameter(
            name: 'category_id',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', format: 'uuid'),
            description: 'Filter by category ID'
        ),
        new OA\Parameter(
            name: 'bike_id',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', format: 'uuid'),
            description: 'Filter by bike ID'
        ),
    ],
    responses: [
        new OA\Response(response: 200, description: 'List of rates'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_fleet permission'),
    ]
)]
class ListRatesEndpoint
{
}

// PUT /api/fleet/rates/{id}
#[OA\Put(
    path: '/api/fleet/rates/{id}',
    summary: 'Update a rate',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Rates'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'duration', type: 'string', enum: ['half_day', 'full_day', 'two_days', 'three_days', 'week', 'custom'], example: 'full_day'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 30.00),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Rate updated successfully'),
        new OA\Response(response: 400, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_fleet permission'),
        new OA\Response(response: 404, description: 'Rate not found'),
    ]
)]
class UpdateRateEndpoint
{
}

// DELETE /api/fleet/rates/{id}
#[OA\Delete(
    path: '/api/fleet/rates/{id}',
    summary: 'Delete a rate',
    security: [['bearerAuth' => []]],
    tags: ['Fleet - Rates'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    responses: [
        new OA\Response(response: 204, description: 'Rate deleted successfully'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_fleet permission'),
        new OA\Response(response: 404, description: 'Rate not found'),
    ]
)]
class DeleteRateEndpoint
{
}

// ------------------------------ RENTALS ------------------------------

// POST /api/rentals
#[OA\Post(
    path: '/api/rentals',
    summary: 'Create a new rental',
    security: [['bearerAuth' => []]],
    tags: ['Rentals'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['customer_id', 'start_date', 'duration', 'deposit_amount', 'bike_items'],
                properties: [
                    new OA\Property(property: 'customer_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date-time', example: '2024-03-15T10:00:00Z'),
                    new OA\Property(property: 'duration', type: 'string', enum: ['half_day', 'full_day', 'two_days', 'three_days', 'week', 'custom'], example: 'full_day'),
                    new OA\Property(property: 'custom_end_date', type: 'string', format: 'date-time', nullable: true, description: 'Required when duration is "custom"'),
                    new OA\Property(property: 'deposit_amount', type: 'number', format: 'float', example: 200.00),
                    new OA\Property(
                        property: 'bike_items',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'bike_id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'daily_rate', type: 'number', format: 'float'),
                                new OA\Property(property: 'quantity', type: 'integer', example: 1),
                            ],
                            type: 'object'
                        )
                    ),
                    new OA\Property(
                        property: 'equipment_items',
                        type: 'array',
                        nullable: true,
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'type', type: 'string', enum: ['helmet', 'knee_pads', 'elbow_pads', 'gloves', 'backpack', 'lock', 'other']),
                                new OA\Property(property: 'quantity', type: 'integer'),
                                new OA\Property(property: 'price_per_unit', type: 'number', format: 'float'),
                            ],
                            type: 'object'
                        )
                    ),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Rental created successfully'),
        new OA\Response(response: 400, description: 'Validation error or bike not available'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_rentals permission'),
        new OA\Response(response: 404, description: 'Customer or bike not found'),
    ]
)]
class CreateRentalEndpoint
{
}

// POST /api/rentals/{id}/checkin
#[OA\Post(
    path: '/api/rentals/{id}/checkin',
    summary: 'Check-in a rental (record customer settings)',
    security: [['bearerAuth' => []]],
    tags: ['Rentals'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['bikes_check_in'],
                properties: [
                    new OA\Property(
                        property: 'bikes_check_in',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'bike_id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'client_height', type: 'integer', example: 175, description: 'Client height in cm'),
                                new OA\Property(property: 'client_weight', type: 'integer', example: 75, description: 'Client weight in kg'),
                                new OA\Property(property: 'saddle_height', type: 'integer', example: 75, description: 'Saddle height in cm'),
                                new OA\Property(property: 'front_suspension_pressure', type: 'integer', nullable: true, example: 80, description: 'Front suspension pressure in PSI'),
                                new OA\Property(property: 'rear_suspension_pressure', type: 'integer', nullable: true, example: 100, description: 'Rear suspension pressure in PSI'),
                                new OA\Property(property: 'pedal_type', type: 'string', nullable: true, example: 'SPD', description: 'Type of pedals mounted'),
                                new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Client prefers softer suspension'),
                            ],
                            type: 'object'
                        )
                    ),
                    new OA\Property(property: 'customer_signature', type: 'string', nullable: true, description: 'Base64 encoded signature image'),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Check-in completed, rental is now active'),
        new OA\Response(response: 400, description: 'Validation error or rental cannot be checked in'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires create_rentals permission'),
        new OA\Response(response: 404, description: 'Rental not found'),
    ]
)]
class CheckInRentalEndpoint
{
}

// POST /api/rentals/{id}/checkout
#[OA\Post(
    path: '/api/rentals/{id}/checkout',
    summary: 'Check-out a rental (process return)',
    security: [['bearerAuth' => []]],
    tags: ['Rentals'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['actual_return_date', 'bikes_condition'],
                properties: [
                    new OA\Property(property: 'actual_return_date', type: 'string', format: 'date-time', example: '2024-03-15T18:00:00Z'),
                    new OA\Property(
                        property: 'bikes_condition',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'bike_id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'condition', type: 'string', enum: ['ok', 'minor_damage', 'major_damage']),
                                new OA\Property(property: 'damage_description', type: 'string', nullable: true),
                                new OA\Property(
                                    property: 'damage_photos',
                                    type: 'array',
                                    nullable: true,
                                    items: new OA\Items(type: 'string', description: 'Photo URLs')
                                ),
                            ],
                            type: 'object'
                        )
                    ),
                    new OA\Property(property: 'deposit_retained', type: 'number', format: 'float', nullable: true, description: 'Amount of deposit to retain (for damages)'),
                    new OA\Property(property: 'hourly_late_rate', type: 'number', format: 'float', example: 10.00, description: 'Hourly rate for late returns'),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Rental checked out successfully'),
        new OA\Response(response: 400, description: 'Validation error or rental cannot be checked out'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_rentals permission'),
        new OA\Response(response: 404, description: 'Rental not found'),
    ]
)]
class CheckOutRentalEndpoint
{
}

// POST /api/rentals/{id}/cancel
#[OA\Post(
    path: '/api/rentals/{id}/cancel',
    summary: 'Cancel a rental',
    security: [['bearerAuth' => []]],
    tags: ['Rentals'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['cancellation_reason'],
                properties: [
                    new OA\Property(property: 'cancellation_reason', type: 'string', minLength: 5, maxLength: 500, example: 'Customer requested cancellation due to weather conditions'),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Rental cancelled successfully. Deposit status changed to released and bikes marked as available.',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'rental_id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'status', type: 'string', example: 'cancelled'),
                        new OA\Property(property: 'cancellation_reason', type: 'string'),
                        new OA\Property(property: 'deposit_status', type: 'string', example: 'released'),
                        new OA\Property(property: 'message', type: 'string'),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 400, description: 'Cannot cancel rental - rental is not in pending status'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires create_rentals permission'),
        new OA\Response(response: 404, description: 'Rental not found'),
        new OA\Response(response: 422, description: 'Validation error - cancellation reason required'),
    ]
)]
class CancelRentalEndpoint
{
}

// GET /api/rentals
#[OA\Get(
    path: '/api/rentals',
    summary: 'List rental history with filters and pagination',
    security: [['bearerAuth' => []]],
    tags: ['Rentals'],
    parameters: [
        new OA\Parameter(
            name: 'customer_id',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', format: 'uuid'),
            description: 'Filter by customer ID'
        ),
        new OA\Parameter(
            name: 'status',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['pending', 'active', 'completed', 'cancelled']),
            description: 'Filter by rental status'
        ),
        new OA\Parameter(
            name: 'start_date',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', format: 'date'),
            description: 'Filter rentals starting from this date'
        ),
        new OA\Parameter(
            name: 'end_date',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', format: 'date'),
            description: 'Filter rentals until this date'
        ),
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer', default: 1),
            description: 'Page number'
        ),
        new OA\Parameter(
            name: 'per_page',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer', default: 20, maximum: 100),
            description: 'Items per page'
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Paginated list of rentals with detailed information',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'customer_id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'customer_name', type: 'string'),
                                    new OA\Property(property: 'start_date', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'expected_return_date', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'actual_return_date', type: 'string', format: 'date-time', nullable: true),
                                    new OA\Property(property: 'bikes', type: 'array', items: new OA\Items(type: 'string')),
                                    new OA\Property(property: 'status', type: 'string'),
                                    new OA\Property(property: 'duration', type: 'string'),
                                    new OA\Property(property: 'total_amount', type: 'number'),
                                    new OA\Property(property: 'deposit_amount', type: 'number'),
                                    new OA\Property(property: 'deposit_status', type: 'string'),
                                    new OA\Property(property: 'deposit_retained', type: 'number', nullable: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                ],
                                type: 'object'
                            )
                        ),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'total', type: 'integer'),
                                new OA\Property(property: 'current_page', type: 'integer'),
                                new OA\Property(property: 'per_page', type: 'integer'),
                                new OA\Property(property: 'last_page', type: 'integer'),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_rentals permission'),
    ]
)]
class ListRentalsEndpoint
{
}

// GET /api/rentals/{id}
#[OA\Get(
    path: '/api/rentals/{id}',
    summary: 'Get rental details by ID',
    security: [['bearerAuth' => []]],
    tags: ['Rentals'],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string', format: 'uuid'),
            description: 'Rental ID'
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Detailed rental information with customer, bikes, and equipments',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        new OA\Property(
                            property: 'customer',
                            properties: [
                                new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'first_name', type: 'string'),
                                new OA\Property(property: 'last_name', type: 'string'),
                                new OA\Property(property: 'email', type: 'string'),
                                new OA\Property(property: 'phone', type: 'string', nullable: true),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(property: 'start_date', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'expected_return_date', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'actual_return_date', type: 'string', format: 'date-time', nullable: true),
                        new OA\Property(property: 'duration', type: 'string'),
                        new OA\Property(property: 'deposit_amount', type: 'number'),
                        new OA\Property(property: 'total_amount', type: 'number'),
                        new OA\Property(property: 'status', type: 'string'),
                        new OA\Property(property: 'deposit_status', type: 'string'),
                        new OA\Property(property: 'deposit_retained', type: 'number', nullable: true),
                        new OA\Property(property: 'cancellation_reason', type: 'string', nullable: true),
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'bike_id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'bike_brand', type: 'string'),
                                    new OA\Property(property: 'bike_model', type: 'string'),
                                    new OA\Property(property: 'bike_internal_number', type: 'string'),
                                    new OA\Property(property: 'daily_rate', type: 'number'),
                                    new OA\Property(property: 'quantity', type: 'integer'),
                                    new OA\Property(property: 'client_height', type: 'integer', nullable: true),
                                    new OA\Property(property: 'client_weight', type: 'integer', nullable: true),
                                    new OA\Property(property: 'saddle_height', type: 'integer', nullable: true),
                                    new OA\Property(property: 'front_suspension_pressure', type: 'integer', nullable: true),
                                    new OA\Property(property: 'rear_suspension_pressure', type: 'integer', nullable: true),
                                    new OA\Property(property: 'pedal_type', type: 'string', nullable: true),
                                    new OA\Property(property: 'check_in_notes', type: 'string', nullable: true),
                                    new OA\Property(property: 'return_condition', type: 'string', nullable: true),
                                    new OA\Property(property: 'damage_description', type: 'string', nullable: true),
                                    new OA\Property(property: 'damage_photos', type: 'array', items: new OA\Items(type: 'string')),
                                ],
                                type: 'object'
                            )
                        ),
                        new OA\Property(
                            property: 'equipments',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'type', type: 'string'),
                                    new OA\Property(property: 'quantity', type: 'integer'),
                                    new OA\Property(property: 'price_per_unit', type: 'number'),
                                ],
                                type: 'object'
                            )
                        ),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_rentals permission'),
        new OA\Response(response: 404, description: 'Rental not found'),
    ]
)]
class GetRentalDetailEndpoint
{
}

// GET /api/rentals/active
#[OA\Get(
    path: '/api/rentals/active',
    summary: 'List active rentals',
    security: [['bearerAuth' => []]],
    tags: ['Rentals'],
    parameters: [
        new OA\Parameter(
            name: 'customer_id',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', format: 'uuid'),
            description: 'Filter by customer ID'
        ),
        new OA\Parameter(
            name: 'bike_id',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', format: 'uuid'),
            description: 'Filter by bike ID'
        ),
        new OA\Parameter(
            name: 'only_late',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'boolean'),
            description: 'Show only late rentals'
        ),
    ],
    responses: [
        new OA\Response(response: 200, description: 'List of active rentals with delay indicators (on_time, soon_late, late)'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_rentals permission'),
    ]
)]
class ListActiveRentalsEndpoint
{
}

// ------------------------------ MAINTENANCE ------------------------------

// GET /api/maintenance/reasons
#[OA\Get(
    path: '/api/maintenance/reasons',
    summary: 'List all maintenance categories and reasons',
    security: [['bearerAuth' => []]],
    tags: ['Maintenance'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of maintenance categories with their associated reasons',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'categories',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'code', type: 'string', example: 'transmission'),
                                    new OA\Property(property: 'label', type: 'string', example: 'Transmission'),
                                    new OA\Property(
                                        property: 'reasons',
                                        type: 'array',
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: 'code', type: 'string', example: 'chain_replacement'),
                                                new OA\Property(property: 'label', type: 'string', example: 'Remplacement chaîne'),
                                            ],
                                            type: 'object'
                                        )
                                    ),
                                ],
                                type: 'object'
                            )
                        ),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_maintenance permission'),
    ]
)]
class ListMaintenanceReasonsEndpoint
{
}

// POST /api/maintenance/maintenances
#[OA\Post(
    path: '/api/maintenance/maintenances',
    summary: 'Declare a new maintenance for a bike',
    security: [['bearerAuth' => []]],
    tags: ['Maintenance'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['bike_id', 'type', 'reason', 'priority'],
                properties: [
                    new OA\Property(property: 'bike_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
                    new OA\Property(property: 'type', type: 'string', enum: ['preventive', 'curative'], example: 'curative'),
                    new OA\Property(property: 'reason', type: 'string', example: 'chain_replacement', description: 'Maintenance reason code from /api/maintenance/reasons'),
                    new OA\Property(property: 'priority', type: 'string', enum: ['low', 'normal', 'urgent'], example: 'normal'),
                    new OA\Property(property: 'scheduled_at', type: 'string', format: 'date-time', example: '2024-03-20T09:00:00Z'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Chaîne très usée, à remplacer rapidement'),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Maintenance declared successfully'),
        new OA\Response(response: 400, description: 'Validation error or bike not available for maintenance'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_maintenance permission'),
        new OA\Response(response: 404, description: 'Bike not found'),
    ]
)]
class DeclareMaintenanceEndpoint
{
}

// GET /api/maintenance/maintenances
#[OA\Get(
    path: '/api/maintenance/maintenances',
    summary: 'List maintenances with filters',
    security: [['bearerAuth' => []]],
    tags: ['Maintenance'],
    parameters: [
        new OA\Parameter(
            name: 'status',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['todo', 'in_progress', 'completed', 'cancelled']),
            description: 'Filter by status'
        ),
        new OA\Parameter(
            name: 'priority',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['low', 'normal', 'urgent']),
            description: 'Filter by priority'
        ),
        new OA\Parameter(
            name: 'type',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['preventive', 'curative']),
            description: 'Filter by type'
        ),
        new OA\Parameter(
            name: 'bike_id',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', format: 'uuid'),
            description: 'Filter by bike'
        ),
        new OA\Parameter(
            name: 'category',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['transmission', 'brakes', 'suspension', 'wheels', 'steering', 'frame', 'electrical', 'full_service', 'other']),
            description: 'Filter by maintenance category'
        ),
    ],
    responses: [
        new OA\Response(response: 200, description: 'List of maintenances'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_maintenance permission'),
    ]
)]
class ListMaintenancesEndpoint
{
}

// PUT /api/maintenance/maintenances/{id}/start
#[OA\Put(
    path: '/api/maintenance/maintenances/{id}/start',
    summary: 'Start a maintenance',
    security: [['bearerAuth' => []]],
    tags: ['Maintenance'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: false,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Début de la maintenance'),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Maintenance started, bike status changed to MAINTENANCE'),
        new OA\Response(response: 400, description: 'Maintenance cannot be started (not in TODO status)'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_maintenance permission'),
        new OA\Response(response: 404, description: 'Maintenance not found'),
    ]
)]
class StartMaintenanceEndpoint
{
}

// PUT /api/maintenance/maintenances/{id}/complete
#[OA\Put(
    path: '/api/maintenance/maintenances/{id}/complete',
    summary: 'Complete a maintenance',
    security: [['bearerAuth' => []]],
    tags: ['Maintenance'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: false,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'parts_cost', type: 'number', format: 'float', nullable: true, example: 25.50, description: 'Cost of parts in euros'),
                    new OA\Property(property: 'labor_cost', type: 'number', format: 'float', nullable: true, example: 15.00, description: 'Labor cost in euros'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Chaîne remplacée, vélo prêt'),
                ],
                type: 'object'
            )
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Maintenance completed, bike status changed to AVAILABLE'),
        new OA\Response(response: 400, description: 'Maintenance cannot be completed (not in IN_PROGRESS status)'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires manage_maintenance permission'),
        new OA\Response(response: 404, description: 'Maintenance not found'),
    ]
)]
class CompleteMaintenanceEndpoint
{
}

// GET /api/maintenance/bikes/{bikeId}/maintenances
#[OA\Get(
    path: '/api/maintenance/bikes/{bikeId}/maintenances',
    summary: 'Get maintenance history for a specific bike',
    security: [['bearerAuth' => []]],
    tags: ['Maintenance'],
    parameters: [
        new OA\Parameter(name: 'bikeId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    responses: [
        new OA\Response(response: 200, description: 'List of all maintenances for this bike'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_maintenance permission'),
        new OA\Response(response: 404, description: 'Bike not found'),
    ]
)]
class GetBikeMaintenanceHistoryEndpoint
{
}

// ------------------------------ DASHBOARD ------------------------------

// GET /api/dashboard/overview
#[OA\Get(
    path: '/api/dashboard/overview',
    summary: 'Get fleet overview (US 6.1)',
    security: [['bearerAuth' => []]],
    tags: ['Dashboard'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Fleet overview with status counts',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'total_bikes', type: 'integer', example: 50),
                        new OA\Property(
                            property: 'by_status',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'available', type: 'integer', example: 30),
                                new OA\Property(property: 'rented', type: 'integer', example: 15),
                                new OA\Property(property: 'maintenance', type: 'integer', example: 4),
                                new OA\Property(property: 'retired', type: 'integer', example: 1),
                            ]
                        ),
                        new OA\Property(property: 'active_rentals', type: 'integer', example: 15),
                        new OA\Property(property: 'total_customers', type: 'integer', example: 120),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_dashboard permission'),
    ]
)]
class GetFleetOverviewEndpoint
{
}

// GET /api/dashboard/today
#[OA\Get(
    path: '/api/dashboard/today',
    summary: 'Get today\'s activity (US 6.2)',
    security: [['bearerAuth' => []]],
    tags: ['Dashboard'],
    parameters: [
        new OA\Parameter(
            name: 'date',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', format: 'date'),
            description: 'Specific date to query (defaults to today)'
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Today\'s activity details',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'date', type: 'string', format: 'date', example: '2024-03-15'),
                        new OA\Property(
                            property: 'rentals',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'starting_today', type: 'integer', example: 5),
                                new OA\Property(property: 'returning_today', type: 'integer', example: 3),
                                new OA\Property(property: 'late_returns', type: 'integer', example: 1),
                            ]
                        ),
                        new OA\Property(
                            property: 'maintenances',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'scheduled_today', type: 'integer', example: 2),
                                new OA\Property(property: 'completed_today', type: 'integer', example: 1),
                                new OA\Property(property: 'urgent_pending', type: 'integer', example: 0),
                            ]
                        ),
                        new OA\Property(
                            property: 'details',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'starting_rentals', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'expected_returns', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'scheduled_maintenances', type: 'array', items: new OA\Items(type: 'object')),
                            ]
                        ),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_dashboard permission'),
    ]
)]
class GetTodayActivityEndpoint
{
}

// GET /api/dashboard/kpis/utilization
#[OA\Get(
    path: '/api/dashboard/kpis/utilization',
    summary: 'Get utilization KPIs (US 6.3)',
    security: [['bearerAuth' => []]],
    tags: ['Dashboard - KPIs'],
    parameters: [
        new OA\Parameter(
            name: 'period',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['day', 'week', 'month', 'year']),
            description: 'Period for KPI calculation (default: month)'
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Utilization KPIs',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'utilization_rate', type: 'number', format: 'float', example: 72.5, description: 'Percentage of fleet utilization'),
                        new OA\Property(property: 'average_rental_duration_hours', type: 'number', format: 'float', example: 4.5),
                        new OA\Property(property: 'total_rentals_period', type: 'integer', example: 150),
                        new OA\Property(property: 'period', type: 'string', example: 'month'),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_dashboard permission'),
    ]
)]
class GetUtilizationKpiEndpoint
{
}

// GET /api/dashboard/kpis/revenue
#[OA\Get(
    path: '/api/dashboard/kpis/revenue',
    summary: 'Get revenue KPIs (US 6.3)',
    security: [['bearerAuth' => []]],
    tags: ['Dashboard - KPIs'],
    parameters: [
        new OA\Parameter(
            name: 'period',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['day', 'week', 'month', 'year']),
            description: 'Period for KPI calculation (default: month)'
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Revenue KPIs',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'total_revenue', type: 'number', format: 'float', example: 12500.00, description: 'Total revenue in euros'),
                        new OA\Property(property: 'rev_pav', type: 'number', format: 'float', example: 250.00, description: 'Revenue Per Available Vehicle'),
                        new OA\Property(property: 'average_ticket', type: 'number', format: 'float', example: 45.50, description: 'Average rental amount'),
                        new OA\Property(property: 'period', type: 'string', example: 'month'),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_dashboard permission'),
    ]
)]
class GetRevenueKpiEndpoint
{
}

// GET /api/dashboard/kpis/maintenance
#[OA\Get(
    path: '/api/dashboard/kpis/maintenance',
    summary: 'Get maintenance KPIs (US 6.3)',
    security: [['bearerAuth' => []]],
    tags: ['Dashboard - KPIs'],
    parameters: [
        new OA\Parameter(
            name: 'period',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['day', 'week', 'month', 'year']),
            description: 'Period for KPI calculation (default: month)'
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Maintenance KPIs',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'total_maintenances', type: 'integer', example: 25),
                        new OA\Property(property: 'mttr_hours', type: 'number', format: 'float', example: 4.2, description: 'Mean Time To Repair in hours'),
                        new OA\Property(property: 'maintenance_cost', type: 'number', format: 'float', example: 850.00, description: 'Total maintenance cost in euros'),
                        new OA\Property(
                            property: 'by_type',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'preventive', type: 'integer', example: 15),
                                new OA\Property(property: 'curative', type: 'integer', example: 10),
                            ]
                        ),
                        new OA\Property(property: 'period', type: 'string', example: 'month'),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_dashboard permission'),
    ]
)]
class GetMaintenanceKpiEndpoint
{
}

// GET /api/dashboard/kpis/top-bikes
#[OA\Get(
    path: '/api/dashboard/kpis/top-bikes',
    summary: 'Get top performing bikes (US 6.3)',
    security: [['bearerAuth' => []]],
    tags: ['Dashboard - KPIs'],
    parameters: [
        new OA\Parameter(
            name: 'limit',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer', default: 10),
            description: 'Number of bikes to return'
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Top performing bikes by revenue and rentals',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'top_bikes',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'bike_id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'internal_number', type: 'string', example: 'VTT-001'),
                                    new OA\Property(property: 'brand_name', type: 'string', example: 'Giant'),
                                    new OA\Property(property: 'model_name', type: 'string', example: 'Trance X'),
                                    new OA\Property(property: 'total_rentals', type: 'integer', example: 45),
                                    new OA\Property(property: 'total_revenue', type: 'number', format: 'float', example: 2250.00),
                                ],
                                type: 'object'
                            )
                        ),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_dashboard permission'),
    ]
)]
class GetTopBikesKpiEndpoint
{
}

// GET /api/dashboard/alerts
#[OA\Get(
    path: '/api/dashboard/alerts',
    summary: 'Get centralized alerts (US 6.4)',
    security: [['bearerAuth' => []]],
    tags: ['Dashboard'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Centralized alerts from all sources',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'alerts',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'type', type: 'string', enum: ['late_return', 'urgent_maintenance', 'bike_long_unavailable', 'maintenance_long_running']),
                                    new OA\Property(property: 'severity', type: 'string', enum: ['high', 'medium', 'low']),
                                    new OA\Property(property: 'message', type: 'string', example: 'Retour en retard de 2 jour(s)'),
                                    new OA\Property(property: 'rental_id', type: 'string', format: 'uuid', nullable: true),
                                    new OA\Property(property: 'maintenance_id', type: 'string', format: 'uuid', nullable: true),
                                    new OA\Property(property: 'bike_id', type: 'string', format: 'uuid', nullable: true),
                                    new OA\Property(property: 'customer_id', type: 'string', format: 'uuid', nullable: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                ],
                                type: 'object'
                            )
                        ),
                        new OA\Property(
                            property: 'counts_by_severity',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'high', type: 'integer', example: 3),
                                new OA\Property(property: 'medium', type: 'integer', example: 5),
                                new OA\Property(property: 'low', type: 'integer', example: 2),
                            ]
                        ),
                        new OA\Property(property: 'total', type: 'integer', example: 10),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_dashboard permission'),
    ]
)]
class GetCentralizedAlertsEndpoint
{
}

// GET /api/rentals/bikes/{bikeId}
#[OA\Get(
    path: '/api/rentals/bikes/{bikeId}',
    summary: 'Get all rentals for a specific bike',
    security: [['bearerAuth' => []]],
    tags: ['Rentals'],
    parameters: [
        new OA\Parameter(
            name: 'bikeId',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string', format: 'uuid'),
            description: 'Bike ID'
        ),
        new OA\Parameter(
            name: 'filter',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                enum: ['all', 'past', 'current', 'upcoming'],
                default: 'all'
            ),
            description: 'Filter rentals by status: all (default), past (completed/cancelled), current (active), upcoming (pending)'
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of rentals for the bike',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'bike_id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'total_count', type: 'integer', example: 15),
                        new OA\Property(
                            property: 'rentals',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'customer_id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'start_date', type: 'string', format: 'date-time', example: '2024-01-15 10:00:00'),
                                    new OA\Property(property: 'expected_return_date', type: 'string', format: 'date-time', example: '2024-01-20 18:00:00'),
                                    new OA\Property(property: 'actual_return_date', type: 'string', format: 'date-time', nullable: true, example: '2024-01-20 17:30:00'),
                                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'active', 'completed', 'cancelled']),
                                    new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 350.00),
                                    new OA\Property(property: 'deposit_amount', type: 'number', format: 'float', example: 200.00),
                                    new OA\Property(property: 'deposit_status', type: 'string', enum: ['held', 'released', 'partial', 'retained']),
                                    new OA\Property(property: 'deposit_retained', type: 'number', format: 'float', nullable: true, example: 50.00),
                                    new OA\Property(property: 'cancellation_reason', type: 'string', nullable: true, example: 'Customer request'),
                                ],
                                type: 'object'
                            )
                        ),
                    ],
                    type: 'object'
                )
            )
        ),
        new OA\Response(response: 400, description: 'Bad Request - Invalid filter parameter'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden - requires view_rentals permission'),
    ]
)]
class GetBikeRentalsEndpoint
{
}
