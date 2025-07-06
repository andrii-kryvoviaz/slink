<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Http\RequestValueResolver\FileRequestValueResolver;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/guest/upload', name: 'guest_upload_image', methods: ['POST', 'PUT'])]
final readonly class GuestUploadImageController {
  use CommandTrait;
  
  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider
  ) {
  }
  
  public function __invoke(
    #[MapRequestPayload(
      resolver: FileRequestValueResolver::class
    )] UploadImageCommand $command
  ): ApiResponse {
    if (!$this->configurationProvider->get('access.allowGuestUploads')) {
      throw new AccessDeniedHttpException('Guest uploads are not allowed');
    }
    
    $this->handle($command);
    
    return ApiResponse::created($command->getId()->toString(), "/explore");
  }
}
