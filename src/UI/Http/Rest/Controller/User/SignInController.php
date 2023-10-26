<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slik\Shared\Application\Command\CommandTrait;
use Slik\Shared\Application\Query\QueryTrait;
use Slik\User\Application\Command\SignIn\SignInCommand;
use Slik\User\Application\Query\Auth\GetToken\GetTokenQuery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/auth', name: 'auth_user', methods: ['POST'])]
class SignInController {
  use CommandTrait;
  use QueryTrait;
  
  public function __invoke(
    #[MapRequestPayload] SignInCommand $command,
  ): ApiResponse {
    $this->handle($command);
    
    $token = $this->ask(new GetTokenQuery($command->getUsername()));
    
    return ApiResponse::fromPayload(['token' => $token], Response::HTTP_OK);
  }
}