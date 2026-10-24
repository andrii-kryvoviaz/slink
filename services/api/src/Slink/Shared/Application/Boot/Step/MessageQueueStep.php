<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Slink\Shared\Application\Boot\SubCommandRunner;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsTaggedItem(priority: 75)]
final readonly class MessageQueueStep implements BootStepInterface {
  public function __construct(
    private SubCommandRunner $subCommandRunner,
    #[Autowire('%slink.message_queue_transport%')]
    private string $transportName,
  ) {}

  public function label(): string {
    return 'message queue';
  }

  public function category(): BootCategory {
    return BootCategory::Boot;
  }

  public function run(BootContext $context): BootResult {
    $result = $this->subCommandRunner->run('messenger:setup-transports', [
      $this->transportName,
      '--no-interaction' => true,
    ]);

    if (!$result->isSuccessful()) {
      return BootResult::fail('queue setup failed', $result->output);
    }

    return BootResult::ok('ready');
  }
}
