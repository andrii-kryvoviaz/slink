<?php

declare(strict_types=1);

namespace UI\Console\Command\Info;
use Slink\Shared\Application\Service\ProjectMetadataExtractor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'slink:about', description: 'Display information about the current project')]
class AboutCommand extends Command {
  
  public function __construct(
    protected KernelInterface $kernel,
    protected ProjectMetadataExtractor $metadataExtractor
  ) {
    parent::__construct();
  }
  
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    
    $xdebugMode = getenv('XDEBUG_MODE') ?: \ini_get('xdebug.mode');
    
    $defaultRows = $this->kernel->getEnvironment() === 'dev'
      ? [
          ['<info>Symfony</>', Kernel::VERSION],
          [''],
          // @phpstan-ignore-next-line
          ['Long-Term Support', 4 === Kernel::MINOR_VERSION ? 'Yes' : 'No'],
          ['End of maintenance', Kernel::END_OF_MAINTENANCE.(self::isExpired(Kernel::END_OF_MAINTENANCE) ? ' <error>Expired</>' : ' (<comment>'.self::daysBeforeExpiration(Kernel::END_OF_MAINTENANCE).'</>)')],
          ['End of life', Kernel::END_OF_LIFE.(self::isExpired(Kernel::END_OF_LIFE) ? ' <error>Expired</>' : ' (<comment>'.self::daysBeforeExpiration(Kernel::END_OF_LIFE).'</>)')],
          ['Environment', $this->kernel->getEnvironment()],
          ['Debug', $this->kernel->isDebug() ? 'true' : 'false'],
          ['Charset', $this->kernel->getCharset()],
          new TableSeparator(),
          ['<info>PHP</>', \PHP_VERSION],
          [''],
          ['Architecture', (\PHP_INT_SIZE * 8).' bits'],
          ['Intl locale', class_exists(\Locale::class, false) && \Locale::getDefault() ? \Locale::getDefault() : 'n/a'],
          ['OPcache', \extension_loaded('Zend OPcache') ? (filter_var(\ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN) ? 'Enabled' : 'Not enabled') : 'Not installed'],
          ['APCu', \extension_loaded('apcu') ? (filter_var(\ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN) ? 'Enabled' : 'Not enabled') : 'Not installed'],
          ['Xdebug', \extension_loaded('xdebug') ? ($xdebugMode && 'off' !== $xdebugMode ? 'Enabled (' . $xdebugMode . ')' : 'Not enabled') : 'Not installed'],
          new TableSeparator(),
          ...$this->metadataExtractor->getClientAppDetails(),
          new TableSeparator(),
        ]
      : [];
    
    $rows = [
      ...$defaultRows,
      ['<info>Slink</>', $this->metadataExtractor->version()],
      [''],
      ...$this->metadataExtractor->getSettings(),
    ];
    
    $io->table([], $rows);
    
    return 0;
  }
  
  private static function isExpired(string $date): bool
  {
    $date = \DateTimeImmutable::createFromFormat('d/m/Y', '01/'.$date);
    
    return false !== $date && new \DateTimeImmutable() > $date->modify('last day of this month 23:59:59');
  }
  
  /**
   * @throws \DateMalformedStringException
   */
  private static function daysBeforeExpiration(string $date): string
  {
    $date = \DateTimeImmutable::createFromFormat('d/m/Y', '01/'.$date);
    
    // @phpstan-ignore-next-line
    return new \DateTimeImmutable()->diff($date->modify('last day of this month 23:59:59'))->format('in %R%a days');
  }
}