<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\Strategy;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Strategy\JsonSerializableBehaviour;
use Slink\Shared\Domain\Strategy\StringTransformer\CamelCaseTransformer;
use Slink\Shared\Domain\Strategy\StringTransformer\PassthroughTransformer;
use Slink\Shared\Domain\Strategy\StringTransformer\StringTransformer;
use stdClass;

final class JsonSerializableBehaviourTest extends TestCase {

  #[Test]
  public function itHandlesEmptyObject(): void {
    $testObject = new EmptyTestObject();

    $result = $testObject->jsonSerialize();

    $this->assertEquals([], $result);
  }

  #[Test]
  public function itHandlesNullValues(): void {
    $testObject = new TestObjectWithNullValues();

    $result = $testObject->jsonSerialize();

    $expected = [
      'nullable_property' => null,
      'string_property' => 'test'
    ];
    $this->assertEquals($expected, $result);
  }

  #[Test]
  public function itSerializesWithCamelCaseTransformer(): void {
    $testObject = new TestObjectWithBehaviour();

    $result = $testObject->getPropertiesWithCamelCase();

    $expected = [
      'firstName' => 'John',
      'lastName' => 'Doe',
      'emailAddress' => 'john.doe@example.com'
    ];
    $this->assertEquals($expected, $result);
  }

  #[Test]
  public function itSerializesWithPassthroughTransformer(): void {
    $testObject = new TestObjectWithBehaviour();

    $result = $testObject->getPropertiesWithPassthrough();

    $expected = [
      'firstName' => 'John',
      'lastName' => 'Doe',
      'emailAddress' => 'john.doe@example.com'
    ];
    $this->assertEquals($expected, $result);
  }

  #[Test]
  public function itSerializesWithSnakeCaseByDefault(): void {
    $testObject = new TestObjectWithBehaviour();

    $result = $testObject->jsonSerialize();

    $expected = [
      'first_name' => 'John',
      'last_name' => 'Doe',
      'email_address' => 'john.doe@example.com'
    ];
    $this->assertEquals($expected, $result);
  }

  #[Test]
  public function itSkipsUninitializedProperties(): void {
    $testObject = new TestObjectWithUninitializedProperty();

    $result = $testObject->jsonSerialize();

    $expected = ['initialized_property' => 'value'];
    $this->assertEquals($expected, $result);
  }

  #[Test]
  public function itThrowsExceptionForInvalidTransformer(): void {
    $testObject = new TestObjectWithBehaviour();

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The name convertor must be a subclass of ' . StringTransformer::class);

    $testObject->getPropertiesWithInvalidTransformer();
  }
}

class TestObjectWithBehaviour {
  use JsonSerializableBehaviour;

  private string $emailAddress = 'john.doe@example.com';
  private string $firstName = 'John';
  private string $lastName = 'Doe';

  public function getEmailAddress(): string {
    return $this->emailAddress;
  }

  public function getFirstName(): string {
    return $this->firstName;
  }

  public function getLastName(): string {
    return $this->lastName;
  }

  /**
   * @return array<string, mixed>
   */
  public function getPropertiesWithCamelCase(): array {
    return $this->getProperties(CamelCaseTransformer::class);
  }

  /**
   * @return array<string, mixed>
   */
  public function getPropertiesWithInvalidTransformer(): array {
    return $this->getProperties(stdClass::class);
  }

  /**
   * @return array<string, mixed>
   */
  public function getPropertiesWithPassthrough(): array {
    return $this->getProperties(PassthroughTransformer::class);
  }
}

class TestObjectWithUninitializedProperty {
  use JsonSerializableBehaviour;

  private string $initializedProperty = 'value';

  public function getInitializedProperty(): string {
    return $this->initializedProperty;
  }
}

class EmptyTestObject {
  use JsonSerializableBehaviour;
}

class TestObjectWithNullValues {
  use JsonSerializableBehaviour;

  private null $nullableProperty = null;
  private string $stringProperty = 'test';

  public function getNullableProperty(): null {
    return $this->nullableProperty;
  }

  public function getStringProperty(): string {
    return $this->stringProperty;
  }
}
