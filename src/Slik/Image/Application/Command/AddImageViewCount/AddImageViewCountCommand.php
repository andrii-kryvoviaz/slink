<?php

declare(strict_types=1);

namespace Slik\Image\Application\Command\AddImageViewCount;

use Slik\Shared\Application\Command\CommandInterface;

final readonly class AddImageViewCountCommand implements CommandInterface {
  public function __construct(private string $id) {
  }
  
  public function getId(): string {
    return $this->id;
  }
}