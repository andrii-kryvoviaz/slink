<?php

declare(strict_types=1);

namespace Slink\Image\Application\Http\RequestValueResolver;

use Slink\Image\Infrastructure\ChunkedUpload\Exception\InvalidUploadTokenException;
use Slink\Image\Infrastructure\ChunkedUpload\UploadToken;
use Slink\Image\Infrastructure\ChunkedUpload\UploadTokenCodec;
use Slink\Shared\Domain\Exception\ForbiddenException;
use Slink\User\Domain\Contracts\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final readonly class UploadTokenValueResolver implements ValueResolverInterface {
  public const string HEADER = 'X-Upload-Token';

  public function __construct(
    private UploadTokenCodec $codec,
    private Security $security,
  ) {
  }

  /**
   * @inheritDoc
   * @return iterable<UploadToken>
   */
  public function resolve(Request $request, ArgumentMetadata $argument): iterable {
    if ($argument->getType() !== UploadToken::class) {
      return [];
    }

    $raw = $request->headers->get(self::HEADER);

    if ($raw === null || $raw === '') {
      throw new InvalidUploadTokenException('Missing upload token.');
    }

    $token = $this->codec->decode($raw, \time());

    $uploadId = $request->attributes->get('uploadId');

    if ($token->getUploadId() !== $uploadId) {
      throw new InvalidUploadTokenException('Upload token does not match the upload identifier.');
    }

    $user = $this->security->getUser();
    $currentUserId = $user instanceof UserInterface ? $user->getIdentifier() : null;

    if ($token->getOwnerId() !== $currentUserId) {
      throw new ForbiddenException('Upload token owner mismatch.');
    }

    return [$token];
  }
}
