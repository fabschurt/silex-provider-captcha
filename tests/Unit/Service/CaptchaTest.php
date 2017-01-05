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
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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

        $this->specify('it should store the generated phrase in session', function () {
            $this->subject->generate($this->validPhrase);
            $this->session->set($this->testStorageKey, $this->validPhrase)->shouldHaveBeenCalled();
        });
    }

    /**
     * @testdox ->verify()
     */
    public function testVerify()
    {
        $this->specify('it should pass validation if the input phrase matches the stored phrase', function () {
            verify($this->subject->verify($this->validPhrase))->true();
        });

        $this->specify('it should fail validation it the input phrase doesn’t match the stored phrase', function () {
            verify($this->subject->verify('Hope is the first step on the road to disappointment'))->false();
        });
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->specifyConfig()->shallowClone();

        $this->validPhrase    = 'Blessed is the mind too small for doubt';
        $this->testStorageKey = 'test.captcha.current';

        $this->session = $this->prophesize(SessionInterface::class);
        $this->session->set(Argument::cetera())->willReturn(null);
        $this->session->get($this->testStorageKey)->willReturn($this->validPhrase);

        ob_start();
        imagejpeg(imagecreatetruecolor(1, 1));
        $jpegStream = ob_get_clean();
        $builder = $this->prophesize(CaptchaBuilder::class);
        $builder->build(Argument::cetera())->willReturn(null);
        $builder->get(Argument::any())->willReturn($jpegStream);
        $builder->getPhrase()->willReturn($this->validPhrase);

        $builderFactory = $this->prophesize(CaptchaBuilderFactoryInterface::class);
        $builderFactory->createBuilder(Argument::any())->willReturn($builder->reveal());

        $this->subject = new Captcha($builderFactory->reveal(), $this->session->reveal(), $this->testStorageKey);
    }
}
