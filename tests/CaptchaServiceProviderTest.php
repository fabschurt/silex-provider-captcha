<?php

/*
 * This file is part of the fabschurt/silex-provider-captcha package.
 *
 * (c) 2016 Fabien Schurter <fabien@fabschurt.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabSchurt\Silex\Provider\Captcha\Tests;

use FabSchurt\Silex\Provider\Captcha\Form\Type\CaptchaType;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class CaptchaServiceProviderTest extends AbstractTestCase
{
    public function testServiceIsRegistered()
    {
        verify($this->app)->hasKey('captcha');
    }

    public function testDefaultRouteServesImage()
    {
        $this->client->request(Request::METHOD_GET, '/captcha');
        verify_that($this->client->getResponse()->isOk());
        verify(
            (new \finfo(\FILEINFO_MIME_TYPE))->buffer(
                $this->client->getResponse()->getContent()
            )
        )->same('image/jpeg');
    }

    public function testFormWidgetOutput()
    {
        $captchaFormRow = (new Crawler($this->buildFormHtml()))
            ->filter("#{$this->getTestFormName()} > div")
            ->eq(0)
        ;
        verify(
            $captchaFormRow
                ->filter(
                    '.captcha-wrapper > .captcha-refresh-btn,'.
                    '.captcha-wrapper > .img.captcha-img,'.
                    'input[type="text"].captcha-input'
                )
                ->count()
        )->same(3);
    }

    public function testViewFormInputValueIsAlwaysEmpty()
    {
        $form = $this->buildForm();
        $form->submit([
            $this->getTestFormName() => [
                'captcha' => uniqid(),
            ],
        ]);
        verify(
            (new Crawler($this->buildFormHtml($form), 'http://dev/null'))
                ->selectButton('Submit')
                ->form()
                ->getPhpValues()[$this->getTestFormName()]['captcha']
        )->isEmpty();
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setup();
        $this->client = $this->createClient();
    }

    /**
     * Returns a form object containing only the captcha field.
     *
     * @return FormInterface
     */
    private function buildForm()
    {
        return $this->app['form.factory']
            ->createNamedBuilder($this->getTestFormName())
            ->add('captcha', CaptchaType::class)
            ->add('submit', Type\SubmitType::class)
            ->getForm()
        ;
    }

    /**
     * Returns an HTML form containing only the captcha field.
     *
     * @param FormInterface $form (optional) A pre-defined form object to be rendered (if left null, a fresh form will
     *                            be built internally)
     *
     * @return string
     */
    private function buildFormHtml(FormInterface $form = null)
    {
        return $this->app['twig']->render($this->getTestFormName(), [
            'form' => ($form ?: $this->buildForm())->createView(),
        ]);
    }
}
