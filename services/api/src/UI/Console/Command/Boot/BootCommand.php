<?php

declare(strict_types=1);

namespace UI\Console\Command\Boot;

use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootRunner;
use Slink\Shared\Application\Boot\ConsoleBootReporter;
use Slink\Shared\Application\Service\ProjectMetadataExtractor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
  name: 'slink:boot',
  description: 'Run the application boot sequence (migrations, integrity checks, config report)',
)]
final class BootCommand extends Command {
  public function __construct(
    private readonly BootRunner $runner,
    private readonly ProjectMetadataExtractor $metadata,
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $application = $this->getApplication();

    if ($application === null) {
      throw new \LogicException('Boot command requires a console Application instance.');
    }

    $reporter = new ConsoleBootReporter($output);
    $reporter->header($this->metadata->version(), []);

    $context = new BootContext($application);

    return $this->runner->run($context, $reporter) ? Command::SUCCESS : Command::FAILURE;
  }
}
