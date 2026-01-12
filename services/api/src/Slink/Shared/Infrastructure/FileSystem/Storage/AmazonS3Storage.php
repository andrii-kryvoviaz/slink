<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Storage;

use Aws\S3\S3Client;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Shared\Infrastructure\Exception\Storage\AmazonS3Exception;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\ObjectStorageInterface;
use Symfony\Component\HttpFoundation\File\File;

final class AmazonS3Storage extends AbstractStorage implements ObjectStorageInterface {
  private S3Client $client;
  private ConfigurationProviderInterface $configurationProvider;
  
  function init(ConfigurationProviderInterface $configurationProvider): void {
    $this->configurationProvider = $configurationProvider;
    $this->client = new S3Client($this->buildClientConfig());
  }
  
  /**
   * @return array<string, mixed>
   */
  private function buildClientConfig(): array {
    $region = $this->configurationProvider->get('storage.adapter.s3.region');
    $endpoint = $this->configurationProvider->get('storage.adapter.s3.endpoint');
    $useCustomProvider = $this->configurationProvider->get('storage.adapter.s3.useCustomProvider') ?? (bool) $endpoint;
    
    $config = [
      'version' => 'latest',
      'credentials' => [
        'key' => $this->configurationProvider->get('storage.adapter.s3.key'),
        'secret' => $this->configurationProvider->get('storage.adapter.s3.secret')
      ]
    ];
    
    if ($region) {
      $config['region'] = $region;
    }
    
    if (!$useCustomProvider) {
      return $config;
    }
    
    if ($endpoint) {
      $config['endpoint'] = $endpoint;
    }
    
    $config['use_path_style_endpoint'] = (bool) $this->configurationProvider->get('storage.adapter.s3.forcePathStyle');
    
    return $config;
  }
  
  /**
   * @param File $file
   * @param string $fileName
   * @return void
   */
  public function upload(File $file, string $fileName): void {
    $filePath = $file->getRealPath();
    if (!$filePath) {
      throw new AmazonS3Exception('Something went wrong while uploading the file');
    }
    
    try {
      $this->client->putObject([
        'Bucket' => $this->getBucket(),
        'Key' => $fileName,
        'SourceFile' => $filePath
      ]);
    } catch (\Exception $e) {
      throw new AmazonS3Exception($e->getMessage());
    }
  }
  
  /**
   * @param string $fileName
   * @return void
   */
  public function delete(string $fileName): void {
    [$name, $_] = explode('.', $fileName);
    
    $this->deleteByPrefix($name);
  }
  
  public function deleteByPrefix(string $prefix): void {
    try {
      $bucket = $this->getBucket();
      
      $result = $this->client->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $prefix,
      ]);
      
      if (!empty($result['Contents'])) {
        $objectsToDelete = array_map(fn($object) => ['Key' => $object['Key']], $result['Contents']);
        
        $this->client->deleteObjects([
          'Bucket' => $bucket,
          'Delete' => [
            'Objects' => $objectsToDelete,
            'Quiet' => true,
          ],
        ]);
      }
    } catch (\Exception $e) {
      throw new AmazonS3Exception($e->getMessage());
    }
  }
  
  /**
   * @param string $path
   * @return bool
   */
  public function exists(string $path): bool {
    try {
      $this->client->headObject([
        'Bucket' => $this->getBucket(),
        'Key' => $path
      ]);
      
      return true;
    } catch (\Exception $e) {
      return false;
    }
  }
  
  /**
   * @param string $path
   * @param string $content
   * @return void
   */
  public function write(string $path, string $content): void {
    try {
      $this->client->putObject([
        'Bucket' => $this->getBucket(),
        'Key' => $path,
        'Body' => $content
      ]);
    } catch (\Exception $e) {
      throw new AmazonS3Exception($e->getMessage());
    }
  }
  
  /**
   * @param string $path
   * @return string|null
   */
  public function read(string $path): ?string {
    try {
      $result = $this->client->getObject([
        'Bucket' => $this->getBucket(),
        'Key' => $path
      ]);
      
      return $result['Body']->getContents();
    } catch (\Exception $e) {
      return null;
    }
  }
  
  /**
   * @return int
   */
  public function clearCache(): int {
    try {
      $bucket = $this->getBucket();
      $cachePrefix = $this->cacheDir . '/';
      
      $result = $this->client->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $cachePrefix,
      ]);
      
      if (empty($result['Contents'])) {
        return 0;
      }
      
      $objectsToDelete = array_map(fn($object) => ['Key' => $object['Key']], $result['Contents']);
      $count = count($objectsToDelete);
      
      $this->client->deleteObjects([
        'Bucket' => $bucket,
        'Delete' => [
          'Objects' => $objectsToDelete,
          'Quiet' => true,
        ],
      ]);
      
      return $count;
    } catch (\Exception $e) {
      throw new AmazonS3Exception($e->getMessage());
    }
  }
  
  /**
   * @return string
   */
  public static function getAlias(): string {
    return StorageProvider::AmazonS3->value;
  }
  
  private function getBucket(): string {
    return $this->configurationProvider->get('storage.adapter.s3.bucket');
  }
}
