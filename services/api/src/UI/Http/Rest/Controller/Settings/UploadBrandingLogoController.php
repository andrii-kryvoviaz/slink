<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Settings;

use Slink\Settings\Application\Command\UploadBrandingLogo\UploadBrandingLogoCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Http\RequestValueResolver\FileRequestValueResolver;
use Slink\User\Domain\Enum\UserRole;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/settings/customization/logo', methods: ['POST'])]
#[IsGranted(UserRole::Admin->value)]
final readonly class UploadBrandingLogoController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload(
      resolver: FileRequestValueResolver::class
    )] UploadBrandingLogoCommand $command,
  ): ApiResponse {
    /** @var array{url: string} $payload */
    $payload = $this->handleSync($command);

    return ApiResponse::fromPayload($payload);
  }
}
