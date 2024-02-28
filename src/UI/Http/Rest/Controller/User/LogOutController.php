<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\LogOut\LogOutCommand;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/auth/logout', name: 'logout', methods: ['POST'])]
final class LogOutController {
  use CommandTrait;
  
  public function __invoke(
    #[MapRequestPayload] LogOutCommand $command,
  ): ApiResponse {
    $this->handle($command);

    return ApiResponse::empty();
  }
}