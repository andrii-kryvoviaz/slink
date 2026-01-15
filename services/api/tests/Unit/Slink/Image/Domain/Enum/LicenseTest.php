<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Domain\Enum;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\License;

final class LicenseTest extends TestCase {
    #[Test]
    public function itReturnsCorrectTitles(): void {
        $this->assertEquals('All Rights Reserved', License::AllRightsReserved->getTitle());
        $this->assertEquals('Public Domain', License::PublicDomain->getTitle());
        $this->assertEquals('CC0', License::CC0->getTitle());
        $this->assertEquals('CC BY', License::CC_BY->getTitle());
        $this->assertEquals('CC BY-SA', License::CC_BY_SA->getTitle());
        $this->assertEquals('CC BY-NC', License::CC_BY_NC->getTitle());
        $this->assertEquals('CC BY-NC-SA', License::CC_BY_NC_SA->getTitle());
        $this->assertEquals('CC BY-ND', License::CC_BY_ND->getTitle());
        $this->assertEquals('CC BY-NC-ND', License::CC_BY_NC_ND->getTitle());
    }

    #[Test]
    public function itReturnsCorrectNames(): void {
        $this->assertEquals('All Rights Reserved', License::AllRightsReserved->getName());
        $this->assertEquals('Public Domain Work', License::PublicDomain->getName());
        $this->assertEquals('Public Domain Dedication (CC0)', License::CC0->getName());
        $this->assertEquals('Attribution', License::CC_BY->getName());
        $this->assertEquals('Attribution-ShareAlike', License::CC_BY_SA->getName());
        $this->assertEquals('Attribution-NonCommercial', License::CC_BY_NC->getName());
        $this->assertEquals('Attribution-NonCommercial-ShareAlike', License::CC_BY_NC_SA->getName());
        $this->assertEquals('Attribution-NoDerivs', License::CC_BY_ND->getName());
        $this->assertEquals('Attribution-NonCommercial-NoDerivs', License::CC_BY_NC_ND->getName());
    }

    #[Test]
    public function itReturnsDescriptionForAllLicenses(): void {
        $this->assertStringContainsString('All rights are reserved', License::AllRightsReserved->getDescription());
        $this->assertStringContainsString('free of known copyright', License::PublicDomain->getDescription());
        $this->assertStringContainsString('waived all rights', License::CC0->getDescription());
        $this->assertStringContainsString('distribute, remix, adapt', License::CC_BY->getDescription());
        $this->assertStringContainsString('identical terms', License::CC_BY_SA->getDescription());
        $this->assertStringContainsString('non-commercially', License::CC_BY_NC->getDescription());
        $this->assertStringContainsString('non-commercially', License::CC_BY_NC_SA->getDescription());
        $this->assertStringContainsString('not adapt', License::CC_BY_ND->getDescription());
        $this->assertStringContainsString('non-commercially', License::CC_BY_NC_ND->getDescription());
    }

    #[Test]
    public function itReturnsNullUrlForNonCreativeCommonsLicenses(): void {
        $this->assertNull(License::AllRightsReserved->getUrl());
        $this->assertNull(License::PublicDomain->getUrl());
    }

    #[Test]
    public function itReturnsCreativeCommonsUrlsForCCLicenses(): void {
        $this->assertEquals('https://creativecommons.org/publicdomain/zero/1.0/', License::CC0->getUrl());
        $this->assertEquals('https://creativecommons.org/licenses/by/4.0/', License::CC_BY->getUrl());
        $this->assertEquals('https://creativecommons.org/licenses/by-sa/4.0/', License::CC_BY_SA->getUrl());
        $this->assertEquals('https://creativecommons.org/licenses/by-nc/4.0/', License::CC_BY_NC->getUrl());
        $this->assertEquals('https://creativecommons.org/licenses/by-nc-sa/4.0/', License::CC_BY_NC_SA->getUrl());
        $this->assertEquals('https://creativecommons.org/licenses/by-nd/4.0/', License::CC_BY_ND->getUrl());
        $this->assertEquals('https://creativecommons.org/licenses/by-nc-nd/4.0/', License::CC_BY_NC_ND->getUrl());
    }

