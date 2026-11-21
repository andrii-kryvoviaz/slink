<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\MessageBus\Event\EventDispatcher;
use Slink\User\Application\Command\GrantRole\GrantRoleCommand;
use Slink\User\Application\Command\ResetPassword\ResetPasswordCommand;
use Slink\User\Application\Command\RevokeRole\RevokeRoleCommand;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Event\Auth\RefreshTokenRevoked;
use Slink\User\Domain\Event\OAuth\OAuthAccountWasLinked;
use Slink\User\Domain\Event\UserLoggedOut;
use Slink\User\Domain\Event\UserSignedIn;
use Slink\User\Domain\Event\UserWasPurged;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Repository\UserPreferencesRepositoryInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Specification\CurrentUserSpecificationInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\UserPreferences;
use Slink\User\Domain\ValueObject\Username;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Integration\Http\Double\RecordingMessageDispatcher;
use Tests\Integration\Http\Double\StubOAuthAdapter;
use Tests\Integration\Http\Store\ViewBackedStoreRoundTripTestCase;

/**
 * @extends ViewBackedStoreRoundTripTestCase<User>
 */
#[Group('sc-1180')]
final class UserStoreRoundTripTest extends ViewBackedStoreRoundTripTestCase {
  use ReloadsUsers;

  private const string EMAIL = 'member@local.test';
  private const string USERNAME = 'memberuser';
  private const string RAW_NAME = 'Ann & Bob';
  private const string STORED_NAME = 'Ann &amp; Bob';
  private const string SUBJECT = 'subject-member';
  private const string NEW_PASSWORD = 'NewPassword456!';
  private const string UNKNOWN_USER_ID = '11111111-2222-4333-8444-555555555555';

  protected function setUp(): void {
    parent::setUp();

    StubOAuthAdapter::reset();
  }

  protected function tearDown(): void {
    StubOAuthAdapter::reset();

    parent::tearDown();
  }

  #[\Override]
  protected function createAggregate(): ID {
    return ID::fromString($this->createUserWithRolesSubjectAndPreferences(
      self::EMAIL,
      self::USERNAME,
      self::RAW_NAME,
      self::SUBJECT,
    ));
  }

  #[\Override]
  protected function fetch(ID $id): User {
    return $this->userStore()->get($id);
  }

  #[\Override]
  protected function aggregateState(object $aggregate): array {
    return [
      'email' => $this->emailOf($aggregate),
      'username' => $aggregate->getUsername()?->toString(),
      'displayName' => $aggregate->getDisplayName()->toString(),
      'passwordHash' => $this->passwordHashOf($aggregate),
      'status' => $aggregate->getStatus()->value,
      'roles' => $this->sortedRolesOf($aggregate),
      'subjects' => $this->subjectsOf($aggregate),
      'preferences' => $aggregate->getPreferences()->toPayload(),
    ];
  }

  #[\Override]
  protected function storedState(ID $id): array {
    $row = $this->readModel()->fetchAssociative('SELECT * FROM "user" WHERE uuid = ?', [$id->toString()]);
    self::assertIsArray($row);

    /** @var UserPreferencesRepositoryInterface $preferences */
    $preferences = static::getContainer()->get(UserPreferencesRepositoryInterface::class);

    return [
      'email' => $row['email'],
      'username' => $row['username'],
      'displayName' => $row['display_name'],
      'passwordHash' => $row['password'],
      'status' => $row['status'],
      'roles' => $this->storedRolesWithTheDefault($id),
      'subjects' => $this->readModel()->fetchFirstColumn(
        "SELECT provider_slug || ':' || provider_user_id FROM oauth_link WHERE user_id = ?",
        [$id->toString()],
      ),
      'preferences' => $preferences->findByUserId($id->toString())?->getPreferences()->toPayload(),
    ];
  }

  #[\Override]
  protected function rootTable(): string {
    return 'user';
  }

  /**
   * @return list<mixed>
   */
  private function storedRolesWithTheDefault(ID $id): array {
    $stored = $this->readModel()->fetchFirstColumn(
      'SELECT role FROM user_to_role WHERE user_id = ?',
      [$id->toString()],
    );
    $roles = \array_values(\array_unique([...$stored, 'ROLE_USER']));
    \sort($roles);

    return $roles;
  }

