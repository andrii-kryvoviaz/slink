<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\UntagImage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\UntagImage\UntagImageCommand;

final class UntagImageCommandTest extends TestCase {

  #[Test]
  public function itCreatesCommandWithValidIds(): void {
    $imageId = '550e8400-e29b-41d4-a716-446655440000';
    $tagId = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
    
    $command = new UntagImageCommand($imageId, $tagId);
    
    $this->assertEquals($imageId, $command->getImageId());
    $this->assertEquals($tagId, $command->getTagId());
  }

  #[Test]
  public function itStoresIdsCorrectly(): void {
    $imageId = 'image-123';
    $tagId = 'tag-456';
    
    $command = new UntagImageCommand($imageId, $tagId);
    
    $this->assertEquals($imageId, $command->getImageId());
    $this->assertEquals($tagId, $command->getTagId());
  }

  #[Test]
  public function itHandlesDifferentUuidFormats(): void {
    $imageId = '01234567-89ab-cdef-0123-456789abcdef';
    $tagId = 'FEDCBA98-7654-3210-FEDC-BA9876543210';
    
    $command = new UntagImageCommand($imageId, $tagId);
    
    $this->assertEquals($imageId, $command->getImageId());
    $this->assertEquals($tagId, $command->getTagId());
  }
}