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
class OpenApi {}

// ------------------------------ PATHS ------------------------------

// GET /api/me
#[OA\Get(
    path: '/api/me',
    summary: 'Get current authenticated user',
    security: [['bearerAuth' => []]],
    tags: ['Auth'],
    responses: [
        new OA\Response(response: 200, description: 'Current user returned'),
        new OA\Response(response: 401, description: 'Unauthorized')
    ]
)]
class MeEndpoint {}

// GET /api/users
#[OA\Get(
    path: '/api/users',
    summary: 'List users (admin only)',
    security: [['bearerAuth' => []]],
    tags: ['Users'],
    parameters: [
        new OA\Parameter(name: 'role', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Filter by role'),
        new OA\Parameter(name: 'is_active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean'), description: 'Filter by active status')
    ],
    responses: [
        new OA\Response(response: 200, description: 'List of users'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden')
    ]
)]
class ListUsersEndpoint {}

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
            new OA\Property(property: 'role', type: 'string', example: 'ADMIN')
        ],
        type: 'object'
    ))),
    responses: [
        new OA\Response(response: 200, description: 'Role updated'),
        new OA\Response(response: 400, description: 'Bad request'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 403, description: 'Forbidden')
    ]
)]
class UpdateUserRoleEndpoint {}

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
        new OA\Response(response: 403, description: 'Forbidden')
    ]
)]
class ToggleUserStatusEndpoint {}

// GET /api/user (Sanctum protected default route)
#[OA\Get(
    path: '/api/user',
    summary: 'Get authenticated user (Sanctum)',
    security: [['bearerAuth' => []]],
    tags: ['Auth'],
    responses: [
        new OA\Response(response: 200, description: 'Current user returned'),
        new OA\Response(response: 401, description: 'Unauthorized')
    ]
)]
class LaravelUserEndpoint {}

// GET /api/login (public example route)
#[OA\Get(
    path: '/api/login',
    summary: 'Login endpoint (placeholder)',
    tags: ['Auth'],
    responses: [
        new OA\Response(response: 200, description: 'Message returned')
    ]
)]
class LoginEndpoint {}
