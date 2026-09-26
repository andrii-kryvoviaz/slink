<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\Image\Application\Command\TagImage\TagImageCommand;
use Slink\Image\Application\Command\UntagImage\UntagImageCommand;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Application\Command\CreateTag\CreateTagCommand;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Domain\ValueObject\TagPath;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;

final class DemoTagSeeder {
  use CommandTrait;

  private const array TREE = [
    'Nature' => ['Mountain', 'Forest', 'Ocean', 'Desert', 'Lake', 'River', 'Flowers', 'Night Sky'],
    'Urban' => ['Architecture', 'Street', 'Skyline', 'Night'],
    'People' => ['Portrait', 'Group', 'Candid'],
    'Animals' => ['Wildlife', 'Pets', 'Birds'],
    'Art' => ['Abstract', 'Digital', 'Traditional'],
    'Travel' => ['Landscape', 'Culture', 'Adventure'],
  ];

  private const array IMAGE_TAGS = [
    'aaron-lau-EOnlL3L3IgQ-unsplash.jpg' => ['#Urban/Street', '#Urban/Architecture'],
    'biel-morro-bsSIk3LV_NE-unsplash.jpg' => ['#Animals/Pets'],
    'christian-grab-AygJgCM_vto-unsplash.jpg' => ['#Nature/Forest', '#Nature/Mountain'],
    'clay-banks-Icfk7F4Ku0w-unsplash.jpg' => ['#Urban/Skyline', '#Urban/Night'],
    'daniel-roe-lpjb_UMOyx8-unsplash.jpg' => ['#Nature/Lake', '#Nature/Mountain', '#Travel/Landscape'],
    'elijah-pilchard-D4WXuiwF5yk-unsplash.jpg' => ['#Nature/Flowers'],
    'filip-zrnzevic-_EMkxLdko9k-unsplash.jpg' => ['#Nature/Forest', '#Travel/Adventure'],
    'george-pisarevsky-4lM10E18HdQ-unsplash.jpg' => ['#Nature/Flowers'],
    'george-pisarevsky-EsxDnpNYfPA-unsplash.jpg' => ['#Nature/Flowers'],
    'jei-lee-yRXuXvy4sQ4-unsplash.jpg' => ['#Nature/Flowers'],
    'jeremy-bishop-dR_q93lfaTw-unsplash.jpg' => ['#Nature/Mountain', '#Travel/Landscape'],
    'jeremy-bishop-dvACrXUExLs-unsplash.jpg' => ['#Nature/River', '#Nature/Forest', '#Travel/Landscape'],
    'john-baker-anwxFFJ7tHw-unsplash.jpg' => ['#Nature/River', '#Nature/Mountain', '#Travel/Landscape'],
    'jordan-steranka-lpddCskeg4A-unsplash.jpg' => ['#Nature/Mountain', '#Nature/Night Sky'],
    'joyce-g-C_Hn9I2AU8I-unsplash.jpg' => ['#Nature/Forest'],
    'kamil-klyta-CT8hJcAT9uA-unsplash.jpg' => ['#Nature/Flowers'],
    'kazuend-2KXEb_8G5vo-unsplash.jpg' => ['#Nature/Mountain', '#Nature/Lake', '#Nature/Night Sky'],
    'luca-bravo-WeFDiEDModQ-unsplash.jpg' => ['#Nature/Lake', '#Nature/Mountain', '#Travel/Landscape'],
    'malte-schmidt-enGr5YbjQKQ-unsplash.jpg' => ['#Urban/Street', '#Urban/Skyline', '#Urban/Architecture'],
    'marcus-dietachmair-MjrjF0D1k-E-unsplash.jpg' => ['#Nature/Forest'],
    'nahil-naseer-xljtGZ2-P3Y-unsplash.jpg' => ['#Nature', '#Art/Abstract'],
    'nate-rayfield-_WR6tUIAJe8-unsplash.jpg' => ['#Nature/Lake', '#Nature/Night Sky'],
    'nathan-dumlao-ciO5L8pin8A-unsplash.jpg' => ['#Nature/Ocean', '#Art/Abstract'],
    'nicolette-meade-RL3F99l0XYE-unsplash.jpg' => ['#Nature/Flowers', '#Art/Traditional'],
    'patrick-langwallner-wiFOaBrX_wc-unsplash.jpg' => ['#Nature/Ocean', '#Art/Abstract'],
    'piotr-pekala-t4cbHkeyXz4-unsplash.jpg' => ['#Nature/Mountain', '#Travel/Landscape'],
    'saffu-A7RzCegedb4-unsplash.jpg' => ['#Nature/Flowers'],
    'samuel-ferrara-dKJXkKCF2D8-unsplash.jpg' => ['#Nature/Mountain', '#Travel/Landscape'],
  ];

