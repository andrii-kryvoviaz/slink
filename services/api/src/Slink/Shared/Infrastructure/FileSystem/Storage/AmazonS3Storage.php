<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Storage;

use Aws\S3\BatchDelete;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\StreamWrapper;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Domain\ValueObject\Storage\AmazonS3StorageSettings;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Shared\Domain\ValueObject\BaseFileName;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Exception\Storage\AmazonS3Exception;
use Slink\Shared\Domain\FileSystem\FileStream;
use Slink\Shared\Domain\FileSystem\Storage\ObjectStorageInterface;
use Symfony\Component\HttpFoundation\File\File;

final class AmazonS3Storage extends AbstractStorage implements ObjectStorageInterface {
  private S3Client $client;
  private AmazonS3StorageSettings $settings;
  
  function init(ConfigurationProviderInterface $configurationProvider): void {
    $this->settings = AmazonS3StorageSettings::fromConfig($configurationProvider);
    $this->client = new S3Client($this->settings->toClientConfig());
  }
  
  public function upload(File $file, string $fileName): void {
    $filePath = $file->getRealPath();
    if (!$filePath) {
      throw new AmazonS3Exception('Something went wrong while uploading the file');
    }
    
    try {
      $this->client->putObject([
        'Bucket' => $this->settings->getBucket(),
        'Key' => $fileName,
        'SourceFile' => $filePath
      ]);
    } catch (\Exception $e) {
      throw new AmazonS3Exception($e->getMessage());
    }
  }
  
  public function delete(string $fileName): void {
    $name = BaseFileName::fromFileName($fileName)->toString();

    $this->deleteObjectsByPrefix($this->cachePath($name . '-'), [$fileName]);
  }
  
  protected function deletePath(string $path): void {
    try {
      $this->client->deleteObject([
        'Bucket' => $this->settings->getBucket(),
        'Key' => $path
      ]);
    } catch (\Exception $e) {
      throw new AmazonS3Exception($e->getMessage());
    }
  }
  
  public function exists(string $path): bool {
    try {
      $this->client->headObject([
        'Bucket' => $this->settings->getBucket(),
        'Key' => $path
      ]);
      
      return true;
    } catch (\Exception $e) {
      return false;
    }
  }
  
  public function write(string $path, string $content): void {
    try {
      $this->client->putObject([
        'Bucket' => $this->settings->getBucket(),
        'Key' => $path,
        'Body' => $content
      ]);
    } catch (\Exception $e) {
      throw new AmazonS3Exception($e->getMessage());
    }
  }
  
  public function read(string $path): ?string {
    try {
      $result = $this->client->getObject([
        'Bucket' => $this->settings->getBucket(),
        'Key' => $path
      ]);
      
      return $result['Body']->getContents();
    } catch (\Exception $e) {
      return null;
    }
  }
  
  public function readStream(string $fileName): FileStream {
    try {
      $result = $this->client->getObject([
        'Bucket' => $this->settings->getBucket(),
        'Key' => $fileName
      ]);
    } catch (\Exception $e) {
      throw new NotFoundException();
    }

    $resource = StreamWrapper::getResource($result['Body']);

    if (!\is_resource($resource)) {
      throw new NotFoundException();
    }

    return new FileStream($resource);
  }

  public function clearCache(): int {
    return $this->deleteObjectsByPrefix($this->cacheDir . '/');
  }
  
  public static function getAlias(): string {
    return StorageProvider::AmazonS3->value;
  }
  
  /**
   * @param list<string> $extraKeys
   */
  private function deleteObjectsByPrefix(string $prefix, array $extraKeys = []): int {
    try {
      $bucket = $this->settings->getBucket();
      $objects = $this->objectsToDelete($bucket, $prefix, $extraKeys);
      
      BatchDelete::fromIterator($this->client, $bucket, $objects)->delete();
      
      return $objects->getReturn();
    } catch (\Exception $e) {
      throw new AmazonS3Exception($e->getMessage());
    }
  }
  
  /**
   * @param list<string> $extraKeys
   * @return \Generator<int, array{Key: string}, mixed, int>
   */
  private function objectsToDelete(string $bucket, string $prefix, array $extraKeys): \Generator {
    foreach ($extraKeys as $key) {
      yield ['Key' => $key];
    }
    
    $count = 0;
    $listedKeys = $this->client->getPaginator('ListObjectsV2', ['Bucket' => $bucket, 'Prefix' => $prefix])->search('Contents[].Key');
    
    foreach ($listedKeys as $key) {
      $count++;
      yield ['Key' => $key];
    }
    
    return $count;
  }
}
