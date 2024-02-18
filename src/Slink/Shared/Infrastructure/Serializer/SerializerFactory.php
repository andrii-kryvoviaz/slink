<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Serializer;

use Slink\Shared\Infrastructure\Serializer\Enum\Encoder;
use Slink\Shared\Infrastructure\Serializer\Enum\Normalizer;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class SerializerFactory {
  private static \WeakMap $registry;
  
  /**
   * @param Normalizer $normalizer
   * @param Encoder $encoder
   * @return Serializer
   */
  public static function create(Normalizer $normalizer = Normalizer::ObjectNormalizer, Encoder $encoder = Encoder::None): Serializer {
    if(!isset(self::$registry)) {
      self::$registry = new \WeakMap();
    }
    
    $key = new \ArrayObject([$normalizer, $encoder]);
    
    if(self::$registry->offsetExists($key)) {
      return self::$registry->offsetGet($key);
    }
    
    [$normalizerClass, $encoderClass] = [$normalizer->value, $encoder->value];
    
    if(!class_exists($normalizerClass)) {
      throw new \RuntimeException("Normalizer class {$normalizerClass} does not exist");
    }
    
    $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
    $normalizerArgs = is_subclass_of($normalizerClass, AbstractNormalizer::class) ? [$classMetadataFactory] : [];
    
    // @phpstan-ignore-next-line
    $normalizers = [new $normalizerClass(...$normalizerArgs)];
    $encoders = class_exists($encoderClass) ? [new $encoderClass()] : [];
    
    $serializer = new Serializer(
      $normalizers, // @phpstan-ignore-line
      $encoders
    );
    
    self::$registry->offsetSet($key, $serializer);
    
    return $serializer;
  }
}