    #[Test]
    public function itIdentifiesCreativeCommonsLicenses(): void {
        $this->assertFalse(License::AllRightsReserved->isCreativeCommons());
        $this->assertFalse(License::PublicDomain->isCreativeCommons());
        $this->assertTrue(License::CC0->isCreativeCommons());
        $this->assertTrue(License::CC_BY->isCreativeCommons());
        $this->assertTrue(License::CC_BY_SA->isCreativeCommons());
        $this->assertTrue(License::CC_BY_NC->isCreativeCommons());
        $this->assertTrue(License::CC_BY_NC_SA->isCreativeCommons());
        $this->assertTrue(License::CC_BY_ND->isCreativeCommons());
        $this->assertTrue(License::CC_BY_NC_ND->isCreativeCommons());
    }

    #[Test]
    public function itConvertsToArray(): void {
        $result = License::CC_BY->toArray();

        $this->assertEquals('cc-by', $result['id']);
        $this->assertEquals('CC BY', $result['title']);
        $this->assertEquals('Attribution', $result['name']);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('isCreativeCommons', $result);
        $this->assertTrue($result['isCreativeCommons']);
    }

    #[Test]
    public function itReturnsAllLicensesAsArray(): void {
        $result = License::allToArray();

        $this->assertCount(9, $result);
        
        foreach ($result as $license) {
            $this->assertArrayHasKey('id', $license);
            $this->assertArrayHasKey('title', $license);
            $this->assertArrayHasKey('name', $license);
            $this->assertArrayHasKey('description', $license);
            $this->assertArrayHasKey('url', $license);
            $this->assertArrayHasKey('isCreativeCommons', $license);
        }
    }

    #[Test]
    public function itHasCorrectEnumValues(): void {
        $this->assertEquals('all-rights-reserved', License::AllRightsReserved->value);
        $this->assertEquals('public-domain', License::PublicDomain->value);
        $this->assertEquals('cc0', License::CC0->value);
        $this->assertEquals('cc-by', License::CC_BY->value);
        $this->assertEquals('cc-by-sa', License::CC_BY_SA->value);
        $this->assertEquals('cc-by-nc', License::CC_BY_NC->value);
        $this->assertEquals('cc-by-nc-sa', License::CC_BY_NC_SA->value);
        $this->assertEquals('cc-by-nd', License::CC_BY_ND->value);
        $this->assertEquals('cc-by-nc-nd', License::CC_BY_NC_ND->value);
    }

    #[Test]
    public function itCanBeParsedFromStringValue(): void {
        $license = License::tryFrom('cc-by');
        
        $this->assertEquals(License::CC_BY, $license);
    }

    #[Test]
    public function itReturnsNullForInvalidStringValue(): void {
        $license = License::tryFrom('invalid-license');
        
        $this->assertSame(null, $license);
    }

    #[Test]
    public function itProvidesAllCases(): void {
        $cases = License::cases();
        
        $this->assertCount(9, $cases);
        $this->assertContains(License::AllRightsReserved, $cases);
        $this->assertContains(License::PublicDomain, $cases);
        $this->assertContains(License::CC0, $cases);
        $this->assertContains(License::CC_BY, $cases);
        $this->assertContains(License::CC_BY_SA, $cases);
        $this->assertContains(License::CC_BY_NC, $cases);
        $this->assertContains(License::CC_BY_NC_SA, $cases);
        $this->assertContains(License::CC_BY_ND, $cases);
        $this->assertContains(License::CC_BY_NC_ND, $cases);
    }

    #[Test]
    public function itReturnsAllEnumValuesAsArray(): void {
        $values = License::values();
        
        $this->assertCount(9, $values);
        $this->assertContains('all-rights-reserved', $values);
        $this->assertContains('public-domain', $values);
        $this->assertContains('cc0', $values);
        $this->assertContains('cc-by', $values);
        $this->assertContains('cc-by-sa', $values);
        $this->assertContains('cc-by-nc', $values);
        $this->assertContains('cc-by-nc-sa', $values);
        $this->assertContains('cc-by-nd', $values);
        $this->assertContains('cc-by-nc-nd', $values);
    }
}
