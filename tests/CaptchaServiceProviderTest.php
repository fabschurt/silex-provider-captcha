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

use FabSchurt\Silex\Provider\Captcha\CaptchaServiceProvider;
use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class CaptchaServiceProviderTest extends WebTestCase
{
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

    /**
     * {@inheritDoc}
     */
    public function createApplication()
    {
        $app = new Application();
        $app['debug']        = true;
        $app['session.test'] = true;
        $app->register(new SessionServiceProvider());
        $app->register(new CaptchaServiceProvider());
        unset($app['exception_handler']);

        return $app;
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setup();
        $this->client = $this->createClient();
    }
}
