<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\Image\Domain\Enum\License;
use Slink\Image\Infrastructure\ReadModel\Repository\ImageLicenseRepository;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;

#[ORM\Table(name: '`image_license`')]
#[ORM\Entity(repositoryClass: ImageLicenseRepository::class)]
class ImageLicenseView extends AbstractView {
  #[ORM\Id]
  #[ORM\Column(type: 'uuid')]
  private string $uuid;

  #[ORM\OneToOne(inversedBy: 'license', targetEntity: ImageView::class)]
  #[ORM\JoinColumn(name: 'image_id', referencedColumnName: 'uuid', nullable: false, onDelete: 'CASCADE')]
  private ImageView $image;

  #[ORM\Column(type: 'string', length: 32, enumType: License::class)]
  private License $license;

  public function __construct(
    string $uuid,
    ImageView $image,
    License $license,
  ) {
    $this->uuid = $uuid;
    $this->image = $image;
    $this->license = $license;
  }

  public function getId(): string {
    return $this->uuid;
  }

  public function getImage(): ImageView {
    return $this->image;
  }

  public function getLicense(): License {
    return $this->license;
  }

  public function setLicense(License $license): void {
    $this->license = $license;
  }
}
