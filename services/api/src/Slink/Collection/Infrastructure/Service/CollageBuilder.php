<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Service;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Collection\Domain\Service\CollageBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(CollageBuilderInterface::class)]
final class CollageBuilder implements CollageBuilderInterface {
  private const int QUALITY = 80;

  private const array LAYOUTS = [
    1 => [['x' => 0, 'y' => 0, 'w' => 1, 'h' => 1]],
    2 => [
      ['x' => 0, 'y' => 0, 'w' => 0.5, 'h' => 1],
      ['x' => 0.5, 'y' => 0, 'w' => 0.5, 'h' => 1],
    ],
    3 => [
      ['x' => 0, 'y' => 0, 'w' => 0.6, 'h' => 1],
      ['x' => 0.6, 'y' => 0, 'w' => 0.4, 'h' => 0.5],
      ['x' => 0.6, 'y' => 0.5, 'w' => 0.4, 'h' => 0.5],
    ],
    4 => [
      ['x' => 0, 'y' => 0, 'w' => 0.5, 'h' => 0.5],
      ['x' => 0.5, 'y' => 0, 'w' => 0.5, 'h' => 0.5],
      ['x' => 0, 'y' => 0.5, 'w' => 0.5, 'h' => 0.5],
      ['x' => 0.5, 'y' => 0.5, 'w' => 0.5, 'h' => 0.5],
    ],
    5 => [
      ['x' => 0, 'y' => 0, 'w' => 0.5, 'h' => 1],
      ['x' => 0.5, 'y' => 0, 'w' => 0.25, 'h' => 0.5],
      ['x' => 0.75, 'y' => 0, 'w' => 0.25, 'h' => 0.5],
      ['x' => 0.5, 'y' => 0.5, 'w' => 0.25, 'h' => 0.5],
      ['x' => 0.75, 'y' => 0.5, 'w' => 0.25, 'h' => 0.5],
    ],
  ];

  public function build(array $images, int $width = 600, int $height = 400): ?string {
    if (empty($images)) {
      return null;
    }

    $normalized = array_map(fn(VipsImage $img) => $this->normalize($img), $images);
    $layout = self::LAYOUTS[count($normalized)] ?? self::LAYOUTS[5];

    return $this->compose($normalized, $layout, $width, $height);
  }

  private function normalize(VipsImage $image): VipsImage {
    return match ($image->bands) {
      1 => $image->bandjoin([$image, $image]),
      4 => $image->flatten(['background' => [255, 255, 255]]),
      default => $image,
    };
  }

  /**
   * @param VipsImage[] $images
   * @param array<int, array{x: float, y: float, w: float, h: float}> $layout
   */
  private function compose(array $images, array $layout, int $width, int $height): string {
    $rows = $this->groupByRow($images, $layout, $width, $height);

    return $this->joinVertically($rows)->writeToBuffer('.avif', ['Q' => self::QUALITY]);
  }

  /**
   * @param VipsImage[] $images
   * @param array<int, array{x: float, y: float, w: float, h: float}> $layout
   * @return array<string, list<array{x: float, image: VipsImage}>>
   */
  private function groupByRow(array $images, array $layout, int $width, int $height): array {
    $rows = [];

    foreach ($layout as $index => $cell) {
      $fitted = $this->coverFit($images[$index], (int)($width * $cell['w']), (int)($height * $cell['h']));
      $key = (string)$cell['y'];
      $rows[$key] ??= [];
      $rows[$key][] = ['x' => $cell['x'], 'image' => $fitted];
    }

    ksort($rows);

    /** @var array<string, list<array{x: float, image: VipsImage}>> */
    return $rows;
  }

  /**
   * @param array<string, list<array{x: float, image: VipsImage}>> $rows
   */
  private function joinVertically(array $rows): VipsImage {
    $rowImages = array_map(fn($cells) => $this->joinHorizontally($cells), $rows);
    $firstRow = array_shift($rowImages);
    assert($firstRow instanceof VipsImage);

    return array_reduce(
      $rowImages,
      fn(VipsImage $result, VipsImage $row) => $result->join($row, 'vertical'),
      $firstRow
    );
  }

  /**
   * @param list<array{x: float, image: VipsImage}> $cells
   */
  private function joinHorizontally(array $cells): VipsImage {
    usort($cells, fn($a, $b) => $a['x'] <=> $b['x']);
    $firstCell = array_shift($cells);
    assert(is_array($firstCell));

    return array_reduce(
      $cells,
      fn(VipsImage $result, array $cell) => $result->join($cell['image'], 'horizontal'),
      $firstCell['image']
    );
  }

  private function coverFit(VipsImage $image, int $width, int $height): VipsImage {
    $scale = max($width / $image->width, $height / $image->height);
    $resized = $image->resize($scale);

    return $resized->crop(
      (int)(($resized->width - $width) / 2),
      (int)(($resized->height - $height) / 2),
      $width,
      $height
    );
  }
}
