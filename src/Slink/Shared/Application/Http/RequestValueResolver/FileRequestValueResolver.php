<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Http\RequestValueResolver;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Exception\UnsupportedFormatException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class FileRequestValueResolver implements ValueResolverInterface, EventSubscriberInterface {
  /**
   * @see \Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT
   * @see DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS
   */
  private const array CONTEXT_DENORMALIZE = [
    'disable_type_enforcement' => true,
    'collect_denormalization_errors' => true,
  ];
  
  /**
   * @see DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS
   */
  private const array CONTEXT_DESERIALIZE = [
    'collect_denormalization_errors' => true,
  ];
  
  /**
   * @param SerializerInterface&DenormalizerInterface $serializer
   * @param ValidatorInterface|null $validator
   */
  public function __construct(
    private readonly SerializerInterface&DenormalizerInterface $serializer,
    private readonly ?ValidatorInterface $validator = null,
  ) {
  }
  
  /**
   * @return string[]
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::CONTROLLER_ARGUMENTS => 'onKernelControllerArguments',
    ];
  }
  
  /**
   * @param Request $request
   * @param ArgumentMetadata $argument
   * @return iterable<MapRequestPayload>
   */
  public function resolve(Request $request, ArgumentMetadata $argument): iterable {
    $attribute = $argument->getAttributesOfType(MapRequestPayload::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;
    
    if (!$attribute) {
      return [];
    }
    
    if ($argument->isVariadic()) {
      throw new \LogicException(sprintf('Mapping variadic argument "$%s" is not supported.', $argument->getName()));
    }
    
    $attribute->metadata = $argument;
    
    return [$attribute];
  }
  
  /**
   * @param ControllerArgumentsEvent $event
   * @return void
   */
  public function onKernelControllerArguments(ControllerArgumentsEvent $event): void {
    $arguments = $event->getArguments();
    
    foreach ($arguments as $i => $argument) {
      if (!$argument instanceof MapRequestPayload) {
        continue;
      }
      
      $validationFailedCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      
      $request = $event->getRequest();
      
      if (!$type = $argument->metadata->getType()) {
        throw new \LogicException(sprintf('Could not resolve the "$%s" controller argument: argument should be typed.', $argument->metadata->getName()));
      }
      
      if ($this->validator) {
        $violations = new ConstraintViolationList();
        try {
          $payload = $this->mapRequestPayload($request, $type, $argument);
        } catch (PartialDenormalizationException $e) {
          $payload = $e->getData();
        }
        
        if (null !== $payload) {
          $violations->addAll($this->validator->validate($payload, null, $argument->validationGroups ?? null));
        }
        
        if (\count($violations)) {
          throw new HttpException($validationFailedCode, implode("\n", array_map(static fn ($e) => $e->getMessage(), iterator_to_array($violations))), new ValidationFailedException($payload, $violations));
        }
      } else {
        try {
          $payload = $this->mapRequestPayload($request, $type, $argument);
        } catch (PartialDenormalizationException $e) {
          throw new HttpException($validationFailedCode, implode("\n", array_map(static fn ($e) => $e->getMessage(), $e->getErrors())), $e);
        }
      }
      
      if (null === $payload) {
        $payload = match (true) {
          $argument->metadata->hasDefaultValue() => $argument->metadata->getDefaultValue(),
          $argument->metadata->isNullable() => null,
          default => throw new HttpException($validationFailedCode)
        };
      }
      
      $arguments[$i] = $payload;
    }
    
    $event->setArguments($arguments);
  }
  
  /**
   * @param Request $request
   * @param string $type
   * @param MapRequestPayload $attribute
   * @return object|null
   * @throws ExceptionInterface
   */
  private function mapRequestPayload(Request $request, string $type, MapRequestPayload $attribute): ?object {
    if (null === $format = $request->getContentTypeFormat()) {
      throw new HttpException(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, 'Unsupported format.');
    }
    
    if ($attribute->acceptFormat && !\in_array($format, (array) $attribute->acceptFormat, true)) {
      throw new HttpException(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, sprintf('Unsupported format, expects "%s", but "%s" given.', implode('", "', (array) $attribute->acceptFormat), $format));
    }
    
    if ($data = [...$request->request->all(), ...$request->files->all()]) {
      return $this->serializer->denormalize($data, $type, null, self::CONTEXT_DENORMALIZE + $attribute->serializationContext);
    }
    
    if ('' === $data = $request->getContent()) {
      return null;
    }
    
    if ('form' === $format) {
      throw new HttpException(Response::HTTP_BAD_REQUEST, 'Request payload contains invalid "form" data.');
    }
    
    try {
      return $this->serializer->deserialize($data, $type, $format, self::CONTEXT_DESERIALIZE + $attribute->serializationContext);
    } catch (UnsupportedFormatException $e) {
      throw new HttpException(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, sprintf('Unsupported format: "%s".', $format), $e);
    } catch (NotEncodableValueException $e) {
      throw new HttpException(Response::HTTP_BAD_REQUEST, sprintf('Request payload contains invalid "%s" data.', $format), $e);
    }
  }
}