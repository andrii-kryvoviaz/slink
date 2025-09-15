<?php

declare(strict_types=1);

namespace UI\Http\Rest\EventSubscriber;

use Psr\Log\LoggerInterface;
use Slink\Shared\Domain\Exception\SpecificationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

final readonly class ExceptionSubscriber implements EventSubscriberInterface {
  /**
   * @param LoggerInterface $logger
   * @param string $environment
   * @param array<string, int> $exceptionToStatus
   * @param array<int, string> $exceptionCodeToMessage
   */
  public function __construct(
    private LoggerInterface $logger,
    private string $environment,
    private array $exceptionToStatus = [],
    private array $exceptionCodeToMessage = [],
  ) {
  }
  
  /**
   * @return array<string, string>
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::EXCEPTION => 'onKernelException',
    ];
  }
  
  /**
   * @param ExceptionEvent $event
   * @return void
   */
  public function onKernelException(ExceptionEvent $event): void {
    $exception = $event->getThrowable();

    try {
      $response = new JsonResponse();
      $response->headers->set('Content-Type', 'application/vnd.api+json');
      $response->setStatusCode($this->determineStatusCode($exception));
      $response->setData($this->getErrorMessage($exception));
      
      $event->setResponse($response);
    } catch (\Throwable $exception) {
      $this->logger->error(sprintf('An error occurred: %s in %s:%s', $exception->getMessage(), $exception->getFile(), $exception->getLine()));
      
      $event->setResponse(new JsonResponse([
        'error' => [
          'title' => 'Internal Server Error',
          'message' => 'An error occurred, while processing the request',
        ],
      ], Response::HTTP_INTERNAL_SERVER_ERROR));
    }
  }
  
  /**
   * @param Throwable $exception
   * @return array<string, mixed>
   */
  private function getErrorMessage(Throwable $exception): array {
    $error = [
      'title' => \str_replace('\\', '.', $exception::class),
      'message' => $this->getExceptionMessage($exception),
    ];
    
    if($exception instanceof SpecificationException) {
      $violation = [
        'property' => $exception->getProperty(),
        'message' => $exception->getMessage(),
      ];

      $data = $exception->toPayload();
      if (!empty($data)) {
        $violation['data'] = $data;
      }

      $violations = [$violation];
      
      $error['violations'] = $violations;
      $error['message'] = 'Specification Error';
    }
    
    $previous = $exception->getPrevious();
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
  
  /**
   * @param Throwable $exception
   * @return string
   */
  private function getExceptionMessage(Throwable $exception): string {
    if($exception->getMessage()) {
      return $exception->getMessage();
    }
    
    $statusCode = $this->determineStatusCode($exception);
    return $this->exceptionCodeToMessage[$statusCode] ?? 'An error occurred';
  }
  
  /**
   * @param Throwable $exception
   * @return int
   */
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
  
  /**
   * @return bool
   */
  private function isDev(): bool {
    return 'dev' === $this->environment;
  }
}