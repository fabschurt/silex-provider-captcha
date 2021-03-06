# Silex Captcha Provider (Silex 2)

[![Build Status](https://travis-ci.org/fabschurt/silex-provider-captcha.svg?branch=master)](https://travis-ci.org/fabschurt/silex-provider-captcha)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/cdf132338e264ea29d66ca8bed0ce865)](https://www.codacy.com/app/fabschurt/silex-provider-captcha)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/ac09abe9-db9c-42e3-a06b-cfc1c0d8330d/mini.png)](https://insight.sensiolabs.com/projects/ac09abe9-db9c-42e3-a06b-cfc1c0d8330d)

This provider will give you access to a set of tools for easily working with
captcha validation (especially in forms), namely&nbsp;:

* a `captcha` service for generating and verifying captchas
* a route/controller which serves captcha images
* a preconfigured `CaptchaType` form type
* a Twig widget for preconfigured rendering within an HTML form

Everything will be automatically plugged in when you register the provider,
given that the required dependencies are installed/registered (see below).

The default captcha engine used by this provider is
[Gregwar/Captcha](https://github.com/Gregwar/Captcha).

*Note&nbsp;:* all the class names in this document (with the exception of PHP
code blocks) are relative to the base `FabSchurt\Silex\Provider\Captcha`
namespace.

## Requirements

* [PHP](https://secure.php.net/) 5.6+
* [Composer](https://getcomposer.org/)
* [Silex](http://silex.sensiolabs.org/) 2

## Installation

Just require the provider with Composer&nbsp;:

```bash
composer require fabschurt/silex-provider-captcha:^1.0@dev
```

If you want to use the provider’s full functionality (form type and widget), you
will need to register some core Silex providers, and before that, you will have
to require their dependencies with Composer too&nbsp;:

```bash
composer require symfony/config:^2.8|^3.0
composer require symfony/form:^2.8|^3.0
composer require symfony/translation:^2.8|^3.0
composer require symfony/twig-bridge:^2.8|^3.0
composer require symfony/validator:^2.8|^3.0
composer require twig/twig
```

Once the required dependencies are installed, just register the provider with
the Silex application, as well as the `SessionServiceProvider`, and mount the
provider to gain access to the captcha route&nbsp;:

```php
use FabSchurt\Silex\Provider\Captcha\CaptchaServiceProvider;
use Silex\Application;
use Silex\Provider;

$app             = new Application();
$captchaProvider = new CaptchaServiceProvider();
$app->register(new Provider\SessionServiceProvider());
$app->register($captchaProvider);
$app->mount('', $captchaProvider);
```

As stated earlier, if you want to use the provider’s form integration, you will
also need to register some core Silex providers&nbsp;:

```php
use FabSchurt\Silex\Provider\Captcha\CaptchaServiceProvider;
use Silex\Application;
use Silex\Provider;

$app             = new Application();
$captchaProvider = new CaptchaServiceProvider();
$app->register(new Provider\SessionServiceProvider());
$app->register(new Provider\FormServiceProvider());
$app->register(new Provider\ValidatorServiceProvider());
$app->register(new Provider\LocaleServiceProvider());
$app->register(new Provider\TranslationServiceProvider());
$app->register(new Provider\TwigServiceProvider());
$app->register($captchaProvider);
$app->mount('', $captchaProvider);
```

**Important&nbsp;:** you MUST register the captcha provider after all the
other depended-on providers, otherwise the form integration won’t work.

## Usage

### Captcha service

Use the `captcha` service to generate and verify catpchas&nbsp;:

```php
$app['captcha']->generate(); // This will generate a random captcha phrase, store it in session and return a matching JPEG bytestream
$app['captcha']->verify($someInput); // This will return `true` if the input value matches the phrase stored in session, `false` otherwise
```

If needed, you can also define the phrase to generate yourself&nbsp;:

```php
$app['captcha']->generate('a23rt7');
```

### Captcha image

You can request a captcha JPEG image from the URL defined by the `captcha.url`
parameter (bound to the route defined by the `captcha.route_name` parameter).
So, for example, in a Twig template&nbsp;:

```twig
<img src="{{ path(app['captcha.route_name'], {cache_buster: 'now'|date('U')}) }}" alt="" />
```

*Note&nbsp;:* it is recommended to use a cache busting mechanism (here&nbsp;: a
`cache_buster` parameter whose value is based on current time), otherwise some
browsers might keep the requested image in cache and display it again even after
page refresh.

When the image is requested, a random phrase will be generated and stored in
session for later use/comparison. The phrase is generated again every time the
image is requested.

You can also add query parameters to the generated URL to override the default
output width and height of the generated image&nbsp;:

```twig
<img src="{{ path(app['captcha.route_name'], {w: 9000, h: 42, cache_buster: 'now'|date('U')}) }}" alt="" />
```

You can customize the names of the query parameters via the `captcha.width_param_name`
and `captcha.height_param_name` application parameters.

### Captcha form field

In most cases, you’ll want to use a captcha to secure a form. This is made easy
thanks to the provided `Form\Type\CaptchaType` class&nbsp;:

```php
use FabSchurt\Silex\Provider\Captcha\Form\Type\CaptchaType;
use Symfony\Component\Form\Extension\Core\Type;

$form = $this->app['form.factory']
    ->createBuilder()
    ->add('name',    Type\Text::class)
    ->add('message', Type\Text::class)
    ->add('captcha', CaptchaType::class)
    ->add('submit',  Type\SubmitType::class)
    ->getForm()
;
```

This form type has preconfigured default validation rules&nbsp;:

* its value should not be blank
* its value should match the captcha phrase currently stored in session

There’s a default Twig form view widget provided for the form type (loaded from
`src/Resources/views/captcha_block.html.twig`)&nbsp;; it should cover most use
cases, and has built-in JavaScript for captcha image refreshing. The widget is
automatically registered with the Twig service, so you don’t have anything
special to do for your application to use it.

## Customization

The provider’s components are configurable via the standard Silex parameter
mechanism&nbsp;: see [this page](http://silex.sensiolabs.org/doc/master/providers.html)
for more information.

Here goes a detailed table of the available parameters/services&nbsp;:

| Key                                | Description                                                                                                           | Expected type                 | Default value                 |
|------------------------------------|-----------------------------------------------------------------------------------------------------------------------|-------------------------------|-------------------------------|
| `captcha`                          | The main captcha service that will generate and verify captcha phrases                                                | `Service\CaptchaInterface`    | Instance of `Service\Captcha` |
| `captcha.url`                      | The URL from which the captcha image is served                                                                        | `string` (valid URL required) | */captcha*                    |
| `captcha.route_name`               | The Silex route name for the image-serving URL                                                                        | `string`                      | *captcha*                     |
| `captcha.storage_key`              | The key under which the current captcha phrase will be stored in session                                              | `string`                      | *captcha.current*             |
| `captcha.image_width`              | The captcha image’s default output width (actual width and `width` attribute of the form widget’s `<img>` element)    | `integer`                     | *120*                         |
| `captcha.image_height`             | The captcha image’s default output height (actual height and `height` attribute of the form widget’s `<img>` element) | `integer`                     | *32*                          |
| `captcha.image_quality`            | The captcha image’s compression level (`100` is great but heavier, `0` is crap but lighter)                           | `integer`                     | *90*                          |
| `captcha.width_param_name`         | The name of the query string parameter used to customize the served image’s width                                     | `string`                      | *w*                           |
| `captcha.height_param_name`        | The name of the query string parameter used to customize the served image’s height                                    | `string`                      | *h*                           |
| `captcha.cache_busting_param_name` | The name of the query string parameter used to cache-bust the served image                                            | `string`                      | *ts*                          |

## License

This software package is licensed under the [MIT License](https://opensource.org/licenses/MIT).
