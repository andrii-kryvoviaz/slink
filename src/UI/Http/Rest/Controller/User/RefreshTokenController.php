<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Application\Command\RefreshToken\RefreshTokenCommand;
use Slink\User\Application\Query\Auth\RotateTokenPair\RotateTokenPairQuery;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/auth/refresh', name: 'refresh_token', methods: ['POST'])]
final class RefreshTokenController {
  use CommandTrait;
  use QueryTrait;
  
  public function __invoke(
    #[MapRequestPayload] RefreshTokenCommand $command,
  ): ApiResponse {
    $tokenPair = $this->ask(new RotateTokenPairQuery($command->getRefreshToken()));
    
    $this->handle($command->withContext([
      'updatedRefreshToken' => $tokenPair->getRefreshToken(),
    ]));
    
    return ApiResponse::fromSerializable($tokenPair);
  }
}