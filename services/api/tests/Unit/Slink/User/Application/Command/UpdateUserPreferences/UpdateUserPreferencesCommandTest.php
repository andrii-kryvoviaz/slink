<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\User\Application\Command\UpdateUserPreferences;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Infrastructure\Serializer\ReadonlyObjectDenormalizer;
use Slink\User\Application\Command\UpdateUserPreferences\UpdateUserPreferencesCommand;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
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
        $command = $this->command(['image.stripExifMetadataOverride' => 'keep']);

        $violations = $this->validator()->validate($command);

        $this->assertCount(0, $violations);
    }

    #[Test]
    public function itRejectsInvalidExifMetadataPreference(): void {
        $command = $this->command(['image.stripExifMetadataOverride' => 'invalid']);

        $violations = $this->validator()->validate($command);

        $this->assertCount(1, $violations);

        $violation = $violations->get(0);
        $this->assertSame('exifMetadataPreference', $violation->getPropertyPath());
    }

    /**
     * @param array<string, mixed> $body
     */
    private function command(array $body): UpdateUserPreferencesCommand {
        return $this->denormalizer()->denormalize($body, UpdateUserPreferencesCommand::class);
    }

    private function denormalizer(): ReadonlyObjectDenormalizer {
        $metadata = new ClassMetadataFactory(new AttributeLoader());

        return new ReadonlyObjectDenormalizer(new PropertyNormalizer($metadata, new MetadataAwareNameConverter($metadata)));
    }

    private function validator(): ValidatorInterface {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }
}
