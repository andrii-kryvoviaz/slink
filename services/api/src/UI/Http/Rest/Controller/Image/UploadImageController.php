<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Http\RequestValueResolver\FileRequestValueResolver;
use Slink\Shared\Infrastructure\Security\Voter\GuestAccessVoter;
use Slink\User\Domain\Contracts\UserInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/upload', name: 'upload_image', methods: ['POST', 'PUT'])]
#[IsGranted(GuestAccessVoter::GUEST_UPLOAD_ALLOWED)]
final class UploadImageController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload(
      resolver: FileRequestValueResolver::class
    )] UploadImageCommand $command,
    #[CurrentUser] ?UserInterface $user
  ): ApiResponse {
    $this->handleSync($command->withContext(['userId' => $user?->getIdentifier()]));

    return ApiResponse::created(
      $command->getId()->toString(),
      $user !== null ? "image/{$command->getId()}/detail" : null,
    );
  }
}
