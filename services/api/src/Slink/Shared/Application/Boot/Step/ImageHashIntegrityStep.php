<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Image\Application\Command\CalculateImageHash\CalculateImageHashCommand;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsTaggedItem(priority: 60)]
final readonly class ImageHashIntegrityStep implements BootStepInterface {
  public function __construct(
    private ImageRepositoryInterface $imageRepository,
    private CommandBusInterface $commandBus,
    #[Autowire('%env(bool:default::SKIP_HASH_CALCULATION)%')]
    private bool $skipHashCalculation,
  ) {}

  public function label(): string {
    return 'image hashes';
  }

  public function category(): BootCategory {
    return BootCategory::Boot;
  }

  public function run(BootContext $context): BootResult {
    if ($this->skipHashCalculation) {
      return BootResult::skipped('env:SKIP_HASH_CALCULATION=true');
    }

    $missing = $this->imageRepository->findImagesWithoutSha1Hash();
    $total = count($missing);

    if ($total === 0) {
      return BootResult::upToDate();
    }

    $errors = 0;

    foreach ($missing as $imageView) {
      try {
        $this->commandBus->handle(
          new CalculateImageHashCommand(ID::fromString($imageView->getUuid())),
        );
      } catch (\Throwable) {
        $errors++;
      }
    }

    $applied = $total - $errors;

    if ($errors > 0) {
      return BootResult::warn(sprintf('%d hashed, %d failed', $applied, $errors));
    }

    return BootResult::applied(sprintf('%d hashed', $applied));
  }
}
