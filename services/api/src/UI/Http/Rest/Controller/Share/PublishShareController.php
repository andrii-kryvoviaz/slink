<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Share;

use Slink\Share\Application\Command\PublishShare\PublishShareCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[Route(path: '/share/{id}/publish', name: 'publish_share', methods: ['PUT'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class PublishShareController {
  use CommandTrait;

  public function __invoke(string $id): Response {
    $this->handle(new PublishShareCommand($id));

    return new Response(null, Response::HTTP_NO_CONTENT);
  }
}
