<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UploadImage;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UploadImageCommand implements CommandInterface {
  use EnvelopedMessage;
  
  private ID $id;
  
  /**
   * @param File $image
   * @param bool $isPublic
   * @param string $description
   * @param array<string> $tagIds
   */
  public function __construct(
    #[Assert\Image(
      mimeTypesMessage: <<<'MESSAGE'
        The mime type {{ type }} is not supported.
        <a href="/help/faq#supported-image-formats"
            class="text-indigo-500 hover:text-indigo-700 mt-2 block"
        >See supported formats</a>
        MESSAGE,
    )]
    private File $image,
    
    private bool $isPublic = false,
    
    #[Assert\Length(max: 255)]
    private string $description = '',
    
    #[Assert\All([
      new Assert\Uuid(message: 'Invalid tag ID format')
    ])]
    private array $tagIds = [],
  ) {
    $this->id = ID::generate();
  }
  
  /**
   * @return ID
   */
  public function getId(): ID {
    return $this->id;
  }
  
  /**
   * @return File
   */
  public function getImageFile(): File {
    return $this->image;
  }
  
  /**
   * @return bool
   */
  public function isPublic(): bool {
    return $this->isPublic;
  }
  
  /**
   * @return string
   */
  public function getDescription(): string {
    return $this->description;
  }
  
  /**
   * @return array<string>
   */
  public function getTagIds(): array {
    return $this->tagIds;
  }
}