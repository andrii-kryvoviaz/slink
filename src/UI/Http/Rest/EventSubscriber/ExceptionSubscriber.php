<?php

declare(strict_types=1);

namespace UI\Http\Rest\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

final readonly class ExceptionSubscriber implements EventSubscriberInterface {
  public function __construct(private string $environment, private array $exceptionToStatus = []) {
  }

  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::EXCEPTION => 'onKernelException',
    ];
  }

  public function onKernelException(ExceptionEvent $event): void {
    $request = $event->getRequest();

    if ($request->getContentTypeFormat() !== 'json' && !$this->isDev()) {
      return;
    }

    $exception = $event->getThrowable();

    $response = new JsonResponse();
    $response->headers->set('Content-Type', 'application/vnd.api+json');
    $response->setStatusCode($this->determineStatusCode($exception));
    $response->setData($this->getErrorMessage($exception));

    $event->setResponse($response);
  }

  private function getErrorMessage(Throwable $exception): array {
    $previous = $exception->getPrevious();

    $error = [
      'title' => \str_replace('\\', '.', $exception::class),
      'message' => $this->getExceptionMessage($exception),
    ];

    if ($previous instanceof ValidationFailedException) {
      $error['message'] = 'Validation Error';
      
      $violations = [];
      
      foreach ($previous->getViolations() as $violation) {
        if(isset($violations[$violation->getPropertyPath()])) {
          continue;
        }
        
        $violations[$violation->getPropertyPath()] = [
          'property' => $violation->getPropertyPath(),
          'message' => $violation->getMessage(),
        ];
      }
      
      $error['violations'] = array_values($violations);
    }

    $meta = $this->isDev() ? [
      'meta' => [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'message' => $exception->getMessage(),
        'trace' => $exception->getTrace(),
      ],
    ] : [];

    return [
      'error' => $error,
      ...$meta,
    ];
  }

  private function getExceptionMessage(Throwable $exception): string {
    return $exception->getMessage();
  }

  private function determineStatusCode(Throwable $exception): int {
    $exceptionClass = $exception::class;

    foreach ($this->exceptionToStatus as $class => $status) {
      if (\is_a($exceptionClass, $class, true)) {
        return $status;
      }
    }

    // Process HttpExceptionInterface after `exceptionToStatus` to allow overrides from config
    if ($exception instanceof HttpExceptionInterface) {
      return $exception->getStatusCode();
    }

    // Default status code is always 500
    return Response::HTTP_INTERNAL_SERVER_ERROR;
  }

  private function isDev(): bool {
    return 'dev' === $this->environment;
  }
}