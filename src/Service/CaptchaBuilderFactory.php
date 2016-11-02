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

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilderInterface;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class CaptchaBuilderFactory implements CaptchaBuilderFactoryInterface
{
    /**
     * @var PhraseBuilderInterface
     */
    private $phraseBuilder;

    /**
     * @param PhraseBuilderInterface $phraseBuilder
     */
    public function __construct($phraseBuilder)
    {
        $this->phraseBuilder = $phraseBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function createBuilder($phrase = null)
    {
        return new CaptchaBuilder($phrase, $this->phraseBuilder);
    }
}
