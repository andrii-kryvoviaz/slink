<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Share\Application\Command\CreateShare\CreateShareCommand;
use Slink\Share\Application\Command\CreateShare\CreateShareResult;
use Slink\Share\Application\Command\PublishShare\PublishShareCommand;
use Slink\Share\Application\Command\SetShareExpiration\SetShareExpirationCommand;
use Slink\Share\Application\Command\SetSharePassword\SetSharePasswordCommand;
use Slink\Share\Domain\Service\ShareUrlBuilderInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareParams;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Clock\Clock;

final class DemoShareSeeder {
  use CommandTrait;

  private const array PASSWORD_PROTECTED = [
    'biel-morro-bsSIk3LV_NE-unsplash.jpg',
    'nicolette-meade-RL3F99l0XYE-unsplash.jpg',
  ];

  private const array EXPIRATIONS = [
    'clay-banks-Icfk7F4Ku0w-unsplash.jpg' => '+7 days',
    'daniel-roe-lpjb_UMOyx8-unsplash.jpg' => '+30 days',
    'samuel-ferrara-dKJXkKCF2D8-unsplash.jpg' => '+90 days',
    'luca-bravo-WeFDiEDModQ-unsplash.jpg' => '-1 day',
  ];

  private const array SHARED_COLLECTIONS = ['High Places', 'After Dark'];

  public function __construct(
    private readonly ShareUrlBuilderInterface $shareUrlBuilder,
    private readonly DemoSession $session,
  ) {
  }

  /**
   * @param array<string, ImageView> $images
   * @param array<string, string> $collectionIds
   */
  public function seed(array $images, array $collectionIds, DemoSeedReport $report): void {
    foreach ($images as $fileName => $image) {
      $report->attempt($fileName, fn() => $this->seedImageShare($fileName, $image, $report));
    }

    foreach (array_intersect_key($collectionIds, array_flip(self::SHARED_COLLECTIONS)) as $name => $collectionId) {
      $shareable = ShareableReference::forCollection(ID::fromString($collectionId));
      $report->attempt($name, fn() => $this->ensureShare(ShareParams::fromShareable($shareable), $name, $report));
    }
  }

  private function seedImageShare(string $fileName, ImageView $image, DemoSeedReport $report): void {
    $targetPath = $this->shareUrlBuilder->buildTargetPath($image->getUuid(), $image->getFileName(), null, null, false);
    $shareable = ShareableReference::forImage(ID::fromString($image->getUuid()));
    $share = $this->ensureShare(ShareParams::withTargetPath($shareable, $targetPath), $fileName, $report);

    $this->ensurePublished($share, $fileName, $report);

    if (in_array($fileName, self::PASSWORD_PROTECTED, true)) {
      $this->ensurePassword($share, $fileName, $report);
    }

    if (isset(self::EXPIRATIONS[$fileName])) {
      $this->ensureExpiration($share, $fileName, self::EXPIRATIONS[$fileName], $report);
    }
  }

  private function ensureShare(ShareParams $params, string $label, DemoSeedReport $report): Share {
    /** @var CreateShareResult $result */
    $result = $this->handleSync(new CreateShareCommand($params));

    if ($result->wasCreated()) {
      $report->created($label);

      return $result->getShare();
    }

    $report->skipped($label);

    return $result->getShare();
  }

  private function ensurePublished(Share $share, string $label, DemoSeedReport $report): void {
    if ($share->isPublished()) {
      return;
    }

    $this->handle(new PublishShareCommand($share->getId()));
    $report->created($label . ' published');
  }

  private function ensurePassword(Share $share, string $fileName, DemoSeedReport $report): void {
    $label = $fileName . ' password';

    if ($share->getPassword() !== null) {
      $report->skipped($label);

      return;
    }

    $command = new SetSharePasswordCommand(bin2hex(random_bytes(16)));
    $this->handle($command->withContext(['shareId' => $share->getId()]));
    $report->created($label);
  }

  private function ensureExpiration(Share $share, string $fileName, string $offset, DemoSeedReport $report): void {
    $label = sprintf('%s expiration %s', $fileName, $offset);

    if ($share->getExpiresAt() !== null) {
      $report->skipped($label);

      return;
    }

    $this->setExpiration($share->getId(), $offset);
    $report->created($label);
  }

  private function setExpiration(string $shareId, string $offset): void {
    $now = Clock::get()->now();
    $expiresAt = $now->modify($offset);
    $command = (new SetShareExpirationCommand($expiresAt))->withContext(['shareId' => $shareId]);

    if ($expiresAt > $now) {
      $this->handle($command);

      return;
    }

    $this->session->at($expiresAt->modify('-1 day'), fn() => $this->handle($command));
  }
}