  public function __construct(
    private readonly TagRepositoryInterface $tagRepository,
  ) {
  }

  /**
   * @return array<string, string>
   */
  public function seedTags(ID $userId, DemoSeedReport $report): array {
    $tagIds = [];

    foreach (self::TREE as $root => $children) {
      $tagIds += $this->seedBranch($userId, $root, $children, $report);
    }

    return $tagIds;
  }

  /**
   * @param array<string, ImageView> $images
   * @param array<string, string> $tagIds
   */
  public function tagImages(ID $userId, array $images, array $tagIds, DemoSeedReport $report): void {
    foreach (array_intersect_key(self::IMAGE_TAGS, $images) as $fileName => $paths) {
      $report->attempt($fileName, fn() => $this->tagImage($userId, $fileName, $images[$fileName], $paths, $tagIds, $report));
    }
  }

  /**
   * @param list<string> $children
   * @return array<string, string>
   */
  private function seedBranch(ID $userId, string $root, array $children, DemoSeedReport $report): array {
    $rootPath = TagPath::createRoot(TagName::fromString($root));
    $rootId = $report->attempt($root, fn() => $this->ensureTag($userId, $rootPath, null, $report));

    if ($rootId === null) {
      return [];
    }

    $tagIds = [$rootPath->getValue() => $rootId];

    foreach ($children as $child) {
      $childPath = TagPath::createChild($rootPath, TagName::fromString($child));
      $tagIds[$childPath->getValue()] = $report->attempt(
        $childPath->getValue(),
        fn() => $this->ensureTag($userId, $childPath, $rootId, $report),
      );
    }

    return array_filter($tagIds);
  }

  private function ensureTag(ID $userId, TagPath $path, ?string $parentId, DemoSeedReport $report): string {
    $existing = $this->tagRepository->findByPathAndUser($path->getValue(), $userId);

    if ($existing !== null) {
      $report->skipped($path->getValue());

      return $existing->getUuid();
    }

    $command = new CreateTagCommand($path->getTagName(), $parentId);
    /** @var ID $tagId */
    $tagId = $this->handleSync($command->withContext(['userId' => $userId->toString()]));
    $report->created($path->getValue());

    return $tagId->toString();
  }

  /**
   * @param list<string> $paths
   * @param array<string, string> $tagIds
   */
  private function tagImage(ID $userId, string $fileName, ImageView $image, array $paths, array $tagIds, DemoSeedReport $report): void {
    $currentTags = $this->tagRepository->findByImageId(ID::fromString($image->getUuid()));
    $missingPaths = array_diff($paths, array_map(static fn(TagView $tag): string => $tag->getPath(), $currentTags));
    $unmappedTags = array_filter($currentTags, static fn(TagView $tag): bool => !in_array($tag->getPath(), $paths, true));

    if ($missingPaths === [] && $unmappedTags === []) {
      $report->skipped($fileName);

      return;
    }

    foreach ($missingPaths as $path) {
      $tagId = $tagIds[$path] ?? throw new \RuntimeException(sprintf('Tag %s is unavailable', $path));
      $command = new TagImageCommand($image->getUuid(), $tagId);
      $this->handle($command->withContext(['userId' => $userId->toString()]));
      $report->created(sprintf('%s %s', $fileName, $path));
    }

    foreach ($unmappedTags as $tag) {
      $command = new UntagImageCommand($image->getUuid(), $tag->getUuid());
      $this->handle($command->withContext(['userId' => $userId->toString()]));
      $report->created(sprintf('%s untagged %s', $fileName, $tag->getPath()));
    }
  }
}
