<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;

#[AsDoctrineListener(event: Events::postLoad, connection: 'read_model')]
final class ViewIdentityListener {
  public function postLoad(PostLoadEventArgs $args): void {
    $entity = $args->getObject();

    if (!$entity instanceof AbstractView) {
      return;
    }

    $entityManager = $args->getObjectManager();
    $metadata = $entityManager->getClassMetadata($entity::class);

    $unitOfWork = $entityManager->getUnitOfWork();

    foreach ($metadata->fieldMappings as $field => $mapping) {
      if ($mapping->type !== 'uuid') {
        continue;
      }

      $unitOfWork->setOriginalEntityProperty(
        \spl_object_id($entity),
        $field,
        $metadata->getFieldValue($entity, $field),
      );
    }
  }
}
