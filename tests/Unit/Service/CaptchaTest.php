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
use FabSchurt\Silex\Provider\Captcha\Service\Captcha;
use FabSchurt\Silex\Provider\Captcha\Service\CaptchaBuilderFactoryInterface;
use Gregwar\Captcha\CaptchaBuilder;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class CaptchaTest extends \PHPUnit_Framework_TestCase
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
        $this->specify('it should pass validation if the input phrase matches the stored phrase', function () {
            $this->subject->generate($this->validPhrase);
            verify($this->subject->verify($this->validPhrase))->true();
            verify($this->subject->verify('Hope is the first step on the road to disappointment'))->false();
        });

        $this->specify('it should fail validation if not phrase has been stored yet', function () {
            verify($this->subject->verify('Fear denies faith'))->false();
        });
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->validPhrase = 'Blessed is the mind too small for doubt';
        $testStorageKey    = 'test.captcha.current';
        $builder           = \Phake::mock(CaptchaBuilder::class);
        $factoryMock       = \Phake::mock(CaptchaBuilderFactoryInterface::class);
        $sessionMock       = \Phake::mock(Session::class);
        ob_start();
        imagejpeg(imagecreatetruecolor(1, 1));
        $jpegStream = ob_get_clean();
        \Phake::when($builder)->get->thenReturn($jpegStream);
        \Phake::when($factoryMock)->createBuilder->thenReturn($builder);
        \Phake::when($sessionMock)->get($testStorageKey)->thenReturn($this->validPhrase);
        $this->subject = new Captcha(
            $factoryMock,
            $sessionMock,
            $testStorageKey,
            120,
            32,
            90
        );
    }
}
