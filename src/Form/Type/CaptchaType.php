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

use FabSchurt\Silex\Provider\Captcha\Service\Captcha;
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
     * @param Captcha $captcha
     */
    public function __construct(Captcha $captcha)
    {
        $this->captcha = $captcha;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // Make sure the input’s value is always empty (the user must type the captcha every time they send the form)
        $view->vars['value'] = null;
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
                            ->buildViolation('Invalid captcha value.')
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
