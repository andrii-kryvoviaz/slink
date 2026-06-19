<?php

declare(strict_types=1);

namespace Tests\Integration\Permissions;

final class PermissionProcess {
  public function __construct(
    private readonly int $_exitCode,
    private readonly string $_stdout,
    private readonly string $_stderr,
  ) {
  }

  public static function shell(string $commandLine): self {
    return self::run(['sh', '-c', $commandLine]);
  }

  /**
   * @param list<string> $command
   */
  public static function run(array $command): self {
    $descriptors = [
      0 => ['pipe', 'r'],
      1 => ['pipe', 'w'],
      2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptors, $pipes);

    if (!\is_resource($process)) {
      return new self(127, '', 'unable to spawn process: ' . implode(' ', $command));
    }

    fclose($pipes[0]);

    $stdout = stream_get_contents($pipes[1]) ?: '';
    $stderr = stream_get_contents($pipes[2]) ?: '';

    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    return new self($exitCode, $stdout, $stderr);
  }

  public function isSuccessful(): bool {
    return $this->_exitCode === 0;
  }

  public function exitCode(): int {
    return $this->_exitCode;
  }

  public function stdout(): string {
    return trim($this->_stdout);
  }

  public function stderr(): string {
    return trim($this->_stderr);
  }
}
