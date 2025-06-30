<?php

declare(strict_types=1);

namespace Slink\Settings\Domain;

use Slink\Settings\Domain\Event\SettingsChanged;
use Slink\Settings\Domain\Exception\InvalidSettingsException;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;
use Slink\Settings\Domain\ValueObject\Image\ImageSettings;
use Slink\Settings\Domain\ValueObject\Storage\StorageSettings;
use Slink\Settings\Domain\ValueObject\User\UserSettings;
use Slink\Shared\Domain\AbstractAggregateRoot;
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
  
  private StorageSettings $storage;
  private UserSettings $user;
  private ImageSettings $image;
  
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