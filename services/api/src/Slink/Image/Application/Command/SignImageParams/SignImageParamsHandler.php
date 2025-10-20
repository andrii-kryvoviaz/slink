<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\SignImageParams;

use Slink\Image\Domain\Service\ImageUrlSignatureInterface;
use Slink\Image\Domain\ValueObject\SignedImageParams;
use Slink\Shared\Application\Command\CommandHandlerInterface;

final readonly class SignImageParamsHandler implements CommandHandlerInterface {
  public function __construct(
    private ImageUrlSignatureInterface $signatureService
  ) {
  }

  public function __invoke(SignImageParamsCommand $command): SignedImageParams {
    $params = array_filter([
      'width' => $command->getWidth(),
      'height' => $command->getHeight(),
      'crop' => $command->isCropped(),
    ], fn($value) => $value !== null && $value !== false);

    $signature = $this->signatureService->sign(
      $command->getImageId(),
      $params
    );

    return SignedImageParams::create(
      $command->getImageId(),
      $command->getWidth(),
      $command->getHeight(),
      $command->isCropped(),
      $signature
    );
  }
}
