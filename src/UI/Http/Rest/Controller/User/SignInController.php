<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Settings\Domain\Service\ConfigurationProvider;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Application\Command\SignIn\SignInCommand;
use Slink\User\Application\Query\Auth\GenerateTokenPair\GenerateTokenPairQuery;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/auth/login', name: 'auth_user', methods: ['POST'])]
class SignInController {
  use CommandTrait;
  use QueryTrait;
  
  public function __invoke(
    #[MapRequestPayload] SignInCommand $command,
    ConfigurationProvider $configurationProvider,
  ): ApiResponse {
    $tokenQuery = new GenerateTokenPairQuery($command->getUsername());
    $tokenPair = $this->ask($tokenQuery->withContext([
      'approvalRequired' => $configurationProvider->get('user.approvalRequired'),
    ]));
    
    $this->handle($command->withContext([
      'refreshToken' => $tokenPair->getRefreshToken(),
    ]));
    
    return ApiResponse::fromSerializable($tokenPair);
  }
}