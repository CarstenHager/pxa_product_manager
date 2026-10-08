<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Tests\Unit\Service\Url;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pixelant\PxaProductManager\Service\Url\UrlBuilderService;
use Pixelant\PxaProductManager\Tests\Utility\TestsUtility;
use TYPO3\CMS\Frontend\Typolink\LinkFactory;

class UrlBuilderServiceTest extends UnitTestCase
{
    /**
     * @var UrlBuilderService
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new UrlBuilderService($this->createMock(LinkFactory::class));
    }

    /**
     * @test
     */
    public function canSetAbsoluteUrl(): void
    {
        $this->subject->absolute(true);

        self::assertTrue(TestsUtility::getProtectedVarValue($this->subject, 'absolute'));
    }
}
