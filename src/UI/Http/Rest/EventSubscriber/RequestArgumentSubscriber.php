<?php

namespace UI\Http\Rest\EventSubscriber;

use JetBrains\PhpStorm\NoReturn;
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
  
  #[NoReturn] public function onKernelControllerArguments(ControllerArgumentsEvent $event): void {
    $arguments = $event->getArguments();
    
    foreach ($arguments as $key => $argument) {
      if (!$argument instanceof MapQueryString && !$argument instanceof MapRequestPayload) {
        continue;
      }
      
      $method = $argument instanceof MapQueryString ? 'query' : 'request';
      
      $request = $event->getRequest();
      
      if (!$type = $argument->metadata->getType()) {
        continue;
      }
      
      // skip if class does not exist
      if (!class_exists($type)) {
        continue;
      }
      
      $typeReflection = new \ReflectionClass($type);
      $typeProperties = $typeReflection->getProperties();
      
      foreach ($typeProperties as $typeProperty) {
        if (!$request->{$method}->has($typeProperty->getName())) {
          if($typeProperty->hasDefaultValue()) {
            $request->{$method}->set($typeProperty->getName(), $typeProperty->getDefaultValue());
            continue;
          }
          
          // if property is missing in request, create it with empty value
          // so symfony mapping can validate it properly
          $request->{$method}->set($typeProperty->getName(), '');
        }
      }
    }
  }
}