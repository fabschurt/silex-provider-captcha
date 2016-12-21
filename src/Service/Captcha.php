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
    private $imageWidth;

    /**
     * @var int
     */
    private $imageHeight;

    /**
     * @var int
     */
    private $imageQuality;

    /**
     * @param CaptchaBuilderFactoryInterface $builderFactory
     * @param SessionInterface               $storage
     * @param string                         $storageKey
     * @param int                            $imageWidth     (optional) Default: 120
     * @param int                            $imageHeight    (optional) Default: 32
     * @param int                            $imageQuality   (optional) Default: 90
     */
    public function __construct(
        CaptchaBuilderFactoryInterface $builderFactory,
        SessionInterface $storage,
        $storageKey,
        $imageWidth = 120,
        $imageHeight = 32,
        $imageQuality = 90
    ) {
        $this->builderFactory = $builderFactory;
        $this->storage        = $storage;
        $this->storageKey     = $storageKey;
        $this->imageWidth     = $imageWidth;
        $this->imageHeight    = $imageHeight;
        $this->imageQuality   = $imageQuality;
    }

    /**
     * {@inheritDoc}
     */
    public function generate($phrase = null)
    {
        $builder = $this->builderFactory->createBuilder($phrase);
        $builder->build($this->imageWidth, $this->imageHeight, null, null);
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
