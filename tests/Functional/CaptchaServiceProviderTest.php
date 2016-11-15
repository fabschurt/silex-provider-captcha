<?php

/*
 * This file is part of the fabschurt/silex-provider-captcha package.
 *
 * (c) 2016 Fabien Schurter <fabien@fabschurt.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabSchurt\Silex\Provider\Captcha\Tests\Functional;

use FabSchurt\Silex\Provider\Captcha\CaptchaServiceProvider;
use FabSchurt\Silex\Provider\Captcha\Form\Type\CaptchaType;
use Silex\Application;
use Silex\Provider;
use Silex\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class CaptchaServiceProviderTest extends WebTestCase
{
    public function testServiceIsRegistered()
    {
        verify($this->app)->hasKey('captcha');
    }

    public function testExceptionIsThrownIfDependedOnProvidersAreNotRegistered()
    {
        $this->expectException(\InvalidArgumentException::class);
        unset($this->app['session']);
        $this->app['captcha'];
    }

    public function testCaptchaValidation()
    {
        $phrase = 'For the glory of the Emperor';
        $this->app['captcha']->generate($phrase);
        verify($this->app['captcha']->verify($phrase))->true();
    }

    public function testDefaultRouteServesImage()
    {
        $client = $this->createClient();
        $client->request(Request::METHOD_GET, '/captcha');
        verify($client->getResponse()->isOk())->true();
        verify(
            (new \finfo(\FILEINFO_MIME_TYPE))->buffer(
                $client->getResponse()->getContent()
            )
        )->same('image/jpeg');
    }

    public function testRenderedFormWidgetContainsExpectedElements()
    {
        $this->bootApp();
        $formCrawler = (new Crawler($this->buildFormHtml()))
            ->filter("#{$this->getTestFormName()} > div")
            ->eq(0)
        ;
        verify(
            $formCrawler
                ->filter(
                    '.captcha-wrapper > .captcha-refresh-btn,'.
                    '.captcha-wrapper > img.captcha-img,'.
                    'input[type="text"].captcha-input'
                )
                ->count()
        )->same(3);
    }

    public function testViewFormInputValueIsAlwaysEmpty()
    {
        $this->bootApp();
        $form = $this->buildForm();
        $form->submit(['captcha' => uniqid()]);
        verify(
            (new Crawler($this->buildFormHtml($form), $this->getDummyUrl()))
                ->selectButton('Submit')
                ->form()
                ->getPhpValues()[$this->getTestFormName()]['captcha']
        )->isEmpty();
    }

    public function testFormValidation()
    {
        $this->bootApp();
        $validPhrase = 'Knowledge is power, guard it well';
        $this->app['captcha']->generate($validPhrase);
        $cycle = [
            $validPhrase                 => true,
            'Heresy grows from idleness' => false,
            ''                           => false,
        ];
        foreach ($cycle as $phrase => $expectedValidity) {
            $form = $this->buildForm();
            $form->submit(['captcha' => $phrase]);
            verify($form->isValid())->same($expectedValidity);
        }
    }

    public function testProviderComponentsAreTranslatable()
    {
        $this->app['locale'] = 'fr';
        $this->app['translator.domains'] = [
            'validators' => [
                'fr' => [
                    'Invalid captcha value.' => 'Captcha invalide.',
                ],
            ],
            'captcha' => [
                'fr' => [
                    'Load a new image' => 'Charger une nouvelle image',
                ],
            ],
        ];
        $this->bootApp();
        $this->app['captcha']->generate('Fear not the psyker');
        $form = $this->buildForm();
        $form->submit(['captcha' => 'There is no such thing as innocence, only degrees of guilt']);
        $formHtml = $this->buildFormHtml($form);
        verify($formHtml)->contains('Captcha invalide.');
        verify($formHtml)->contains('Charger une nouvelle image');
    }

    /**
     * {@inheritDoc}
     */
    public function createApplication()
    {
        $app = new Application(['debug' => true]);
        $app->register(new Provider\SessionServiceProvider(), ['session.test' => true]);
        $app->register(new Provider\FormServiceProvider());
        $app->register(new Provider\ValidatorServiceProvider());
        $app->register(new Provider\LocaleServiceProvider());
        $app->register(new Provider\TranslationServiceProvider());
        $app->register(new Provider\TwigServiceProvider(), [
            'twig.templates' => [
                $this->getTestFormName() => '{{ form(form) }}',
            ],
        ]);
        $app->register(new CaptchaServiceProvider());
        unset($app['exception_handler']);

        return $app;
    }

    /**
     * Boots the test app and flushes its controllers.
     */
    private function bootApp()
    {
        $this->app->boot();
        $this->app->flush();
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
        $form = $form ?: $this->buildForm();

        return $this->app['twig']->render($this->getTestFormName(), [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Returns a constant test form identifier.
     *
     * @return string
     */
    private function getTestFormName()
    {
        return 'test_form';
    }

    /**
     * Returns a fake (but well-formed) URL.
     *
     * @return string
     */
    private function getDummyUrl()
    {
        return 'http://dev/null';
    }
}
