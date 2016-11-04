# Silex Captcha Provider (Silex 2)

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

Just require the provider with Composer file&nbsp;:

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
the Silex application&nbsp;:

```php
use FabSchurt\Silex\Provider\Captcha\CaptchaServiceProvider;
use Silex\Application;

$app = new Application();
$app->register(new CaptchaServiceProvider());
```

As stated earlier, if you want to use the provider’s form functionality, you
will also need to register some core Silex providers&nbsp;:

```php
use FabSchurt\Silex\Provider\Captcha\CaptchaServiceProvider;
use Silex\Application;
use Silex\Provider as SilexProvider;

$app = new Application();
$app->register(new SilexProvider\FormServiceProvider());
$app->register(new SilexProvider\ValidatorServiceProvider());
$app->register(new SilexProvider\LocaleServiceProvider());
$app->register(new SilexProvider\TranslationServiceProvider());
$app->register(new SilexProvider\TwigServiceProvider());
$app->register(new CaptchaServiceProvider());
```

Take good care of registering `CaptchaServiceProvider` last, otherwise it won’t
be able to plug the form functionality in.

## Usage

### Captcha image

You can now request a captcha JPEG image from the URL defined by the `captcha.url`
parameter (bound to the route defined by the `captcha.route_name` parameter).
So, for example, in a Twig template&nbsp;:

```twig
<img src="{{ path(app['captcha.route_name'], {cache_buster: 'now'|date('U')}) }}" alt="" />
```

*Note&nbsp;:* it is advised to use a cache busting mechanism (here&nbsp;: a
`cache_buster` parameter whose value is based on current time), otherwise some
browsers might keep the requested image in cache and display it again even after
page refresh.

When the image is requested, the controller stores the corresponding phrase in
session for later use/comparison. The phrase changes every time the image is
requested.

### Captcha form field

In most cases, you’ll want to use a captcha to secure a form. This is made easy
thanks to the provided `Form\Type\CaptchaType` class. This form type has
preconfigured default validation rules&nbsp;:

* it should not be blank
* its value should match the captcha phrase currently stored in session

There’s a default Twig form view widget provided for the form type (loaded from
`src/Resources/views/captcha_block.html.twig`)&nbsp;; it should cover most use
cases. The widget is automatically registered with the Twig service, so you don’t
have anything special to do for your application to use it.

## Customization

The provider’s components are configurable via the standard Silex parameter
mechanism&nbsp;: see [this page](http://silex.sensiolabs.org/doc/master/providers.html)
for more information.

Here goes a detailed table of the available parameters/services&nbsp;:

| Key                     | Description                                                                                     | Expected type                 | Default value                                                  |
|-------------------------|-------------------------------------------------------------------------------------------------|-------------------------------|-------------------------------|
| `captcha`               | The main captcha service that will generate and verify captcha phrases                          | `Service\CaptchaInterface`    | Instance of `Service\Captcha` |
| `captcha.url`           | The URL from which captcha images are served                                                    | `string` (valid URL required) | `/captcha`                    |
| `captcha.route_name`    | The Silex route name for the image-serving URL                                                  | `string`                      | `captcha`                     |
| `captcha.session_key`   | The key under which the current captcha phrase will be stored in session                        | `string`                      | `captcha.current`             |
| `captcha.image_width`   | The captcha image’s output width (actual width and `width` attribute on the `<img>` element)    | `integer`                     | `120`                         |
| `captcha.image_height`  | The captcha image’s output height (actual height and `height` attribute on the `<img>` element) | `integer`                     | `32`                          |
| `captcha.image_quality` | The captcha image’s compression level (`100` is great but heavier, `0` is crap but lighter)     | `integer`                     | `90`                          |

## License

This software package is licensed under the [MIT License](https://opensource.org/licenses/MIT).
