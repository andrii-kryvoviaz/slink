<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Share;

use Slink\Share\Application\Command\UnpublishShare\UnpublishShareCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/share/{id}/unpublish', name: 'unpublish_share', methods: ['PUT'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class UnpublishShareController {
  use CommandTrait;

  public function __invoke(string $id): ApiResponse {
    $this->handle(new UnpublishShareCommand($id));

    return ApiResponse::empty();
  }
}
