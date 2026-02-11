<?php

declare(strict_types=1);

namespace Auth\Application\GetCurrentUser;

use Auth\Domain\UserRepositoryInterface;
use Tenant\Application\TenantContext;

final class GetCurrentUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly TenantContext $tenantContext,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function handle(GetCurrentUserQuery $query): GetCurrentUserResponse
    {
        $user = $this->users->findById($query->userId);

        if ($user === null) {
            throw new UserNotFoundException($query->userId);
        }

        // Ne pas inclure le tenant pour les super admins
        $tenant = null;
        if (! $user->isSuperAdmin() && $this->tenantContext->hasTenant()) {
            $tenant = $this->tenantContext->tenant();
        }

        return GetCurrentUserResponse::fromUser($user, $tenant);
    }
}
