<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Repository;

use Slink\Image\Domain\Image;
use Slink\Shared\Domain\ValueObject\ID;

interface ImageStoreRepositoryInterface {
  
  public function get(ID $id): Image;
  
  public function store(Image $image): void;
}