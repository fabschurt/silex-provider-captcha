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

use Gregwar\Captcha\CaptchaBuilderInterface;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
interface CaptchaBuilderFactoryInterface
{
    /**
     * Creates a captcha builder.
     *
     * @param string $phrase (optional) A specific phrase to generate as captcha (if left null, a random phrase should
     *                       be generated)
     *
     * @return CaptchaBuilderInterface
     */
    public function createBuilder($phrase = null);
}
