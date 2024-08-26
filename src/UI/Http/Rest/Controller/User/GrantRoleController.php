<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Application\Command\GrantRole\GrantRoleCommand;
use Slink\User\Application\Query\User\FindUserById\FindUserByIdQuery;
use Slink\User\Domain\Enum\UserRole;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/user/role', name: 'user_grant_role', methods: ['POST'])]
#[IsGranted(UserRole::Admin->value)]
final readonly class GrantRoleController {
  use CommandTrait;
  use QueryTrait;
  
  public function __invoke(
    #[MapRequestPayload] GrantRoleCommand $command,
  ): ApiResponse {
    $this->handle($command);
    
    $query = new FindUserByIdQuery((string) $command->getId());
    $user = $this->ask($query->withContext(['groups' => ['internal', 'public']]));
    
    return ApiResponse::one($user);
  }
}
