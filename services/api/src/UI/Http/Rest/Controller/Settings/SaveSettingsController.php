<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Settings;

use Slink\Settings\Application\Command\SaveSettings\SaveSettingsCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Domain\Enum\UserRole;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/settings', methods: ['POST'])]
#[IsGranted(UserRole::Admin->value)]
final readonly class SaveSettingsController {
  use CommandTrait;
  
  public function __invoke(
    #[MapRequestPayload] SaveSettingsCommand $command
  ): ApiResponse {
    $this->handle($command);
    
    return ApiResponse::empty();
  }
}