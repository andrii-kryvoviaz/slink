<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slink\Image\Application\Query\GetPublicImageContent\GetPublicImageContentQuery;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\Shared\Infrastructure\Security\Voter\GuestAccessVoter;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ContentResponse;

#[AsController]
#[Route(path: '/image/public/{id}.{ext}', name: 'get_public_image_content', methods: ['GET'])]
#[IsGranted(GuestAccessVoter::GUEST_VIEW_ALLOWED)]
final readonly class GetPublicImageContentController {
  use CommandTrait;
  use QueryTrait;

  public function __invoke(
    string $id,
    #[CurrentUser] ?JwtUser $user = null,
  ): ContentResponse {
    $imageData = $this->ask(new GetPublicImageContentQuery($id));

    $this->handle((new AddImageViewCountCommand($id))->withContext([
      'userId' => $user?->getIdentifier(),
    ]));

    return ContentResponse::file($imageData);
  }
}
