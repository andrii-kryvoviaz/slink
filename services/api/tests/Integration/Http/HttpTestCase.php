<?php

declare(strict_types=1);

namespace Tests\Integration\Http;

use Slink\Settings\Application\Command\SaveSettings\SaveSettingsCommand;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Application\Command\CreateUser\CreateUserCommand;
use Slink\User\Application\Command\GrantRole\GrantRoleCommand;
use Slink\User\Domain\Enum\UserStatus;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class HttpTestCase extends WebTestCase {
  private static string $readModelDb = '';
  private static string $eventStoreDb = '';
  private static bool $schemaProvisioned = false;

  protected KernelBrowser $client;

  #[\Override]
  protected static function getKernelClass(): string {
    return HttpTestKernel::class;
  }

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();

    $projectDir = \dirname(__DIR__, 3);
    $token = \getenv('TEST_TOKEN') ?: 'shared';
    $dir = $projectDir . '/var/functional-test/' . \md5((string) $token);

    if (!\is_dir($dir)) {
      \mkdir($dir, 0777, true);
    }

    self::$readModelDb = $dir . '/read_model.db';
    self::$eventStoreDb = $dir . '/event_store.db';

    self::applyDatabaseEnv();
    self::provisionSchema($projectDir);
  }

  protected function setUp(): void {
    parent::setUp();

    self::applyDatabaseEnv();
    $this->truncateDatabases();

    $this->client = static::createClient();
    $this->client->disableReboot();
    $this->client->catchExceptions(true);
  }

  protected function tearDown(): void {
    parent::tearDown();
    self::ensureKernelShutdown();
  }

  private static function applyDatabaseEnv(): void {
    $readModelUrl = 'sqlite:///' . self::$readModelDb;
    $eventStoreUrl = 'sqlite:///' . self::$eventStoreDb;

    $_ENV['DATABASE_URL'] = $readModelUrl;
    $_SERVER['DATABASE_URL'] = $readModelUrl;
    \putenv('DATABASE_URL=' . $readModelUrl);

    $_ENV['ES_DATABASE_URL'] = $eventStoreUrl;
    $_SERVER['ES_DATABASE_URL'] = $eventStoreUrl;
    \putenv('ES_DATABASE_URL=' . $eventStoreUrl);

    foreach (self::requiredEnv() as $name => $value) {
      $_ENV[$name] = $value;
      $_SERVER[$name] = $value;
      \putenv($name . '=' . $value);
    }
  }

  /**
   * @return array<string, string>
   */
  private static function requiredEnv(): array {
    return [
      'ORIGIN' => 'http://localhost',
    ];
  }

  private static function provisionSchema(string $projectDir): void {
    if (self::$schemaProvisioned) {
      return;
    }

    @\unlink(self::$readModelDb);
    @\unlink(self::$eventStoreDb);

    $eventStoreConfig = self::$readModelDb . '.es_migrations.yaml';
    $migrationsPath = $projectDir . '/src/Slink/Shared/Infrastructure/Persistence/EventStore/Migrations';
    \file_put_contents(
      $eventStoreConfig,
      \sprintf(
        "migrations_paths:\n    Slink\\Shared\\Infrastructure\\Persistence\\EventStore\\Migrations: %s\nconnection: event_store\n",
        $migrationsPath,
      ),
    );

    self::runConsole([
      'command' => 'doctrine:migrations:migrate',
      '--no-interaction' => true,
      '--allow-no-migration' => true,
    ]);

    self::runConsole([
      'command' => 'doctrine:migrations:migrate',
      '--no-interaction' => true,
      '--allow-no-migration' => true,
      '--em' => 'event_store',
      '--configuration' => $eventStoreConfig,
    ]);

    self::ensureKernelShutdown();
    self::$schemaProvisioned = true;
  }

  /**
   * @param array<string, bool|string> $command
   */
  private static function runConsole(array $command): void {
    $kernel = self::bootKernel(['environment' => 'test']);
    $application = new Application($kernel);
    $application->setAutoExit(false);

    $output = new BufferedOutput();
    $exitCode = $application->run(new ArrayInput($command), $output);

    if ($exitCode !== 0) {
      throw new \RuntimeException(\sprintf(
        "Console command failed: %s\n%s",
        (string) ($command['command'] ?? ''),
        $output->fetch(),
      ));
    }
  }

  private function truncateDatabases(): void {
    foreach ([self::$readModelDb, self::$eventStoreDb] as $database) {
      if (!\is_file($database)) {
        continue;
      }

      $preserved = ['user_role'];
      $pdo = new \PDO('sqlite:' . $database);
      $tables = $pdo->query(
        "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name NOT LIKE 'doctrine_migration%'",
      );

      if ($tables === false) {
        continue;
      }

      foreach ($tables->fetchAll(\PDO::FETCH_COLUMN) as $table) {
        if (\in_array($table, $preserved, true)) {
          continue;
        }

        $pdo->exec(\sprintf('DELETE FROM "%s"', $table));
      }
    }
  }

  protected function commandBus(): CommandBusInterface {
    /** @var CommandBusInterface $bus */
    $bus = static::getContainer()->get(CommandBusInterface::class);

    return $bus;
  }

  /**
   * @param array<string, bool> $flags
   */
  protected function setAccessSettings(array $flags): void {
    $defaults = [
      'allowGuestUploads' => false,
      'allowUnauthenticatedAccess' => false,
      'requireSsl' => false,
      'requireAuthForMediaShares' => false,
      'requireAuthForCollectionShares' => false,
    ];

    $this->commandBus()->handle(new SaveSettingsCommand('access', \array_merge($defaults, $flags)));
  }

  /**
   * @param array<string, mixed> $settings
   */
  protected function saveSettings(string $category, array $settings): void {
    $this->commandBus()->handle(new SaveSettingsCommand($category, $settings));
  }

  protected function createUser(
    string $email,
    string $username,
    string $password,
    UserStatus $status = UserStatus::Active,
  ): string {
    $command = new CreateUserCommand($email, $password, $username, $username, $status);
    $this->commandBus()->handle($command);

    return $command->getId()->toString();
  }

  protected const string PASSWORD = 'Password123!';
  protected const string SHARE_PASSWORD = 'SharePw123!';

  protected function grantAdmin(string $userId): void {
    $this->commandBus()->handle(new GrantRoleCommand($userId, 'ROLE_ADMIN'));
  }

  protected function login(string $username, string $password): string {
    $this->client->request(
      'POST',
      '/api/auth/login',
      [],
      [],
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['username' => $username, 'password' => $password], JSON_THROW_ON_ERROR),
    );

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), 'Login failed: ' . (string) $response->getContent());

    /** @var array{access_token?: string, accessToken?: string, token?: string} $payload */
    $payload = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['access_token'] ?? $payload['accessToken'] ?? $payload['token'] ?? '';
  }

  /**
   * @param array<string, string> $server
   */
  protected function apiRequest(
    string $method,
    string $path,
    ?string $token = null,
    array $server = [],
    ?string $body = null,
  ): int {
    $headers = $server;

    if ($token !== null) {
      $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }

    $this->client->request($method, $path, [], [], $headers, $body);

    return $this->client->getResponse()->getStatusCode();
  }

  protected function uploadImage(string $token, bool $isPublic): string {
    $file = $this->sampleImage();
    $parameters = [];

    if ($isPublic) {
      $parameters['isPublic'] = 'true';
    }

    $this->client->request(
      'POST',
      '/api/upload',
      $parameters,
      ['image' => $file],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $token],
    );

    $response = $this->client->getResponse();
    self::assertContains(
      $response->getStatusCode(),
      [200, 201],
      'Upload failed: ' . (string) $response->getContent(),
    );

    return $this->extractId((string) $response->getContent());
  }

  protected function createCollection(string $token): string {
    $this->client->request(
      'POST',
      '/api/collection',
      [],
      [],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
      \json_encode(['name' => 'functional-collection'], JSON_THROW_ON_ERROR),
    );

    $response = $this->client->getResponse();
    self::assertContains(
      $response->getStatusCode(),
      [200, 201],
      'Create collection failed: ' . (string) $response->getContent(),
    );

    return $this->extractId((string) $response->getContent());
  }

  protected function addImageToCollection(string $token, string $collectionId, string $imageId): void {
    $this->apiRequest(
      'POST',
      \sprintf('/api/collection/%s/items/%s', $collectionId, $imageId),
      $token,
    );
  }

  protected function createImageShare(string $token, string $imageId): string {
    $this->apiRequest('GET', \sprintf('/api/image/%s/share', $imageId), $token);

    return $this->extractId((string) $this->client->getResponse()->getContent());
  }

  protected function createCollectionShare(string $token, string $collectionId): string {
    $this->client->request(
      'POST',
      \sprintf('/api/collection/%s/share', $collectionId),
      [],
      [],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
      '{}',
    );

    return $this->extractId((string) $this->client->getResponse()->getContent());
  }

  protected function publishShare(string $token, string $shareId): void {
    $this->apiRequest('PUT', \sprintf('/api/share/%s/publish', $shareId), $token);
  }

  protected function unpublishShare(string $token, string $shareId): void {
    $this->apiRequest('PUT', \sprintf('/api/share/%s/unpublish', $shareId), $token);
  }

  protected function setSharePassword(string $token, string $shareId, string $password): void {
    $this->apiRequest(
      'PUT',
      \sprintf('/api/share/%s/password', $shareId),
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['password' => $password], JSON_THROW_ON_ERROR),
    );
  }

  protected function setShareExpiration(string $token, string $shareId, string $isoDate): void {
    $this->apiRequest(
      'PUT',
      \sprintf('/api/share/%s/expiration', $shareId),
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['expiresAt' => $isoDate], JSON_THROW_ON_ERROR),
    );
  }

  /**
   * @return array{0: int, 1: array<int, string>}
   */
  protected function unlockShare(string $shareId, string $password): array {
    $this->client->request(
      'POST',
      \sprintf('/api/share/%s/unlock', $shareId),
      [],
      [],
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['password' => $password], JSON_THROW_ON_ERROR),
    );

    $response = $this->client->getResponse();
    $cookies = [];

    foreach ($response->headers->getCookies() as $cookie) {
      $cookies[] = $cookie->getName() . '=' . (string) $cookie->getValue();
    }

    return [$response->getStatusCode(), $cookies];
  }

  /**
   * @param array<int, string> $cookies
   */
  protected function requestWithCookies(string $method, string $path, array $cookies): int {
    $headers = [];

    if ($cookies !== []) {
      $headers['HTTP_COOKIE'] = \implode('; ', $cookies);
    }

    $this->client->request($method, $path, [], [], $headers);

    return $this->client->getResponse()->getStatusCode();
  }

  protected function futureIso(int $offsetSeconds): string {
    return (new \DateTimeImmutable('+' . $offsetSeconds . ' seconds', new \DateTimeZone('UTC')))
      ->format('Y-m-d\TH:i:sP');
  }

  protected function sampleImage(): UploadedFile {
    $temp = \tempnam(\sys_get_temp_dir(), 'slink_functional_') . '.png';

    \file_put_contents($temp, self::uniquePng());

    return new UploadedFile($temp, 'sample.png', 'image/png', null, true);
  }

  private static function uniquePng(): string {
    $width = 2;
    $height = 2;

    $ihdr = \pack('N', $width)
      . \pack('N', $height)
      . \chr(8)
      . \chr(2)
      . \chr(0)
      . \chr(0)
      . \chr(0);

    $raw = '';
    for ($y = 0; $y < $height; $y++) {
      $raw .= \chr(0);
      for ($x = 0; $x < $width * 3; $x++) {
        $raw .= \chr(\random_int(0, 255));
      }
    }

    $compressed = \gzcompress($raw, 9);
    if ($compressed === false) {
      throw new \RuntimeException('Unable to compress test image data.');
    }

    return "\x89PNG\r\n\x1a\n"
      . self::pngChunk('IHDR', $ihdr)
      . self::pngChunk('IDAT', $compressed)
      . self::pngChunk('IEND', '');
  }

  private static function pngChunk(string $type, string $data): string {
    return \pack('N', \strlen($data))
      . $type
      . $data
      . \pack('N', \crc32($type . $data));
  }

  protected function extractId(string $json): string {
    /** @var array<string, mixed> $payload */
    $payload = \json_decode($json, true, 512, JSON_THROW_ON_ERROR) ?: [];

    if (isset($payload['data']) && \is_array($payload['data'])) {
      /** @var array<string, mixed> $payload */
      $payload = $payload['data'];
    }

    foreach (['id', 'shareId', 'collectionId', 'imageId'] as $field) {
      if (isset($payload[$field]) && \is_string($payload[$field])) {
        return $payload[$field];
      }
    }

    return '';
  }
}
