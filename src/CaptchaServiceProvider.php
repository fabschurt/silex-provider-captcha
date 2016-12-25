<?php

/*
 * This file is part of the fabschurt/silex-provider-captcha package.
 *
 * (c) 2016 Fabien Schurter <fabien@fabschurt.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabSchurt\Silex\Provider\Captcha;

use FabSchurt\Silex\Provider\Captcha\Form\Type\CaptchaType;
use FabSchurt\Silex\Provider\Captcha\Service\Captcha;
use FabSchurt\Silex\Provider\Captcha\Service\CaptchaBuilderFactory;
use Gregwar\Captcha\PhraseBuilder;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class CaptchaServiceProvider implements ServiceProviderInterface, BootableProviderInterface, ControllerProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Parameters
        $container['captcha.url']           = '/captcha';
        $container['captcha.route_name']    = 'captcha';
        $container['captcha.storage_key']   = 'captcha.current';
        $container['captcha.image_width']   = 120;
        $container['captcha.image_height']  = 32;
        $container['captcha.image_quality'] = 90;

        // Services
        $container['captcha'] = function (Container $container) {
            return new Captcha(
                new CaptchaBuilderFactory(new PhraseBuilder()),
                $container['session'],
                $container['captcha.storage_key'],
                $container['captcha.image_width'],
                $container['captcha.image_height'],
                $container['captcha.image_quality']
            );
        };
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        if (isset($app['form.factory'])) {
            $app->extend('form.types', function (array $formTypes, Application $app) {
                $formTypes[] = new CaptchaType(
                    $app['captcha'],
                    $app['url_generator']->generate(
                        $app['captcha.route_name'],
                        ['ts' => microtime()] // This is used as permanent cache busting
                    ),
                    $app['captcha.image_width'],
                    $app['captcha.image_height']
                );

                return $formTypes;
            });
            if (isset($app['twig'])) {
                $app['twig.path'] = array_merge(
                    [__DIR__.'/Resources/views'],
                    $app['twig.path']
                );
                $app['twig.form.templates'] = array_merge(
                    ['captcha_block.html.twig'],
                    $app['twig.form.templates']
                );
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        $controllers->get($app['captcha.url'], function (Application $app) {
            return new Response(
                $app['captcha']->generate(),
                Response::HTTP_OK,
                ['Content-Type' => 'image/jpeg']
            );
        })->bind($app['captcha.route_name']);

        return $controllers;
    }
}
