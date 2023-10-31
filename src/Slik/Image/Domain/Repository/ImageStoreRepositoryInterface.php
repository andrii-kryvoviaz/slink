<?php

declare(strict_types=1);

namespace Slik\Image\Domain\Repository;

use Slik\Image\Domain\Image;
use Slik\Shared\Domain\ValueObject\ID;

interface ImageStoreRepositoryInterface {
  
  public function get(ID $id): Image;
  
  public function store(Image $image): void;
}