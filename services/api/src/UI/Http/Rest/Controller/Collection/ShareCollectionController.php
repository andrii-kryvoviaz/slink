<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Collection;

use Slink\Collection\Application\Query\GetCollection\GetCollectionQuery;
use Slink\Share\Application\Command\CreateShare\CreateShareCommand;
use Slink\Share\Application\Command\CreateShare\CreateShareResult;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareParams;
use Slink\Share\Domain\ValueObject\ShareResponse;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/collection/{id}/share', name: 'share_collection', methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class ShareCollectionController {
  use CommandTrait;
  use QueryTrait;

  public function __construct(
    private ShareServiceInterface $shareService,
  ) {}

  public function __invoke(
    #[CurrentUser] JWTUser $user,
    string $id,
  ): ApiResponse {
    $query = new GetCollectionQuery($id);

    $collection = $this->ask($query->withContext([
      'userId' => $user->getIdentifier()
    ]));

    if ($collection === null) {
      return ApiResponse::empty(Response::HTTP_NOT_FOUND);
    }

    $shareable = ShareableReference::forCollection(ID::fromString($id));
    $params = ShareParams::fromShareable($shareable);
    $command = new CreateShareCommand($params);

    /** @var CreateShareResult $result */
    $result = $this->handleSync($command);

    $response = ShareResponse::fromShare(
      $result->getShare(),
      $this->shareService->resolveUrl($result->getShare()),
      $result->wasCreated(),
    );

    return ApiResponse::fromPayload(
      $response->toPayload(),
      $result->wasCreated() ? Response::HTTP_CREATED : Response::HTTP_OK
    );
  }
}
