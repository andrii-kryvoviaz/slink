<?php

declare(strict_types=1);

namespace Slink\User\Application\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class ShareXConfigService {
  public function __construct(
    #[Autowire('%env(ORIGIN)%')]
    private string $appOrigin
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function generateConfig(string $apiKey, ?string $baseUrl = null): array {
    $url = $baseUrl ?? $this->appOrigin;
    
    return [
      'Version' => '14.1.0',
      'Name' => 'Slink Image Upload',
      'DestinationType' => 'ImageUploader',
      'RequestMethod' => 'POST',
      'RequestURL' => "{$url}/api/external/upload",
      'Headers' => [
        'Authorization' => "Bearer {$apiKey}"
      ],
      'Body' => 'MultipartFormData',
      'FileFormName' => 'image',
      'URL' => "{$url}/{json:url}",
      'ThumbnailURL' => "{$url}/{json:thumbnailUrl}",
    ];
  }
}
