<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use Doctrine\Persistence\ConnectionRegistry;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Image\Domain\Image;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1182')]
final class SnapshotRemovalBootTest extends HttpTestCase {
  #[Test]
  public function theSnapshotFrequencyParameterIsGone(): void {
    self::assertFalse(static::getContainer()->hasParameter('event_sauce.snapshot_frequency'));
  }

  #[Test]
  #[Group('sc-1183')]
  public function onlyTheReadModelConnectionAndEntityManagerRemain(): void {
    self::assertSame(['read_model'], $this->entityManagerNames());
    self::assertSame(['read_model'], \array_values(\array_diff($this->connectionNames(), ['default'])));
  }

  #[Test]
  public function aControllerArgumentTypedAsAnAggregateIsNotResolved(): void {
    $resolver = static::getContainer()->get('argument_resolver');
    self::assertInstanceOf(ArgumentResolverInterface::class, $resolver);
    $request = new Request(attributes: ['id' => ID::generate()->toString()]);
    $controller = static fn (Image $image): Image => $image;

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('requires the "$image" argument that could not be resolved');

    $resolver->getArguments($request, $controller);
  }

  /**
   * @return list<string>
   */
  private function entityManagerNames(): array {
    $registry = static::getContainer()->get('doctrine');
    self::assertInstanceOf(ManagerRegistry::class, $registry);

    return \array_keys($registry->getManagerNames());
  }

  /**
   * @return list<string>
   */
  private function connectionNames(): array {
    $registry = static::getContainer()->get('doctrine');
    self::assertInstanceOf(ConnectionRegistry::class, $registry);

    return \array_keys($registry->getConnectionNames());
  }
}
