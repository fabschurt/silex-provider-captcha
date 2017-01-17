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

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class Captcha implements CaptchaInterface
{
    /**
     * @var CaptchaBuilderFactoryInterface
     */
    private $builderFactory;

    /**
     * @var SessionInterface
     */
    private $storage;

    /**
     * @var string
     */
    private $storageKey;

    /**
     * @var int
     */
    private $defaultImageWidth;

    /**
     * @var int
     */
    private $defaultImageHeight;

    /**
     * @var int
     */
    private $imageQuality;

    /**
     * @param CaptchaBuilderFactoryInterface $builderFactory
     * @param SessionInterface               $storage
     * @param string                         $storageKey
     * @param int                            $defaultImageWidth  (optional)
     * @param int                            $defaultImageHeight (optional)
     * @param int                            $imageQuality       (optional)
     */
    public function __construct(
        CaptchaBuilderFactoryInterface $builderFactory,
        SessionInterface $storage,
        $storageKey,
        $defaultImageWidth = 120,
        $defaultImageHeight = 32,
        $imageQuality = 90
    ) {
        $this->builderFactory     = $builderFactory;
        $this->storage            = $storage;
        $this->storageKey         = $storageKey;
        $this->defaultImageWidth  = $defaultImageWidth;
        $this->defaultImageHeight = $defaultImageHeight;
        $this->imageQuality       = $imageQuality;
    }

    /**
     * {@inheritDoc}
     */
    public function generate($phrase = null, $width = null, $height = null)
    {
        $builder = $this->builderFactory->createBuilder($phrase);
        $builder->build($width ?: $this->defaultImageWidth, $height ?: $this->defaultImageHeight, null, null);
        $this->storage->set($this->storageKey, $builder->getPhrase());

        return $builder->get($this->imageQuality);
    }

    /**
     * {@inheritDoc}
     */
    public function verify($inputPhrase)
    {
        if (is_null($storedPhrase = $this->storage->get($this->storageKey))) {
            return false;
        }

        return $inputPhrase === $storedPhrase;
    }
}
