<?php

/*
 * This file is part of the fabschurt/silex-provider-captcha package.
 *
 * (c) 2016 Fabien Schurter <fabien@fabschurt.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabSchurt\Silex\Provider\Captcha\Form\Type;

use FabSchurt\Silex\Provider\Captcha\Service\CaptchaInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class CaptchaType extends AbstractType
{
    /**
     * @var Captcha
     */
    private $captcha;

    /**
     * @var string
     */
    private $captchaUrl;

    /**
     * @var int
     */
    private $imageWidth;

    /**
     * @var int
     */
    private $imageHeight;

    /**
     * @var string
     */
    private $cacheBustingParamName;

    /**
     * @param Captcha $captcha
     * @param string  $captchaUrl
     * @param int     $imageWidth            (optional, defaults to *120*)
     * @param int     $imageHeight           (optional, defaults to *32*)
     * @param string  $cacheBustingParamName (optional, defaults to *ts*)
     */
    public function __construct(CaptchaInterface $captcha, $captchaUrl, $imageWidth = 120, $imageHeight = 32, $cacheBustingParamName = 'ts')
    {
        $this->captcha               = $captcha;
        $this->captchaUrl            = $captchaUrl;
        $this->imageWidth            = $imageWidth;
        $this->imageHeight           = $imageHeight;
        $this->cacheBustingParamName = $cacheBustingParamName;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['value']                    = null; // Make sure the input’s value is always empty (the user must type the captcha every time they send the form)
        $view->vars['captcha_url']              = $this->captchaUrl;
        $view->vars['image_width']              = $this->imageWidth;
        $view->vars['image_height']             = $this->imageHeight;
        $view->vars['cache_busting_param_name'] = $this->cacheBustingParamName;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [
                new Constraints\NotBlank(),
                new Constraints\Callback(function ($value, ExecutionContextInterface $context) {
                    if (!$this->captcha->verify($value)) {
                        $context
                            ->buildViolation('Incorrect captcha value.')
                            ->atPath($this->getBlockPrefix())
                            ->addViolation()
                        ;
                    }
                }),
            ],
            'attr' => [
                'class' => 'captcha-input',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }
}
