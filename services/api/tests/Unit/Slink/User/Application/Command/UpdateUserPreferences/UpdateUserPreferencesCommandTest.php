<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\User\Application\Command\UpdateUserPreferences;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Application\Command\UpdateUserPreferences\UpdateUserPreferencesCommand;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateUserPreferencesCommandTest extends TestCase {
    #[Test]
    public function itPassesValidationWhenNoPropertyIsSet(): void {
        $command = new UpdateUserPreferencesCommand();

        $violations = $this->validator()->validate($command);

        $this->assertCount(0, $violations);
    }

    #[Test]
    public function itAcceptsValidExifMetadataPreference(): void {
        $command = new UpdateUserPreferencesCommand();
        $command->exifMetadataPreference = 'keep';

        $violations = $this->validator()->validate($command);

        $this->assertCount(0, $violations);
    }

    #[Test]
    public function itRejectsInvalidExifMetadataPreference(): void {
        $command = new UpdateUserPreferencesCommand();
        $command->exifMetadataPreference = 'invalid';

        $violations = $this->validator()->validate($command);

        $this->assertCount(1, $violations);

        $violation = $violations->get(0);
        $this->assertSame('exifMetadataPreference', $violation->getPropertyPath());
    }

    private function validator(): ValidatorInterface {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }
}
