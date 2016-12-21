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

use Codeception\Specify;
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
    use Specify;

    public function testServiceRegistration()
    {
        $this->bootApp();

        $this->specify('captcha service should be registered', function () {
            verify($this->app)->hasKey('captcha');
        });

        $this->specify('an exception should be thrown if there are missing dependencies', function () {
            unset($this->app['session']);
            $this->app['captcha'];
        }, [
            'throws' => \InvalidArgumentException::class,
        ]);
    }

    public function testCaptchaImage()
    {
        $this->specify('default route should serve a JPEG image', function () {
            $client = $this->createClient();
            $client->request(Request::METHOD_GET, '/captcha');
            verify($client->getResponse()->isOk())->true();
            verify(
                (new \finfo(\FILEINFO_MIME_TYPE))->buffer($client->getResponse()->getContent())
            )->same('image/jpeg');
        });
    }

    public function testCaptchaValidation()
    {
        $this->bootApp();

        $this->specify('validation should pass if stored phrase equals verified phrase', function () {
            $phrase = 'For the glory of the Emperor';
            $this->app['captcha']->generate($phrase);
            verify($this->app['session']->get($this->app['captcha.storage_key']))->same($phrase);
            verify($this->app['captcha']->verify($phrase))->true();
            verify($this->app['captcha']->verify('Fear denies faith'))->false();
        });

        $this->specify('validation should fail if no phrase has been stored yet', function () {
            verify_not($this->app['session']->get($this->app['captcha.storage_key']));
            verify($this->app['captcha']->verify('Fear denies faith'))->false();
        });

        $this->specify('form validity should depend on captcha field validity', function () {
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
        });
    }

    public function testFormWidget()
    {
        $this->specify('built-in form widget should contain some expected elements', function () {
            $this->bootApp();
            $formCrawler = (new Crawler($this->buildFormHtml()))
                ->filter("#{$this->getTestFormName()} > div")
                ->first()
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
        });

        $this->specify('captcha form input value should always be empty after form submission', function () {
            $this->bootApp();
            $form = $this->buildForm();
            $form->submit(['captcha' => uniqid()]);
            verify_not(
                (new Crawler($this->buildFormHtml($form), $this->getDummyUrl())) // We must pass a dummy URL to the crawler constructor, otherwise form functionality can’t be used
                    ->selectButton('Submit')
                    ->form()
                    ->getPhpValues()[$this->getTestFormName()]['captcha']
            );
        });

        $this->specify('form field label and help text should be translatable', function () {
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
        });
    }

    /**
     * {@inheritDoc}
     */
    public function createApplication()
    {
        $app = new Application(['debug' => true]);
        unset($app['exception_handler']);
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
        $captchaProvider = new CaptchaServiceProvider();
        $app->register($captchaProvider);
        $app->mount('', $captchaProvider);

        return $app;
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->specifyConfig()->shallowClone();
        parent::setUp();
    }

    /**
     * Boots the test app and flushes its controllers (required for tests that
     * don’t mean to test `HttpKernel` responses).
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
