<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\UpdateUserPreferences;

use Slink\Image\Domain\Enum\License;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Slink\User\Domain\ValueObject\UserPreferences;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateUserPreferencesCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\Choice(callback: [License::class, 'values'], message: 'Invalid license type.')]
    private ?string $defaultLicense = null,
    private bool $syncLicenseToImages = false,
  ) {
  }

  public function getPreferences(): UserPreferences {
    $license = $this->defaultLicense ? License::tryFrom($this->defaultLicense) : null;
    return UserPreferences::create($license);
  }

  public function getDefaultLicense(): ?License {
    return $this->defaultLicense ? License::tryFrom($this->defaultLicense) : null;
  }

  public function shouldSyncLicenseToImages(): bool {
    return $this->syncLicenseToImages;
  }
}
