<?php

declare(strict_types=1);

namespace Slink\Share\Domain\AccessRule;

use Slink\Share\Domain\ValueObject\HashedSharePassword;

interface PasswordProtected {
  public function getId(): string;

  public function getPassword(): ?HashedSharePassword;
}
