<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Application\Command\RefreshToken\RefreshTokenCommand;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/auth/refresh', name: 'refresh_token', methods: ['POST'])]
final class RefreshTokenController {
  use CommandTrait;
  use QueryTrait;
  
  public function __invoke(
    #[MapRequestPayload] RefreshTokenCommand $command,
  ): ApiResponse {
    $tokenPair = $this->handleSync($command);
    
    return ApiResponse::fromSerializable($tokenPair);
  }
}