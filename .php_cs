<?php

/*
 * This file is part of the fabschurt/silex-provider-captcha package.
 *
 * (c) 2016 Fabien Schurter <fabien@fabschurt.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers([
        '-phpdoc_inline_tag',
        '-phpdoc_to_comment',
        '-psr0',
        '-unalign_double_arrow',
        '-unalign_equals',
        'combine_consecutive_unsets',
        'empty_return',
        'ereg_to_preg',
        'mb_str_functions',
        'newline_after_open_tag',
        'no_useless_else',
        'no_useless_return',
        'ordered_use',
        'php4_constructor',
        'phpdoc_order',
        'short_array_syntax',
        'strict',
        'strict_param',
    ])
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->in('src/')
            ->in('tests/')
    )
;
