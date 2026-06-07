<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image\ChunkedUpload;

use Slink\Image\Infrastructure\ChunkedUpload\ChunkedUploadCompleter;
use Slink\Image\Infrastructure\ChunkedUpload\Exception\ChunkIndexOutOfRangeException;
use Slink\Image\Infrastructure\ChunkedUpload\Exception\ChunkTooLargeException;
use Slink\Image\Infrastructure\ChunkedUpload\Storage\ChunkStorageInterface;
use Slink\Image\Infrastructure\ChunkedUpload\UploadToken;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Infrastructure\Security\Voter\GuestAccessVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(
  path: '/upload/chunked/{uploadId}/{index}',
  name: 'upload_chunk',
  requirements: ['index' => '\d+'],
  methods: ['PUT'],
)]
#[IsGranted(GuestAccessVoter::GUEST_UPLOAD_ALLOWED)]
final readonly class UploadChunkController {
  private const string COMPLETE_HEADER = 'X-Upload-Complete';

  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private ChunkStorageInterface $chunkStorage,
    private ChunkedUploadCompleter $completer,
    private ConfigurationProviderInterface $configurationProvider,
  ) {
  }

  public function __invoke(
    string $uploadId,
    int $index,
    Request $request,
    UploadToken $token,
  ): ApiResponse {
    if ($this->isCompletionRequest($request)) {
      return $this->handleCompletion($token, $index, $request);
    }

    $this->storeChunk($token, $index, $request);

    return ApiResponse::fromPayload([
      'data' => [
        'index' => $index,
      ],
    ]);
  }

  private function handleCompletion(UploadToken $token, int $index, Request $request): ApiResponse {
    \set_time_limit(0);

    $completedImageId = $this->chunkStorage->findCompletedImageId($token->getUploadId());

    if ($completedImageId !== null) {
      return $this->createdResponse($completedImageId, $token, Response::HTTP_OK);
    }

    $this->storeChunk($token, $index, $request);

    $result = $this->completer->complete($token);

    return $this->createdResponse(
      $result->imageId,
      $token,
      $result->created ? Response::HTTP_CREATED : Response::HTTP_OK,
    );
  }

  private function storeChunk(UploadToken $token, int $index, Request $request): void {
    if ($index < 0 || $index >= $token->getTotalChunks()) {
      throw new ChunkIndexOutOfRangeException();
    }

    $content = $request->getContent();
    $chunkSize = convertSizeToBytes((string) $this->configurationProvider->get('image.chunkSize'));

    if (\strlen($content) > $chunkSize) {
      throw new ChunkTooLargeException();
    }

    $this->chunkStorage->writeChunk($token->getUploadId(), $index, $content);
  }

  private function createdResponse(string $imageId, UploadToken $token, int $status): ApiResponse {
    return ApiResponse::fromPayload([
      'data' => [
        'id' => $imageId,
      ],
    ], $status);
  }

  private function isCompletionRequest(Request $request): bool {
    return \filter_var($request->headers->get(self::COMPLETE_HEADER), FILTER_VALIDATE_BOOLEAN);
  }
}
