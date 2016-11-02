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
     * @param int                            $imageWidth
     * @param int                            $imageHeight
     * @param int                            $imageQuality
     */
    public function __construct(
        CaptchaBuilderFactoryInterface $builderFactory,
        SessionInterface $storage,
        $storageKey,
        $imageWidth,
        $imageHeight,
        $imageQuality
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
        $builder->build($this->imageWidth, $this->imageHeight);
        $this->storage->set($this->storageKey, $builder->getPhrase());

        return $builder->get($this->imageQuality);
    }

    /**
     * {@inheritDoc}
     */
    public function isPhraseValid($inputPhrase)
    {
        return $inputPhrase === $this->storage->get($this->storageKey);
    }
}
