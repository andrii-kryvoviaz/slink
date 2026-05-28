<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class SubCommandRunner {
  public function __construct(
    #[Autowire('%kernel.project_dir%/bin/console')]
    private string $consolePath,
  ) {}

  /**
   * @param array<string, mixed> $args
   */
  public function run(string $name, array $args = []): SubCommandResult {
    $command = [PHP_BINARY, $this->consolePath, $name, '--no-ansi'];

    foreach ($args as $key => $value) {
      if (is_bool($value)) {
        if ($value) {
          $command[] = $key;
        }
        continue;
      }

      $command[] = $key . '=' . (string) $value;
    }

    $descriptors = [
      1 => ['pipe', 'w'],
      2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptors, $pipes);

    if (!is_resource($process)) {
      return new SubCommandResult(1, sprintf('Failed to spawn %s', $name));
    }

    $stdout = stream_get_contents($pipes[1]) ?: '';
    $stderr = stream_get_contents($pipes[2]) ?: '';

    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    return new SubCommandResult($exitCode, trim($stdout . "\n" . $stderr));
  }
}
