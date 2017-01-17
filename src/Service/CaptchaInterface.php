<?php

/*
 * This file is part of the fabschurt/silex-provider-captcha package.
 *
 * (c) 2016 Fabien Schurter <fabien@fabschurt.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabSchurt\Silex\Provider\Captcha\Service;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
interface CaptchaInterface
{
    /**
     * Generates a captcha phrase and returns it as a JPEG stream.
     *
     * @param string $phrase (optional) A specific phrase to generate as captcha (if left null, a random phrase should
     *                       be generated)
     * @param int    $width  (optional) The width of the generated image
     * @param int    $height (optional) The height of the generated image
     *
     * @return string The JPEG bytestream
     */
    public function generate($phrase = null, $width = null, $height = null);

    /**
     * Checks if `$inputPhrase` matches the current captcha phrase (concrete
     * implementors will obviously need a way to persist the current captcha
     * value, PHP sessions being the most natural choice).
     *
     * @param string $inputPhrase
     *
     * @return bool
     */
    public function verify($inputPhrase);
}
