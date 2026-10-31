<?php

declare(strict_types=1);

namespace Slink\Settings\Domain;

use Slink\Settings\Domain\Event\SettingsChanged;
use Slink\Settings\Domain\Exception\InvalidSettingsException;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;
use Slink\Settings\Domain\ValueObject\Access\AccessSettings;
use Slink\Settings\Domain\ValueObject\Customization\CustomizationSettings;
use Slink\Settings\Domain\ValueObject\Image\ImageSettings;
use Slink\Settings\Domain\ValueObject\Share\ShareSettings;
use Slink\Settings\Domain\ValueObject\Storage\StorageSettings;
use Slink\Settings\Domain\ValueObject\User\UserSettings;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\Exception\SpecificationException;
use Slink\Shared\Domain\ValueObject\ID;

final class Settings extends AbstractAggregateRoot {
  /**
   * @var string
   */
  private static string $idReference = 'settings.global';
  
  /**
   * @return string
   */
  public static function getIdReference(): string {
    return self::$idReference;
  }
  
  /**
   *
   */
  protected function __construct() {
    $id = ID::fromString(self::getIdReference());
    parent::__construct($id);
  }

  private ?StorageSettings $storage = null;
  private ?UserSettings $user = null;
  private ?ImageSettings $image = null;
  private ?AccessSettings $access = null;
  private ?ShareSettings $share = null;
  private ?CustomizationSettings $customization = null;
  
  /**
   * @param array<string, mixed>|null $storage
   * @param array<string, mixed>|null $user
   * @param array<string, mixed>|null $image
   * @param array<string, mixed>|null $access
   * @param array<string, mixed>|null $share
   * @param array<string, mixed>|null $customization
   */
  public static function fromView(
    ?array $storage,
    ?array $user,
    ?array $image,
    ?array $access,
    ?array $share,
    ?array $customization,
  ): self {
    $settings = new self();

    $settings->storage = self::hydrateCategory($storage, StorageSettings::fromPayload(...));
    $settings->user = self::hydrateCategory($user, UserSettings::fromPayload(...));
    $settings->image = self::hydrateCategory($image, ImageSettings::fromPayload(...));
    $settings->access = self::hydrateCategory($access, AccessSettings::fromPayload(...));
    $settings->share = self::hydrateCategory($share, ShareSettings::fromPayload(...));
    $settings->customization = self::hydrateCategory($customization, CustomizationSettings::fromPayload(...));

    return $settings;
  }

  /**
   * @template T of AbstractSettingsValueObject
   * @param array<string, mixed>|null $payload
   * @param \Closure(array<string, mixed>): T $factory
   * @return T|null
   */
  private static function hydrateCategory(?array $payload, \Closure $factory): ?AbstractSettingsValueObject {
    if ($payload === null) {
      return null;
    }

    try {
      return $factory($payload);
    } catch (SpecificationException) {
      return null;
    }
  }

  /**
   * @param array<int, ?AbstractSettingsValueObject> $data
   */
  public function initialize(array $data): void {
    foreach ($data as $value) {
      if (!$value instanceof AbstractSettingsValueObject) {
        throw new InvalidSettingsException("Invalid Settings Value Object provided");
      }

      $category = $value->getSettingsCategory();
      $categoryKey = $category->getCategoryKey();

      $this->{$categoryKey} = $value;
    }
  }

  /**
   * @param string $key
   * @return mixed
   */
  public function get(string $key): mixed {
    $path = explode('.', $key);
    $root = array_shift($path);

    if(!isset($this->{$root})) {
      throw new \RuntimeException('Invalid Settings key');
    }

    $payload = $this->{$root}->toPayload();

    foreach ($path as $segment) {
      if(!isset($payload[$segment])) {
        throw new \RuntimeException('Invalid Settings key');
      }

      $payload = $payload[$segment];
    }

    return $payload;
  }
  
  public function setSettings(AbstractSettingsValueObject $settings): void {
    $category = $settings->getSettingsCategory();
    $categoryKey = $category->getCategoryKey();
    
    if (!property_exists($this, $categoryKey)) {
      throw new \RuntimeException('Invalid Settings Category');
    }
    
    $this->recordThat(new SettingsChanged($category, $settings));
  }
  
  public function applySettingsChanged(SettingsChanged $event): void {
    $categoryKey = $event->category->getCategoryKey();
    $this->{$categoryKey} = $event->settings;
  }
}