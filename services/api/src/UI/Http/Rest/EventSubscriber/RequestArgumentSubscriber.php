<?php

namespace UI\Http\Rest\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestArgumentSubscriber implements EventSubscriberInterface {
  
  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    // with a higher priority than the default one to be able to modify the controller arguments
    return [
      KernelEvents::CONTROLLER_ARGUMENTS => ['onKernelControllerArguments', 10],
    ];
  }
  
  public function onKernelControllerArguments(ControllerArgumentsEvent $event): void {
    $arguments = $event->getArguments();
    
    foreach ($arguments as $argument) {
      if (!$argument instanceof MapQueryString && !$argument instanceof MapRequestPayload) {
        continue;
      }
      
      $method = $argument instanceof MapQueryString ? 'query' : 'request';
      
      $request = $event->getRequest();
      $format = $request->getContentTypeFormat();
      
      if (!$type = $argument->metadata->getType()) {
        continue;
      }
      
      // skip if class does not exist
      if (!class_exists($type)) {
        continue;
      }
      
      if ($format === 'json' && $data = json_decode($request->getContent(), true)) {
        $request->{$method}->replace($data);
      }
      
      $typeReflection = new \ReflectionClass($type);
      $constructor = $typeReflection->getConstructor();
      $typeProperties = $constructor?->getParameters() ?? [];
      
      foreach ($typeProperties as $typeProperty) {
        if (!$request->{$method}->has($typeProperty->getName())) {
          $defaultValue = $typeProperty->isDefaultValueAvailable() ? $typeProperty->getDefaultValue() : null;
          
          // if property is missing in request, create it with empty value
          // so symfony mapping can validate it properly
          $request->{$method}->set($typeProperty->getName(), $defaultValue);
        }
      }
    }
  }
}