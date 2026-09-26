<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
  name: 'slink:demo:seed',
  description: 'Seeds the demo instance with tags, pictures, collections, shares, SSO providers, accounts and their activity, filling only what is missing'
)]
final class SeedDemoCommand extends Command {
  public function __construct(
    private readonly ConfigurationProviderInterface $configurationProvider,
    private readonly DemoSession $session,
    private readonly DemoTagSeeder $tagSeeder,
    private readonly DemoImageSeeder $imageSeeder,
    private readonly DemoCollectionSeeder $collectionSeeder,
    private readonly DemoShareSeeder $shareSeeder,
    private readonly DemoSsoSeeder $ssoSeeder,
    private readonly DemoAccountSeeder $accountSeeder,
    private readonly DemoActivitySeeder $activitySeeder,
    #[Autowire('/app/docker/demo/pictures')]
    private readonly string $picturesPath,
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    if (!$this->configurationProvider->get('demo.enabled')) {
      $io->error('Demo mode is not enabled');
      return Command::FAILURE;
    }

    if (!is_dir($this->picturesPath)) {
      $io->error('Demo pictures directory not found: ' . $this->picturesPath);
      return Command::FAILURE;
    }

    $username = (string) $this->configurationProvider->get('demo.demoUsername');
    $demoUser = $this->accountSeeder->find($username);

    if ($demoUser === null) {
      $io->error(sprintf('Demo user "%s" not found, run slink:demo:init first', $username));
      return Command::FAILURE;
    }

    $reports = $this->session->actingAs($demoUser, fn() => $this->seed($demoUser));

    foreach ($reports as $report) {
      $report->render($io);
    }

    if (array_any($reports, static fn(DemoSeedReport $report): bool => $report->hasFailures())) {
      $io->error('Demo seeding finished with failures');
      return Command::FAILURE;
    }

    $io->success('Demo instance is seeded');

    return Command::SUCCESS;
  }

  /**
   * @return list<DemoSeedReport>
   */
  private function seed(UserView $demoUser): array {
    $userId = ID::fromString($demoUser->getUuid());
    $tags = new DemoSeedReport('Tags');
    $images = new DemoSeedReport('Images');
    $imageTags = new DemoSeedReport('Image tags');
    $collections = new DemoSeedReport('Collections');
    $shares = new DemoSeedReport('Shares');
    $providers = new DemoSeedReport('SSO providers');

    $tagIds = $this->tagSeeder->seedTags($userId, $tags);
    $seededImages = $this->imageSeeder->seed($userId, $this->picturesPath, $images);
    $this->tagSeeder->tagImages($userId, $seededImages, $tagIds, $imageTags);
    $collectionIds = $this->collectionSeeder->seed($userId, $seededImages, $collections);
    $this->shareSeeder->seed($seededImages, $collectionIds, $shares);
    $this->ssoSeeder->seed($providers);

    return [$tags, $images, $imageTags, $collections, $shares, $providers, ...$this->seedCommunity($demoUser, $seededImages)];
  }

  /**
   * @param array<string, ImageView> $images
   * @return list<DemoSeedReport>
   */
  private function seedCommunity(UserView $demoUser, array $images): array {
    $accounts = new DemoSeedReport('Accounts');
    $accountImages = new DemoSeedReport('Account images');

    $seededAccounts = $this->accountSeeder->seed($accounts);
    $seededAccountImages = $this->accountSeeder->seedPictures($seededAccounts, $this->picturesPath, $accountImages);

    if ($accounts->hasFailures()) {
      return [$accounts, $accountImages];
    }

    return [$accounts, $accountImages, ...$this->activitySeeder->seed($demoUser, $seededAccounts, $images, $seededAccountImages)];
  }
}
