<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Exception\InvalidSettingsException;

final class InvalidSettingsExceptionTest extends TestCase {
    #[Test]
    public function itShouldCreateWithDefaultMessage(): void {
        $exception = new InvalidSettingsException();
        
        $this->assertSame('Invalid settings configuration.', $exception->getMessage());
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    #[Test]
    public function itShouldCreateWithCustomMessage(): void {
        $customMessage = 'Custom error message';
        $exception = new InvalidSettingsException($customMessage);
        
        $this->assertSame($customMessage, $exception->getMessage());
    }

    #[Test]
    public function itShouldExtendRuntimeException(): void {
        $exception = new InvalidSettingsException();
        
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
