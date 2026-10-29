<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Service;

use Slink\Shared\Domain\ValueObject\EscapedString;
use Slink\User\Domain\Service\UsernameGeneratorInterface;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Username;
use Symfony\Component\String\Slugger\SluggerInterface;

final readonly class UsernameGenerator implements UsernameGeneratorInterface {
  public function __construct(private SluggerInterface $slugger) {
  }

  public function generate(DisplayName $displayName): ?Username {
    $text = EscapedString::fromTrusted($displayName->toString())->unescaped();
    $username = $this->slugger->slug($text, '.')->lower()->truncate(Username::MAX_LENGTH)->toString();

    if (strlen($username) < Username::MIN_LENGTH) {
      return null;
    }

    return Username::fromString($username);
  }
}
