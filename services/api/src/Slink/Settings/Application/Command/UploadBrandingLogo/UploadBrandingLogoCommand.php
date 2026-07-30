<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Command\UploadBrandingLogo;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UploadBrandingLogoCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\File(
      maxSize: '2M',
      mimeTypes: [
        'image/png',
        'image/jpeg',
        'image/webp',
        'image/gif',
        'image/svg+xml',
      ],
      mimeTypesMessage: 'The mime type {{ type }} is not supported.',
    )]
    private File $file,
  ) {
  }

  public function getFile(): File {
    return $this->file;
  }
}
