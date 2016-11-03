<?php

/*
 * This file is part of the fabschurt/silex-provider-captcha package.
 *
 * (c) 2016 Fabien Schurter <fabien@fabschurt.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabSchurt\Silex\Provider\Captcha\Tests\Service;

use Codeception\Specify;
use FabSchurt\Silex\Provider\Captcha\Service\Captcha;
use FabSchurt\Silex\Provider\Captcha\Service\CaptchaBuilderFactory;
use FabSchurt\Silex\Provider\Captcha\Tests\AbstractTestCase;
use Gregwar\Captcha\PhraseBuilder;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class CaptchaTest extends AbstractTestCase
{
    use Specify;

    /**
     * @testdox ->generate()
     */
    public function testGenerate()
    {
        $this->specify('it should generate a JPEG image', function () {
            verify(
                (new \finfo(\FILEINFO_MIME_TYPE))->buffer($this->subject->generate())
            )->same('image/jpeg');
        });
    }

    /**
     * @testdox ->verify()
     */
    public function testVerify()
    {
        $validPhrase = 'Love is all you need';
        $this->subject->generate($validPhrase);
        $this->specify(
            'it should validate an input phrase against the currently stored phrase',
            function () use ($validPhrase) {
                verify($this->subject->verify($validPhrase))->true();
                verify($this->subject->verify('This is not the phrase you are looking for'))->false();
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->subject = new Captcha(
            new CaptchaBuilderFactory(new PhraseBuilder()),
            $this->app['session'],
            'test.captcha.current',
            120,
            32,
            90
        );
    }
}
