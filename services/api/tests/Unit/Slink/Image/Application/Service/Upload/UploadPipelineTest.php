<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Upload;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Application\Service\Upload\UploadPipeline;
use Slink\Image\Application\Service\Upload\UploadStageInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\HttpFoundation\File\File;

class UploadPipelineTest extends TestCase {
  use UploadContextFactoryTrait;

  private function recordingStage(\Closure $record): UploadStageInterface {
    return new class($record) implements UploadStageInterface {
      public function __construct(
        private readonly \Closure $record,
      ) {
      }

      public function process(UploadContext $context): UploadContext {
        ($this->record)();

        return $context;
      }
    };
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itReturnsSeedContextWhenNoStages(): void {
    $context = $this->contextWithFile($this->createStub(File::class));

    $pipeline = new UploadPipeline([]);

    $this->assertSame($context, $pipeline->run($context));
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itExecutesStagesInTheInjectedIterableOrder(): void {
    $order = [];
    $record = static function (string $label) use (&$order): \Closure {
      return static function () use (&$order, $label): void {
        $order[] = $label;
      };
    };

    $first = $this->recordingStage($record('first'));
    $second = $this->recordingStage($record('second'));
    $third = $this->recordingStage($record('third'));

    $pipeline = new UploadPipeline([$first, $second, $third]);

    $pipeline->run($this->contextWithFile($this->createStub(File::class)));

    $this->assertSame(['first', 'second', 'third'], $order);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itThreadsTheContextThroughEveryStage(): void {
    $file = $this->createStub(File::class);
    $converted = $this->createStub(File::class);

    $rewrite = new class($converted) implements UploadStageInterface {
      public function __construct(private readonly File $converted) {
      }

      public function process(UploadContext $context): UploadContext {
        return $context->withFile($this->converted);
      }
    };

    $assert = new class implements UploadStageInterface {
      public ?File $seenFile = null;

      public function process(UploadContext $context): UploadContext {
        $this->seenFile = $context->file();

        return $context;
      }
    };

    $pipeline = new UploadPipeline([$rewrite, $assert]);

    $result = $pipeline->run($this->uploadContext($file, id: ID::generate()));

    $this->assertSame($converted, $assert->seenFile);
    $this->assertSame($converted, $result->file());
  }
}
