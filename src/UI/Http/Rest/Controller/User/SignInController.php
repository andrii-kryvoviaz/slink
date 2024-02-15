<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Application\Command\SignIn\SignInCommand;
use Slink\User\Application\Query\Auth\GenerateTokenPair\GenerateTokenPairQuery;
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
    
    $tokenPair = $this->ask(new GenerateTokenPairQuery($command->getUsername()));
    $this->handle($command);
    
    return ApiResponse::fromPayload($tokenPair->jsonSerialize());
  }
}