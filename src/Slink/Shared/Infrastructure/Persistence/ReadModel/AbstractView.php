<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\ReadModel;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\Event\EventWithEntityManager;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\Shared\Domain\ValueObject\DateTime;

abstract class AbstractView {
  /**
   * @param SerializablePayload|EventWithEntityManager $event
   * @return static
   */
  public static function fromEvent(SerializablePayload | EventWithEntityManager $event): static {
    try {
      $payload = $event->toPayload();
      $entityManger = $event instanceof EventWithEntityManager
        ? $event->getEntityManager()
        : null;
      
      return self::fromPayload($payload, $entityManger);
    } catch (\Exception $e) {
      throw new \InvalidArgumentException($e->getMessage());
    }
  }
  
  /**
   * @param array<string, mixed> $payload
   * @param EntityManagerInterface|null $entityManager
   * @return static
   * @throws ORMException
   */
  public static function fromPayload(array $payload, ?EntityManagerInterface $entityManager): static {
    $reflection = new \ReflectionClass(static::class);
    $parameters = $reflection->getConstructor()?->getParameters() ?? [];
    
    foreach ($parameters as $parameter) {
      /** @var \ReflectionNamedType $type */
      [$name, $type] = [$parameter->getName(), $parameter->getType()];
      $typeName = $type->getName();
      
      $value = $payload[$name] ?? null;
      
      if (!$parameter->allowsNull() && $value === null) {
        throw new \InvalidArgumentException(static::class . "::$name cannot be null.");
      }
      
      if ($value === null) {
        $payload[$name] = $parameter->isDefaultValueAvailable()
          ? $parameter->getDefaultValue()
          : null;
        
        continue;
      }
        
      if(is_subclass_of($typeName, AbstractValueObject::class)
        || $typeName === DateTime::class
      ) {
        $value = self::createValueObject($typeName, $value);
      }
      
      if($entityManager && is_subclass_of($typeName, AbstractView::class)) {
        $value = $entityManager->getReference($typeName, $value);
      }
      
      $payload[$name] = $value;
    }
    
    // @phpstan-ignore-next-line
    return new static(...$payload);
  }
  
  private static function createValueObject(string $class, mixed $value): mixed {
    if(method_exists($class, 'fromPayload')) {
      return $class::fromPayload($value);
    }
    
    if(method_exists($class, 'fromString')) {
      return $class::fromString($value);
    }
    
    if(method_exists($class, 'fromHash')) {
      return $class::fromHash($value);
    }
    
    return $value;
  }
  
  /**
   * @param AbstractView $view
   * @return $this
   */
  public function merge(AbstractView $view): static {
    $reflection = new \ReflectionClass($this);
    $properties = $reflection->getProperties();
    
    foreach ($properties as $property) {
      $value = $property->getValue($view);
      
      if ($value && !$property->isReadOnly()) {
        $property->setValue($this, $value);
      }
    }
    
    return $this;
  }
}