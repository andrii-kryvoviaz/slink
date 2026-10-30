<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Slink\Shared\Application\Boot\Step\EventStoreSchemaStep;
use Slink\Shared\Application\Boot\SubCommandRunner;
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
    $runner = static::getContainer()->get(SubCommandRunner::class);
    self::assertInstanceOf(SubCommandRunner::class, $runner);
    $path = $this->connection()->getParams()['path'] ?? null;
    self::assertIsString($path);
    static::getContainer()->set(EventStoreSchemaStep::class, new EventStoreSchemaStep($runner, $path . '.es_migrations.yaml'));
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

  /**
   * @param array<string, string> $arguments
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
