<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Admin\Image;

use Slink\Image\Application\Command\DeleteImage\AdminDeleteImageCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Domain\Enum\UserRole;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/admin/image/{id}', name: 'admin_delete_image', methods: ['DELETE'])]
#[IsGranted(UserRole::Admin->value)]
final class DeleteImageController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] AdminDeleteImageCommand $command,
    string $id,
  ): ApiResponse {
    $this->handle($command->withContext([
      'id' => $id,
    ]));

    return ApiResponse::empty();
  }
}
