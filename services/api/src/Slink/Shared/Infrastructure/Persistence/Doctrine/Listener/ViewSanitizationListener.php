<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Slink\Shared\Domain\ValueObject\EscapedString;
use Slink\Shared\Domain\ValueObject\SanitizableValueObject;
use Slink\Shared\Infrastructure\Attribute\Sanitize;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;

#[AsDoctrineListener(event: Events::prePersist, connection: 'read_model')]
#[AsDoctrineListener(event: Events::preUpdate, connection: 'read_model')]
final class ViewSanitizationListener {
  public function prePersist(PrePersistEventArgs $args): void {
    $this->sanitizeEntity($args->getObject());
  }

  public function preUpdate(PreUpdateEventArgs $args): void {
    $this->sanitizeEntity($args->getObject());
  }

  private function sanitizeEntity(object $entity): void {
    if (!$entity instanceof AbstractView) {
      return;
    }

    $reflection = new \ReflectionClass($entity);

    foreach ($reflection->getProperties() as $property) {
      $attributes = $property->getAttributes(Sanitize::class);

      if (empty($attributes)) {
        continue;
      }

      $value = $property->getValue($entity);

      if ($value === null) {
        continue;
      }

      if (is_string($value)) {
        $sanitized = EscapedString::fromString($value)->getValue();
        $property->setValue($entity, $sanitized);
        continue;
      }

      if ($value instanceof SanitizableValueObject) {
        $sanitized = $value->sanitize();
        $property->setValue($entity, $sanitized);
      }
    }
  }
}
