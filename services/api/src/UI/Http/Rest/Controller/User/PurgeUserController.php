<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\PurgeUser\PurgeUserCommand;
use Slink\User\Domain\Enum\UserRole;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(
  path: '/user/{id}',
  name: 'purge_user',
  requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'],
  methods: ['DELETE']
)]
#[IsGranted(UserRole::Admin->value)]
final readonly class PurgeUserController {
  use CommandTrait;

  public function __invoke(string $id): ApiResponse {
    $this->handle(new PurgeUserCommand($id));

    return ApiResponse::empty();
  }
}
