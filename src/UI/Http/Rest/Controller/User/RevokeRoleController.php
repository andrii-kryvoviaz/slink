<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Application\Command\RevokeRole\RevokeRoleCommand;
use Slink\User\Application\Query\User\FindUserById\FindUserByIdQuery;
use Slink\User\Domain\Enum\UserRole;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/user/role', name: 'user_revoke_role', methods: ['DELETE'])]
#[IsGranted(UserRole::Admin->value)]
final readonly class RevokeRoleController {
  use CommandTrait;
  use QueryTrait;
  
  public function __invoke(
    #[MapRequestPayload] RevokeRoleCommand $command,
  ): ApiResponse {
    $this->handle($command);
    
    $query = new FindUserByIdQuery((string) $command->getId());
    $user = $this->ask($query->withContext(['groups' => ['internal', 'public']]));
    
    return ApiResponse::one($user);
  }
}