  /**
   * @param array<string, bool|string> $arguments
   * @return array{int, string}
   */
  private function purgeCommandResult(array $arguments): array {
    $kernel = self::$kernel;
    self::assertNotNull($kernel);
    $tester = new CommandTester((new Application($kernel))->find('user:purge'));
    $exitCode = $tester->execute($arguments, ['interactive' => false]);

    return [$exitCode, $tester->getDisplay()];
  }

  #[Test]
  public function aReloadedUserHoldsTheValuesItWasCreatedWith(): void {
    $id = $this->createAggregate();

    $user = $this->reload($id);

    self::assertSame(self::EMAIL, $this->emailOf($user));
    self::assertSame(self::USERNAME, $user->getUsername()?->toString());
    self::assertSame(self::STORED_NAME, $user->getDisplayName()->toString());
    self::assertTrue(HashedPassword::fromHash($this->passwordHashOf($user))->match(self::PASSWORD));
    self::assertSame(UserStatus::Active, $user->getStatus());
    self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $this->sortedRolesOf($user));
    self::assertSame(['acme:' . self::SUBJECT], $this->subjectsOf($user));
    self::assertSame(self::PREFERENCES, $user->getPreferences()->toPayload());
  }

  #[Test]
  public function aReloadedUserTakesItsStateFromItsRows(): void {
    $id = $this->createAggregate();
    $this->readModel()->executeStatement(
      'UPDATE "user" SET email = ?, username = ?, display_name = ?, password = ?, status = ? WHERE uuid = ?',
      [
        'renamed@local.test',
        'renameduser',
        'Renamed Name',
        HashedPassword::encode(self::NEW_PASSWORD)->toString(),
        UserStatus::Suspended->value,
        $id->toString(),
      ],
    );
    $this->readModel()->executeStatement(
      'DELETE FROM user_to_role WHERE user_id = ? AND role = ?',
      [$id->toString(), 'ROLE_ADMIN'],
    );
    $this->readModel()->executeStatement(
      'UPDATE oauth_link SET provider_user_id = ? WHERE user_id = ?',
      ['subject-renamed', $id->toString()],
    );

    $user = $this->reload($id);

    self::assertSame('renamed@local.test', $this->emailOf($user));
    self::assertSame('renameduser', $user->getUsername()?->toString());
    self::assertSame('Renamed Name', $user->getDisplayName()->toString());
    self::assertTrue(HashedPassword::fromHash($this->passwordHashOf($user))->match(self::NEW_PASSWORD));
    self::assertSame(UserStatus::Suspended, $user->getStatus());
    self::assertSame(['ROLE_USER'], $this->sortedRolesOf($user));
    self::assertSame(['acme:subject-renamed'], $this->subjectsOf($user));
  }

  #[Test]
  public function aUserWithoutPreferencesRowReloadsWithEmptyPreferences(): void {
    $id = $this->createAggregate();
    $this->readModel()->executeStatement('DELETE FROM user_preferences WHERE user_id = ?', [$id->toString()]);

    self::assertSame(UserPreferences::empty()->toPayload(), $this->reload($id)->getPreferences()->toPayload());
  }

  #[Test]
  public function aUserWithoutRoleRowsReloadsWithTheDefaultRole(): void {
    $id = $this->createAggregate();
    $this->readModel()->executeStatement('DELETE FROM user_to_role WHERE user_id = ?', [$id->toString()]);

    self::assertSame(['ROLE_USER'], $this->sortedRolesOf($this->reload($id)));
  }

  #[Test]
  public function aReloadedUserSignsInWithItsStoredPasswordAndRejectsAnother(): void {
    $id = $this->bootMember();
    $this->userEntityManager()->clear();

    $user = $this->reload($id);
    $user->signIn(self::PASSWORD);

    self::assertInstanceOf(UserSignedIn::class, $user->releaseEvents()[0]);

    $this->expectException(InvalidCredentialsException::class);

    $user->signIn('WrongPassword789!');
  }

  #[Test]
  public function aReloadedUserDoesNotLinkAStoredSubjectAgain(): void {
    $id = $this->createAggregate();
    $this->userEntityManager()->clear();

    $user = $this->reload($id);
    $user->link($this->identity(self::SUBJECT, self::EMAIL));

    self::assertSame([], $user->releaseEvents());

    $user->link($this->identity('subject-other', self::EMAIL));

    $events = $user->releaseEvents();
    self::assertCount(1, $events);
    self::assertInstanceOf(OAuthAccountWasLinked::class, $events[0]);
  }

  #[Test]
  public function aLinkedUserSignsInThroughSsoAgainWithoutASecondLink(): void {
    $id = $this->createAggregate();
    $this->userEntityManager()->clear();

    $this->signInThroughSso($this->identity(self::SUBJECT, self::EMAIL));

    self::assertSame(1, $this->countRowsByColumn('oauth_link', 'user_id', $id->toString()));
  }

  #[Test]
  public function lookupsByUsernameEmailAndRefreshTokenReturnTheUserFromItsRows(): void {
    $id = $this->bootMember();
    $tokens = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    $this->userEntityManager()->clear();
    $store = $this->userStore();

    $byUsername = $store->getByUsername(Username::fromString(self::USERNAME));
    $byEmail = $store->getByUsername(Email::fromString(self::EMAIL));
    $byRefreshToken = $store->getByRefreshToken(HashedRefreshToken::encode($tokens['refresh_token']));

    self::assertNotNull($byUsername);
    self::assertSame(self::EMAIL, $this->emailOf($byUsername));
    self::assertSame(self::USERNAME, $byEmail?->getUsername()?->toString());
    self::assertSame(self::USERNAME, $byRefreshToken?->getUsername()?->toString());
  }

  #[Test]
  public function lookupsOfAnUnknownUsernameOrRefreshTokenFindNobody(): void {
    $id = $this->bootMember();
    $tokens = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    self::assertSame(204, $this->purgeUser($this->bootAdmin(), $id->toString()));
    $store = $this->userStore();

    self::assertNull($store->getByUsername(Username::fromString('nobodyuser')));
    self::assertNull($store->getByUsername(Username::fromString(self::USERNAME)));
    self::assertNull($store->getByRefreshToken(HashedRefreshToken::encode($tokens['refresh_token'])));
  }

  #[Test]
  public function fetchingAnUnknownUserThrowsNotFound(): void {
    $this->expectException(NotFoundException::class);

    $this->userStore()->get(ID::fromString(self::UNKNOWN_USER_ID));
  }

  #[Test]
  public function aStoredTokenIsRestoredWithoutRecordingAnything(): void {
    $id = $this->bootMember();
    $tokens = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    $this->userEntityManager()->clear();
    [$store, $dispatcher] = $this->recordingStore();

    $user = $store->get($id);
    $store->store($user);

    self::assertSame([], $dispatcher->messages);

    $user->refreshToken->revoke(HashedRefreshToken::encode($tokens['refresh_token']));
    $store->store($user);

    $revoked = \array_filter(
      $dispatcher->messages,
      static fn (Message $message): bool => $message->payload() instanceof RefreshTokenRevoked,
    );
    self::assertCount(1, $revoked);
  }

  #[Test]
  public function refreshingRotatesThePresentedToken(): void {
    $id = $this->bootMember();
    $tokens = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    $this->userEntityManager()->clear();

    $rotated = $this->rotatedRefreshToken($tokens['refresh_token']);

    self::assertNotSame($tokens['refresh_token'], $rotated);
    self::assertSame(400, $this->refreshStatus($tokens['refresh_token']));
    self::assertSame(1, $this->countRowsByColumn('refresh_token', 'user_uuid', $id->toString()));
    self::assertSame(200, $this->refreshStatus($rotated));
  }

  #[Test]
  public function rotatingOneSessionLeavesTheOtherSessionsTokenWorking(): void {
    $id = $this->bootMember();
    $firstSession = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    $secondSession = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    $this->userEntityManager()->clear();

    $this->rotatedRefreshToken($firstSession['refresh_token']);

    self::assertSame(2, $this->countRowsByColumn('refresh_token', 'user_uuid', $id->toString()));
    self::assertSame(200, $this->refreshStatus($secondSession['refresh_token']));
  }

  #[Test]
  public function signingOutRevokesThePresentedTokenAndLeavesOtherSessionsWorking(): void {
    $id = $this->bootMember();
    $firstSession = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    $secondSession = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    $this->userEntityManager()->clear();

    self::assertContains($this->signOut($firstSession['refresh_token']), [200, 204]);

    self::assertSame(1, $this->countRowsByColumn('refresh_token', 'user_uuid', $id->toString()));
    self::assertSame(400, $this->refreshStatus($firstSession['refresh_token']));
    self::assertSame(200, $this->refreshStatus($secondSession['refresh_token']));
  }

  #[Test]
  public function anExpiredTokenIsRemovedWhenTheUserIsNextStored(): void {
    $id = $this->bootMember();
    $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    $this->expireRefreshTokens($id->toString());
    $this->userEntityManager()->clear();

    $this->userStore()->store($this->reload($id));
    $this->entityManager()->flush();

    self::assertSame(0, $this->countRowsByColumn('refresh_token', 'user_uuid', $id->toString()));
  }

  #[Test]
  public function signingInAgainRemovesAnExpiredTokenAndKeepsTheNewOne(): void {
    $id = $this->bootMember();
    $expired = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    $this->expireRefreshTokens($id->toString());
    $this->userEntityManager()->clear();

    $fresh = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);

    self::assertSame(1, $this->countRowsByColumn('refresh_token', 'user_uuid', $id->toString()));
    self::assertSame(400, $this->refreshStatus($expired['refresh_token']));
    self::assertSame(200, $this->refreshStatus($fresh['refresh_token']));
  }

  /**
   * @return array<string, array{string, string}>
   */
  public static function displayNamesWithSpecialCharacters(): array {
    return [
      'ampersand' => [self::RAW_NAME, self::STORED_NAME],
      'apostrophe at the length limit' => ['Siobhan O\'Callaghan-Fitzgerald', 'Siobhan O&apos;Callaghan-Fitzgerald'],
      'ampersands at the length limit' => ['a&b&c&d&e&f&g&h&i&j&k&l&m&n&o&', 'a&amp;b&amp;c&amp;d&amp;e&amp;f&amp;g&amp;h&amp;i&amp;j&amp;k&amp;l&amp;m&amp;n&amp;o&amp;'],
    ];
  }

  #[Test]
  #[DataProvider('displayNamesWithSpecialCharacters')]
  public function aDisplayNameWithSpecialCharactersReloadsAsStoredInItsRow(string $rawName, string $storedName): void {
    $userId = $this->createActiveUser(self::EMAIL, self::USERNAME, $rawName);
    self::assertSame($storedName, $this->storedDisplayName($userId));

    self::assertSame($storedName, $this->reload(ID::fromString($userId))->getDisplayName()->toString());
  }

  #[Test]
  public function aUserWithoutADisplayNameReloadsWithNoName(): void {
    $id = $this->bootMember();
    $this->clearDisplayName($id->toString());

    self::assertSame('Guest', $this->reload($id)->getDisplayName()->toString());
  }

  /**
   * @return array<string, array{UserStatus}>
   */
  public static function statusesAfterActive(): array {
    return [
      'suspended' => [UserStatus::Suspended],
      'deleted' => [UserStatus::Deleted],
    ];
  }

  #[Test]
  #[DataProvider('statusesAfterActive')]
  public function changingTheStatusLeavesAnEscapedNameUnchanged(UserStatus $status): void {
    $adminToken = $this->bootAdmin();
    $id = $this->bootMember();
    $this->userEntityManager()->clear();

    self::assertSame(200, $this->changeUserStatus($adminToken, $id->toString(), $status));

    self::assertSame(self::STORED_NAME, $this->storedDisplayName($id->toString()));
    self::assertSame(self::STORED_NAME, $this->reload($id)->getDisplayName()->toString());
  }

  #[Test]
  #[DataProvider('statusesAfterActive')]
  public function changingTheStatusLeavesAMissingNameMissing(UserStatus $status): void {
    $adminToken = $this->bootAdmin();
    $id = $this->bootMember();
    $this->clearDisplayName($id->toString());
    $this->userEntityManager()->clear();

    self::assertSame(200, $this->changeUserStatus($adminToken, $id->toString(), $status));

    self::assertNull($this->storedDisplayName($id->toString()));
    self::assertSame('Guest', $this->reload($id)->getDisplayName()->toString());
  }

  #[Test]
  public function changingThePasswordLeavesTheNameAndItsResponseUnchanged(): void {
    $id = $this->bootMember();
    $token = $this->login(self::USERNAME, self::PASSWORD);
    self::assertSame(200, $this->apiRequest('GET', '/api/user', $token));
    $nameBefore = $this->responsePayload()['data']['displayName'] ?? null;
    $this->userEntityManager()->clear();

    self::assertContains($this->changePassword($token, self::NEW_PASSWORD), [200, 204]);

    self::assertSame(self::STORED_NAME, $this->storedDisplayName($id->toString()));
    self::assertSame(200, $this->apiRequest('GET', '/api/user', $this->login(self::USERNAME, self::NEW_PASSWORD)));
    self::assertSame($nameBefore, $this->responsePayload()['data']['displayName'] ?? null);
  }

  #[Test]
  public function aDeletedUserReloadsWithoutEmailAndUsername(): void {
    $adminToken = $this->bootAdmin();
    $id = $this->createAggregate();
    $this->softDelete($adminToken, $id->toString());
    $this->userEntityManager()->clear();

    $user = $this->reload($id);

    self::assertNull($this->emailOf($user));
    self::assertNull($user->getUsername());
    self::assertSame(self::STORED_NAME, $user->getDisplayName()->toString());
    self::assertSame(UserStatus::Deleted, $user->getStatus());
    self::assertSame(['ROLE_USER'], $this->sortedRolesOf($user));
    self::assertSame([], $this->subjectsOf($user));
  }

  #[Test]
  public function aPurgedUserReloadsWithoutEmailUsernameAndPreferences(): void {
    $adminToken = $this->bootAdmin();
    $id = $this->createAggregate();
    self::assertSame(204, $this->purgeUser($adminToken, $id->toString()));
    $this->userEntityManager()->clear();

    $user = $this->reload($id);

    self::assertNull($this->emailOf($user));
    self::assertNull($user->getUsername());
    self::assertSame(UserStatus::Deleted, $user->getStatus());
    self::assertSame(['ROLE_USER'], $this->sortedRolesOf($user));
    self::assertSame([], $this->subjectsOf($user));
    self::assertSame(UserPreferences::empty()->toPayload(), $user->getPreferences()->toPayload());
  }

  #[Test]
  public function aRestrictedUserCannotSignInOrRefreshOnceItsRowSaysSo(): void {
    $adminToken = $this->bootAdmin();
    $id = $this->bootMember();
    $tokens = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    self::assertSame(200, $this->changeUserStatus($adminToken, $id->toString(), UserStatus::Banned));

    $signInStatus = $this->apiRequest(
      'POST',
      '/api/auth/login',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['username' => self::USERNAME, 'password' => self::PASSWORD], JSON_THROW_ON_ERROR),
    );

    self::assertContains($signInStatus, [400, 401, 403]);
    self::assertContains($this->refreshStatus($tokens['refresh_token']), [400, 401, 403]);
  }

  #[Test]
  public function aDeletedUserStaysDeleted(): void {
    $adminToken = $this->bootAdmin();
    $id = $this->bootMember();
    $this->softDelete($adminToken, $id->toString());
    $this->userEntityManager()->clear();

    self::assertSame(400, $this->changeUserStatus($adminToken, $id->toString(), UserStatus::Active));

    self::assertSame(UserStatus::Deleted, $this->reload($id)->getStatus());
  }

  #[Test]
  public function purgingADeletedUserTwiceSucceeds(): void {
    $adminToken = $this->bootAdmin();
    $id = $this->bootMember();
    $this->softDelete($adminToken, $id->toString());
    $this->userEntityManager()->clear();

    $statuses = [$this->purgeUser($adminToken, $id->toString()), $this->purgeUser($adminToken, $id->toString())];

    self::assertSame([204, 204], $statuses);
    self::assertSame(0, $this->countRowsByColumn('user_preferences', 'user_id', $id->toString()));
  }

  #[Test]
  public function purgingAnAlreadyPurgedUserRecordsThePurgeAgain(): void {
    $adminToken = $this->bootAdmin();
    $id = $this->bootMember();
    self::assertSame(204, $this->purgeUser($adminToken, $id->toString()));
    [$store, $dispatcher] = $this->recordingStore();

    $user = $store->get($id);
    $user->purge();
    $store->store($user);

    self::assertCount(1, $dispatcher->messages);
    self::assertInstanceOf(UserWasPurged::class, $dispatcher->messages[0]->payload());
  }

  #[Test]
  public function thePurgeCommandPurgesAnAlreadyPurgedUserAgain(): void {
    $adminToken = $this->bootAdmin();
    $id = $this->bootMember();
    self::assertSame(204, $this->purgeUser($adminToken, $id->toString()));

    [$singleExitCode, $singleOutput] = $this->purgeCommandResult(['user' => $id->toString(), '--force' => true]);
    [$allDeletedExitCode, $allDeletedOutput] = $this->purgeCommandResult(['--all-deleted' => true]);

    self::assertSame(Command::SUCCESS, $singleExitCode);
    self::assertStringNotContainsString('already purged', $singleOutput);
    self::assertSame(Command::SUCCESS, $allDeletedExitCode);
    self::assertStringContainsString($id->toString(), $allDeletedOutput);
    self::assertStringNotContainsString('No soft-deleted users left to purge.', $allDeletedOutput);
    self::assertSame(UserStatus::Deleted, $this->reload($id)->getStatus());
  }

  #[Test]
  public function everyWriteOnAUserSucceeds(): void {
    $adminToken = $this->bootAdmin();
    $id = $this->createAggregate();
    $userId = $id->toString();
    $tokens = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    $memberToken = $tokens['access_token'];

    $this->commandBus()->handle(new RevokeRoleCommand($userId, 'ROLE_ADMIN'));
    $this->commandBus()->handle(new GrantRoleCommand($userId, 'ROLE_ADMIN'));
    $this->updatePreferences($memberToken);
    self::assertContains($this->updateDisplayName($memberToken, 'Changed Name'), [200, 204]);
    $this->createApiKey($memberToken);
    $this->rotatedRefreshToken($tokens['refresh_token']);
    self::assertContains($this->changePassword($memberToken, self::NEW_PASSWORD), [200, 204]);
    $this->commandBus()->handleSync(new ResetPasswordCommand(self::EMAIL, self::PASSWORD));
    $session = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    self::assertContains($this->signOut($session['refresh_token']), [200, 204]);
    self::assertSame(200, $this->changeUserStatus($adminToken, $userId, UserStatus::Suspended));
    self::assertSame(200, $this->changeUserStatus($adminToken, $userId, UserStatus::Active));
    self::assertSame(204, $this->purgeUser($adminToken, $userId));
  }

  #[Test]
  public function storingDispatchesDecoratedMessages(): void {
    $id = $this->bootMember();
    $tokens = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    $this->userEntityManager()->clear();
    [$store, $dispatcher] = $this->recordingStore();

    $user = $store->get($id);
    $user->logout();
    $store->store($user);

    $signedOut = null;
    foreach ($dispatcher->messages as $message) {
      if ($message->payload() instanceof UserLoggedOut) {
        $signedOut = $message;
      }
    }

    self::assertNotNull($signedOut);
    self::assertSame($id->toString(), $signedOut->aggregateRootId()?->toString());
    self::assertSame('slink.user.domain.event.user_logged_out', $signedOut->header(Header::EVENT_TYPE));
    self::assertSame('slink.user.domain.user', $signedOut->header(Header::AGGREGATE_ROOT_TYPE));
    self::assertNotNull($signedOut->header(Header::AGGREGATE_ROOT_VERSION));
    self::assertNotNull($signedOut->header(Header::TIME_OF_RECORDING));
    self::assertNotNull($signedOut->header(Header::TIME_OF_RECORDING_FORMAT));
  }

  #[Test]
  public function theSecondOfTwoConcurrentWritersFailsAndTheFirstWritersStatusStays(): void {
    $id = $this->bootMember();
    $firstContainer = $this->independentContainer();
    $secondContainer = $this->independentContainer();
    $firstLoad = $this->userStoreOf($firstContainer)->get($id);
    $secondLoad = $this->userStoreOf($secondContainer)->get($id);

    $this->changeStatusAndStore($firstContainer, $firstLoad, UserStatus::Suspended);

    self::assertCausedBy(OptimisticLockException::class, $this->failureOf(
      fn () => $this->changeStatusAndStore($secondContainer, $secondLoad, UserStatus::Banned),
    ));
    self::assertSame(UserStatus::Suspended, $this->reload($id)->getStatus());
  }

  private function bootMember(): ID {
    return ID::fromString($this->createActiveUser(self::EMAIL, self::USERNAME, self::RAW_NAME));
  }

  /**
   * @return array{UserStoreRepositoryInterface, RecordingMessageDispatcher}
   */
  private function recordingStore(): array {
    $container = $this->independentContainer();
    $dispatcher = new RecordingMessageDispatcher();
    $container->set(EventDispatcher::class, $dispatcher);

    return [$this->userStoreOf($container), $dispatcher];
  }

  private function userStoreOf(ContainerInterface $container): UserStoreRepositoryInterface {
    /** @var UserStoreRepositoryInterface $store */
    $store = $container->get(UserStoreRepositoryInterface::class);

    return $store;
  }

  private function changeStatusAndStore(ContainerInterface $container, User $user, UserStatus $status): void {
    $user->changeStatus($status, $this->createStub(CurrentUserSpecificationInterface::class));
    $this->userStoreOf($container)->store($user);

    /** @var EntityManagerInterface $entityManager */
    $entityManager = $container->get(EntityManagerInterface::class);
    $entityManager->flush();
  }
}
