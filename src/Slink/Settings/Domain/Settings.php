<?php

declare(strict_types=1);

namespace Slink\Settings\Domain;

use Slink\Settings\Domain\ValueObject\StorageProviderSettings;
use Slink\Settings\Domain\ValueObject\UserSettings;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;
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
  private function __construct() {
    $id = ID::fromString(self::getIdReference());
    parent::__construct($id);
  }
  
  /**
   * @var StorageProviderSettings
   */
  private StorageProviderSettings $storage;
  /**
   * @var UserSettings
   */
  private UserSettings $user;
  
  /**
   * @param array<int, AbstractCompoundValueObject> $data
   */
  public function initialize(array $data): void {
    foreach ($data as $value) {
      if ($value instanceof StorageProviderSettings) {
        $this->storage = $value;
      }
      
      if ($value instanceof UserSettings) {
        $this->user = $value;
      }
    }
  }
  
  /**
   * @param string $key
   * @return mixed
   */
  public function get(string $key): mixed {
    $path = explode('.', $key);
    $root = array_shift($path);
    
    if(!isset($this->$root)) {
      throw new \RuntimeException('Invalid Settings key');
    }
    
    $payload = $this->$root->toPayload();
    
    foreach ($path as $segment) {
      if(!isset($payload[$segment])) {
        throw new \RuntimeException('Invalid Settings key');
      }
      
      $payload = $payload[$segment];
    }
    
    return $payload;
  }
  
  // ToDo: Implement Events and Projections for Settings
}