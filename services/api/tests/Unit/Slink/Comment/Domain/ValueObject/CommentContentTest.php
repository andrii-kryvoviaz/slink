<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Comment\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Domain\ValueObject\CommentContent;

final class CommentContentTest extends TestCase {
  #[Test]
  public function itCreatesCommentContent(): void {
    $content = CommentContent::fromString('Valid comment content');

    $this->assertEquals('Valid comment content', $content->toString());
  }

  #[Test]
  public function itThrowsExceptionForEmptyContent(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Comment content cannot be empty');

    CommentContent::fromString('');
  }

  #[Test]
  public function itThrowsExceptionForContentExceeding2000Characters(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Comment content cannot exceed 2000 characters');

    $longContent = str_repeat('a', 2001);
    CommentContent::fromString($longContent);
  }

  #[Test]
  public function itAllowsContentOf2000Characters(): void {
    $content = str_repeat('a', 2000);
    $commentContent = CommentContent::fromString($content);

    $this->assertEquals(2000, mb_strlen($commentContent->toString()));
  }

  #[Test]
  public function itAllowsSingleCharacterContent(): void {
    $commentContent = CommentContent::fromString('a');

    $this->assertEquals('a', $commentContent->toString());
  }

  #[Test]
  public function itEscapesHtmlContent(): void {
    $content = CommentContent::fromString('<script>alert("xss")</script>');

    $this->assertStringNotContainsString('<script>', $content->toString());
    $this->assertStringContainsString('&lt;script&gt;', $content->toString());
  }

  #[Test]
  public function itEscapesQuotes(): void {
    $content = CommentContent::fromString('He said "hello" and \'goodbye\'');

    $this->assertStringContainsString('&quot;', $content->toString());
    $this->assertStringContainsString('&apos;', $content->toString());
  }

  #[Test]
  public function itHandlesUnicodeContent(): void {
    $content = CommentContent::fromString('Hello 世界 🎉');

    $this->assertEquals('Hello 世界 🎉', $content->toString());
  }

  #[Test]
  public function itHandlesMultilineContent(): void {
    $content = CommentContent::fromString("Line 1\nLine 2\nLine 3");

    $this->assertStringContainsString("\n", $content->toString());
  }
}
