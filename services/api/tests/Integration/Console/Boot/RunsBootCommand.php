<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootRunner;
use Slink\Shared\Application\Service\ProjectMetadataExtractor;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

trait RunsBootCommand {
  private function connection(): Connection {
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    self::assertInstanceOf(EntityManagerInterface::class, $entityManager);

    return $entityManager->getConnection();
  }

  /** @return array{int, string} */
  private function bootResult(): array {
    $metadata = $this->createStub(ProjectMetadataExtractor::class);
    $metadata->method('version')->willReturn('test');
    static::getContainer()->set(ProjectMetadataExtractor::class, $metadata);

    $environment = getenv('APP_ENV');
    $debug = getenv('APP_DEBUG');
    putenv('APP_ENV=test');
    putenv('APP_DEBUG=1');
    try {
      return $this->consoleResult(['command' => 'slink:boot']);
    } finally {
      putenv($environment === false ? 'APP_ENV' : 'APP_ENV=' . $environment);
      putenv($debug === false ? 'APP_DEBUG' : 'APP_DEBUG=' . $debug);
    }
  }

  private function bootReport(): RecordingBootReporter {
    $runner = static::getContainer()->get(BootRunner::class);
    self::assertInstanceOf(BootRunner::class, $runner);
    self::assertNotNull(self::$kernel);
    $application = new Application(self::$kernel);
    $application->setAutoExit(false);
    $application->setCatchExceptions(false);
    $report = new RecordingBootReporter();
    $runner->run(new BootContext($application), $report);

    return $report;
  }

  /**
   * @param array<string, string|bool|list<string>> $arguments
   * @return array{int, string}
   */
  private function consoleResult(array $arguments): array {
    self::assertNotNull(self::$kernel);
    $application = new Application(self::$kernel);
    $application->setAutoExit(false);
    $application->setCatchExceptions(false);
    $output = new BufferedOutput();
    $status = $application->run(new ArrayInput($arguments + ['--no-interaction' => true, '--verbose' => true]), $output);

    return [$status, $output->fetch()];
  }
}
