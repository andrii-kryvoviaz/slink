<?php

declare(strict_types=1);

namespace Slink\Tests\Unit\Slink\Shared\Infrastructure\Persistence\Doctrine\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\EscapedString;
use Slink\Shared\Domain\ValueObject\SanitizableValueObject;
use Slink\Shared\Infrastructure\Attribute\Sanitize;
use Slink\Shared\Infrastructure\Persistence\Doctrine\Listener\ViewSanitizationListener;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;

final class ViewSanitizationListenerTest extends TestCase {
  private ViewSanitizationListener $listener;
  private EntityManagerInterface $entityManager;

  protected function setUp(): void {
    $this->listener = new ViewSanitizationListener();
    $this->entityManager = $this->createStub(EntityManagerInterface::class);
  }

  #[Test]
  public function itSanitizesStringPropertyOnPrePersist(): void {
    $view = new TestViewWithStrings(
      'test-id',
      '<script>alert("xss")</script>',
      'Normal description',
    );

    $args = new PrePersistEventArgs($view, $this->entityManager);
    $this->listener->prePersist($args);

    $this->assertSame('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $view->getName());
    $this->assertSame('Normal description', $view->getDescription());
  }

  #[Test]
  public function itSanitizesStringPropertyOnPreUpdate(): void {
    $view = new TestViewWithStrings(
      'test-id',
      '<img onerror="alert(1)">',
      'Safe content',
    );

    $changeSet = [];
    $args = new PreUpdateEventArgs($view, $this->entityManager, $changeSet);
    $this->listener->preUpdate($args);

    $this->assertSame('&lt;img onerror=&quot;alert(1)&quot;&gt;', $view->getName());
  }

  #[Test]
  public function itSanitizesValueObjectPropertyOnPrePersist(): void {
    $vo = new TestSanitizableVO('<script>evil()</script>');
    $view = new TestViewWithVO('test-id', $vo);

    $args = new PrePersistEventArgs($view, $this->entityManager);
    $this->listener->prePersist($args);

    $this->assertSame('&lt;script&gt;evil()&lt;/script&gt;', $view->getContent()->getValue());
  }

  #[Test]
  public function itIgnoresNonAbstractViewEntities(): void {
    $entity = new \stdClass();

    $args = new PrePersistEventArgs($entity, $this->entityManager);
    $this->listener->prePersist($args);

    $this->expectNotToPerformAssertions();
  }

  #[Test]
  public function itIgnoresPropertiesWithoutSanitizeAttribute(): void {
    $view = new TestViewWithStrings(
      '<script>id</script>',
      'Name',
      '<script>desc</script>',
    );

    $args = new PrePersistEventArgs($view, $this->entityManager);
    $this->listener->prePersist($args);

    $this->assertSame('<script>id</script>', $view->getId());
    $this->assertSame('<script>desc</script>', $view->getDescription());
  }

  #[Test]
  public function itHandlesNullValuesGracefully(): void {
    $view = new TestViewWithNullable('test-id', null);

    $args = new PrePersistEventArgs($view, $this->entityManager);
    $this->listener->prePersist($args);

    $this->assertNull($view->getName());
  }

  #[Test]
  public function itPreventsDoubleEncodingOnMultiplePersists(): void {
    $view = new TestViewWithStrings(
      'test-id',
      '<script>alert(1)</script>',
      'desc',
    );

    $args = new PrePersistEventArgs($view, $this->entityManager);
    $this->listener->prePersist($args);
    $firstResult = $view->getName();

    $changeSet = [];
    $args2 = new PreUpdateEventArgs($view, $this->entityManager, $changeSet);
    $this->listener->preUpdate($args2);
    $secondResult = $view->getName();

    $this->assertSame($firstResult, $secondResult);
    $this->assertStringNotContainsString('&amp;lt;', $secondResult);
  }
}

class TestViewWithStrings extends AbstractView {
  public function __construct(
    private string $id,

    #[Sanitize]
    private string $name,

    private string $description,
  ) {
  }

  public function getId(): string {
    return $this->id;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getDescription(): string {
    return $this->description;
  }
}

class TestViewWithVO extends AbstractView {
  public function __construct(
    private string $id,

    #[Sanitize]
    private TestSanitizableVO $content,
  ) {
  }

  public function getId(): string {
    return $this->id;
  }

  public function getContent(): TestSanitizableVO {
    return $this->content;
  }
}

class TestViewWithNullable extends AbstractView {
  public function __construct(
    private string $id,

    #[Sanitize]
    private ?string $name,
  ) {
  }

  public function getId(): string {
    return $this->id;
  }

  public function getName(): ?string {
    return $this->name;
  }
}

final class TestSanitizableVO implements SanitizableValueObject {
  public function __construct(private string $value) {
  }

  public function getValue(): string {
    return $this->value;
  }

  public function sanitize(): static {
    return new self(EscapedString::fromString($this->value)->getValue());
  }
}
