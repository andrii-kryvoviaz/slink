<?php

declare(strict_types=1);

namespace Tests\Integration\Http\External;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Tests\Integration\Http\HttpTestCase;

final class ShareXEmulatedUploadTest extends HttpTestCase {
  private const string BASE_URL = 'http://localhost';
  private const string SHAREX_DEFAULT_REQUEST_METHOD = 'POST';
  private const string SHAREX_USER_AGENT = 'ShareX/16.0.0';

  /**
   * @var array<string, string>
   */
  private const array SHAREX_MIME_TYPES = [
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
  ];

  #[Test]
  public function shareXClientUploadsAndPublishedUrlsServeImageBytes(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);

    $token = $this->bootOwnerToken();
    $apiKey = $this->createApiKey($token);
    $this->enableAutoPublish($token);

    $result = $this->uploadThroughShareX($token, $apiKey['key']);

    $this->assertServesImageBytes($result['url']);
    $this->assertServesImageBytes($result['thumbnailUrl']);
  }

  #[Test]
  public function shareXUploadWithoutAutoPublishStaysHiddenFromGuests(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);

    $token = $this->bootOwnerToken();
    $apiKey = $this->createApiKey($token);

    $result = $this->uploadThroughShareX($token, $apiKey['key']);

    self::assertSame(404, $this->fetchAsGuest($result['url'])->getStatusCode());
    self::assertSame(404, $this->fetchAsGuest($result['thumbnailUrl'])->getStatusCode());
  }

  private function bootOwnerToken(): string {
    $this->createUser('sharex-client@local.test', 'sharexclient', self::PASSWORD);

    return $this->login('sharexclient', self::PASSWORD);
  }

  private function enableAutoPublish(string $token): void {
    $status = $this->apiRequest(
      'PATCH',
      '/api/user/preferences',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['image.externalUploadAutoPublish' => true], JSON_THROW_ON_ERROR),
    );

    self::assertContains($status, [200, 204], 'Enabling auto-publish failed.');
  }

  /**
   * @return array{url: string, thumbnailUrl: string}
   */
  private function uploadThroughShareX(string $token, string $apiKey): array {
    $config = $this->fetchShareXConfig($token, $apiKey);

    $response = $this->sendShareXUpload($config, 'sharex-screenshot.png');
    self::assertSame(201, $response->getStatusCode(), 'ShareX upload failed: ' . (string) $response->getContent());

    $body = (string) $response->getContent();
    $url = $this->resolveResultUrl($config, $body);
    $thumbnailUrl = $this->resolveShareXTemplate($this->stringField($config, 'ThumbnailURL', ''), $body);

    $this->assertResolvedUrl($url);
    $this->assertResolvedUrl($thumbnailUrl);

    return ['url' => $url, 'thumbnailUrl' => $thumbnailUrl];
  }

  /**
   * @return array<string, mixed>
   */
  private function fetchShareXConfig(string $token, string $apiKey): array {
    $this->client->request(
      'POST',
      '/api/user/sharex-config',
      [],
      [],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
      \json_encode(['apiKey' => $apiKey, 'baseUrl' => self::BASE_URL], JSON_THROW_ON_ERROR),
    );

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), 'Config request failed: ' . (string) $response->getContent());

    /** @var array<string, mixed> $config */
    $config = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $config;
  }

  /**
   * Mirrors ShareX Uploader.SendRequestFile: every aspect of the request is
   * driven by the config fields, falling back to documented ShareX defaults.
   *
   * @param array<string, mixed> $config
   */
  private function sendShareXUpload(array $config, string $fileName): Response {
    self::assertSame('MultipartFormData', $config['Body'] ?? null, 'Emulation only supports multipart uploads.');

    $requestUrl = $this->stringField($config, 'RequestURL', '');
    self::assertNotSame('', $requestUrl, 'ShareX throws when RequestURL is empty.');

    $fileFormName = $this->stringField($config, 'FileFormName', '');
    self::assertNotSame('', $fileFormName, 'ShareX throws when FileFormName is empty for multipart uploads.');

    $this->client->request(
      $this->stringField($config, 'RequestMethod', self::SHAREX_DEFAULT_REQUEST_METHOD),
      $this->applyQueryParameters($requestUrl, $config),
      $this->bodyArguments($config),
      [$fileFormName => $this->shareXFilePart($fileName)],
      $this->headersFromConfig($config),
    );

    return $this->client->getResponse();
  }

  /**
   * @param array<string, mixed> $config
   */
  private function stringField(array $config, string $field, string $default): string {
    $value = $config[$field] ?? $default;
    self::assertIsString($value);

    return $value;
  }

  /**
   * @param array<string, mixed> $config
   */
  private function applyQueryParameters(string $requestUrl, array $config): string {
    /** @var array<string, string> $parameters */
    $parameters = $config['Parameters'] ?? [];

    if ($parameters === []) {
      return $requestUrl;
    }

    return $requestUrl . '?' . \http_build_query($parameters);
  }

  /**
   * @param array<string, mixed> $config
   * @return array<string, string>
   */
  private function bodyArguments(array $config): array {
    /** @var array<string, string> $arguments */
    $arguments = $config['Arguments'] ?? [];

    return $arguments;
  }

  /**
   * @param array<string, mixed> $config
   * @return array<string, string>
   */
  private function headersFromConfig(array $config): array {
    $server = ['HTTP_USER_AGENT' => self::SHAREX_USER_AGENT];

    /** @var array<string, string> $headers */
    $headers = $config['Headers'] ?? [];

    foreach ($headers as $name => $value) {
      $server['HTTP_' . \strtoupper(\str_replace('-', '_', $name))] = $value;
    }

    return $server;
  }

  private function shareXFilePart(string $fileName): UploadedFile {
    $source = $this->sampleImage();

    return new UploadedFile($source->getPathname(), $fileName, $this->mimeTypeFromFileName($fileName), null, true);
  }

  private function mimeTypeFromFileName(string $fileName): string {
    $extension = \strtolower(\pathinfo($fileName, PATHINFO_EXTENSION));

    return self::SHAREX_MIME_TYPES[$extension] ?? 'application/octet-stream';
  }

  /**
   * @param array<string, mixed> $config
   */
  private function resolveResultUrl(array $config, string $responseBody): string {
    $template = $this->stringField($config, 'URL', '');

    if ($template === '') {
      return $responseBody;
    }

    return $this->resolveShareXTemplate($template, $responseBody);
  }

  private function resolveShareXTemplate(string $template, string $responseBody): string {
    $resolved = \preg_replace_callback(
      '/\{json:([^}]+)\}/',
      fn (array $matches): string => $this->resolveJsonPath($matches[1], $responseBody),
      $template,
    );

    return (string) $resolved;
  }

  private function resolveJsonPath(string $path, string $responseBody): string {
    if (\str_starts_with($path, '$.')) {
      $path = \substr($path, 2);
    }

    /** @var mixed $value */
    $value = \json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);

    foreach ($this->jsonPathSegments($path) as $segment) {
      if (!\is_array($value) || !\array_key_exists($segment, $value)) {
        return '';
      }

      /** @var mixed $value */
      $value = $value[$segment];
    }

    if (!\is_scalar($value)) {
      return '';
    }

    return (string) $value;
  }

  /**
   * @return list<int|string>
   */
  private function jsonPathSegments(string $path): array {
    \preg_match_all('/([^.\[\]]+)|\[(\d+)\]/', $path, $matches, PREG_SET_ORDER);

    $segments = [];

    foreach ($matches as $match) {
      if (isset($match[2])) {
        $segments[] = (int) $match[2];
        continue;
      }

      if (isset($match[1])) {
        $segments[] = $match[1];
      }
    }

    return $segments;
  }

  private function assertResolvedUrl(string $resolvedUrl): void {
    self::assertMatchesRegularExpression('#^' . \preg_quote(self::BASE_URL, '#') . '/.+#', $resolvedUrl);
    self::assertStringNotContainsString('{', $resolvedUrl);
  }

  private function assertServesImageBytes(string $resolvedUrl): void {
    $response = $this->fetchAsGuest($resolvedUrl);

    self::assertSame(200, $response->getStatusCode(), 'Resolved URL did not serve content: ' . $resolvedUrl);
    self::assertStringStartsWith('image/', (string) $response->headers->get('Content-Type'));
    self::assertNotSame('', (string) $response->getContent());
  }

  private function fetchAsGuest(string $url): Response {
    $this->client->request('GET', $url);
    $response = $this->client->getResponse();

    if (!$response->isRedirection()) {
      return $response;
    }

    return $this->followToApiContent((string) $response->headers->get('Location'));
  }

  private function followToApiContent(string $location): Response {
    // The production front-end proxies the redirect target into the API under /api; mirror that mapping here.
    $target = '/api' . (string) \parse_url($location, PHP_URL_PATH);
    $query = \parse_url($location, PHP_URL_QUERY);

    if (\is_string($query) && $query !== '') {
      $target .= '?' . $query;
    }

    $this->client->request('GET', $target);

    return $this->client->getResponse();
  }
}
