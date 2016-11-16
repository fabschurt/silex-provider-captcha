<?php

/*
 * This file is part of the fabschurt/silex-provider-captcha package.
 *
 * (c) 2016 Fabien Schurter <fabien@fabschurt.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/vendor/autoload.php';

use Robo\Tasks;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
class RoboFile extends Tasks
{
    public function __construct()
    {
        $this->stopOnFail();
    }

    /**
     * Runs PHPUnit tests.
     */
    public function test()
    {
        $this
            ->taskPHPUnit('./vendor/bin/phpunit')
            ->run()
        ;
    }

    /**
     * Validates PHP syntax.
     */
    public function lint()
    {
        $this
            ->taskExec('find {src,tests} -type f -name "*.php" -exec php -l {} \\;')
            ->run()
        ;
    }

    /**
     * Checks coding standards using PHP CS Fixer.
     *
     * @option $fix Check CS, but also implement fixes (WARNING: source files will be modified)
     */
    public function cs(array $opts = ['fix' => false])
    {
        $this
            ->taskExec(sprintf('./vendor/bin/php-cs-fixer fix -vvv %s', $opts['fix'] ? '' : '--dry-run'))
            ->run()
        ;
    }
}
