<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image\ChunkedUpload;

use Slink\Image\Infrastructure\ChunkedUpload\ChunkedUploadLock;
use Slink\Image\Infrastructure\ChunkedUpload\Storage\ChunkStorageInterface;
use Slink\Image\Infrastructure\ChunkedUpload\UploadToken;
use Slink\Shared\Infrastructure\Security\Voter\GuestAccessVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/upload/chunked/{uploadId}', name: 'abort_chunked_upload', methods: ['DELETE'])]
#[IsGranted(GuestAccessVoter::GUEST_UPLOAD_ALLOWED)]
final readonly class AbortController {
  public function __construct(
    private ChunkStorageInterface $chunkStorage,
    private ChunkedUploadLock $uploadLock,
  ) {
  }

  public function __invoke(
    string $uploadId,
    UploadToken $token,
  ): ApiResponse {
    $lock = $this->uploadLock->acquire($token->getUploadId());

    try {
      $this->chunkStorage->deleteUpload($token->getUploadId());
    } finally {
      $lock->release();
    }

    return ApiResponse::empty(Response::HTTP_NO_CONTENT);
  }
}
