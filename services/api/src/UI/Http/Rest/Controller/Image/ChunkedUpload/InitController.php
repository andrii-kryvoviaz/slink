<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image\ChunkedUpload;

use Slink\Image\Infrastructure\ChunkedUpload\UploadToken;
use Slink\Image\Infrastructure\ChunkedUpload\UploadTokenCodec;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Security\Voter\GuestAccessVoter;
use Slink\User\Domain\Contracts\UserInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Controller\Image\ChunkedUpload\Request\InitUploadRequest;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/upload/chunked', name: 'init_chunked_upload', methods: ['POST'])]
#[IsGranted(GuestAccessVoter::GUEST_UPLOAD_ALLOWED)]
final readonly class InitController {
  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private UploadTokenCodec $tokenCodec,
    private ConfigurationProviderInterface $configurationProvider,
    private ParameterBagInterface $parameterBag,
  ) {
  }

  public function __invoke(
    #[MapRequestPayload] InitUploadRequest $request,
    #[CurrentUser] ?UserInterface $user,
  ): ApiResponse {
    $maxSize = convertSizeToBytes((string) $this->configurationProvider->get('image.maxSize'));

    if ($request->totalSize > $maxSize) {
      throw new HttpException(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'The uploaded file exceeds the maximum allowed size.');
    }

    /** @var array<string> $supportedFormats */
    $supportedFormats = (array) $this->parameterBag->get('supported_image_formats');

    if (!\in_array($request->mimeType, $supportedFormats, true)) {
      throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, \sprintf('The mime type %s is not supported.', $request->mimeType));
    }

    $chunkSize = convertSizeToBytes((string) $this->configurationProvider->get('image.chunkSize'));
    $totalChunks = \max(1, (int) \ceil($request->totalSize / $chunkSize));

    $token = UploadToken::create(
      uploadId: ID::generate()->toString(),
      ownerId: $user?->getIdentifier(),
      isGuest: $user === null,
      fileName: $request->fileName,
      totalSize: $request->totalSize,
      mimeType: $request->mimeType,
      isPublic: $request->isPublic,
      description: $request->description,
      tagIds: $request->tagIds,
      collectionIds: $request->collectionIds,
      totalChunks: $totalChunks,
      expiresAt: \time() + UploadTokenCodec::TTL,
    );

    return ApiResponse::fromPayload([
      'data' => [
        'uploadId' => $token->getUploadId(),
        'token' => $this->tokenCodec->encode($token),
        'chunkSize' => $chunkSize,
      ],
    ], Response::HTTP_CREATED);
  }
}
