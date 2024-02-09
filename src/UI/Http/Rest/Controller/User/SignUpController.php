<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;


use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\SignUp\SignUpCommand;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use UI\Http\Rest\Response\ApiResponse;


#[AsController]
#[Route(path: '/user', name: 'signup_user', methods: ['POST'])]
final class SignUpController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] SignUpCommand $command,
  ): ApiResponse {
    $this->handle($command);

    return ApiResponse::created('/user');
  }
}