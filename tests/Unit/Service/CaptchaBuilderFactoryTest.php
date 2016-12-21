<?php

/*
 * This file is part of the fabschurt/silex-provider-captcha package.
 *
 * (c) 2016 Fabien Schurter <fabien@fabschurt.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabSchurt\Silex\Provider\Captcha\Tests\Unit\Service;

use Codeception\Specify;
use FabSchurt\Silex\Provider\Captcha\Service\CaptchaBuilderFactory;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class CaptchaBuilderFactoryTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    /**
     * @testdox ->createBuilder()
     */
    public function testCreateBuilder()
    {
        $this->specify('it should return a captcha builder', function () {
            verify($this->subject->createBuilder())->isInstanceOf(CaptchaBuilder::class);
        });
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->specifyConfig()->shallowClone();

        $this->subject = new CaptchaBuilderFactory(
            $this->prophesize(PhraseBuilder::class)->reveal()
        );
    }
}
