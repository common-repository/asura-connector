<?php

namespace Dplugins\Asura\Connector\Utils;

use Exception;
use League\Plates\Engine;

/**
 * Plate the native PHP template system.
 * This class is used to run plate on WordPress.
 * 
 * To use this class, you need to include the Medoo library in your project.
 * Run `composer require league/plates` from your plugin root folder.
 * 
 * Plate's object are available on `Template::template()` method.
 * Example usage:
 * - `Template::template()->addFolder('emails', '/path/to/email/templates');`
 * - `echo Template::template()->render('partials/header');`
 * - `echo Template::template()->render('profile', ['name' => 'Jonathan']);`
 * - `<p>Hello <?=$this->e($name)?></p>`
 * 
 * @see http://platesphp.com/ Please read the Plate's documentation for complete usage instruction.
 * 
 * 
 * @package Dplugins\Asura\Connector
 * @since 1.0.0
 * @author dplugins <mail@dplugins.com>
 * @copyright 2021 dplugins
 */
class Template
{
    private static $instances = [];

    public static $templates;

    protected function __construct()
    {
        self::$templates = new Engine(dirname(SUBNAMESPACENAMES_FILE) . '/templates');

        self::$templates->registerFunction('asset', fn ($string) => plugins_url("/dist/{$string}", SUBNAMESPACENAMES_FILE));
    }

    public static function getInstance(): Template
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public static function __callStatic(string $method, array $args)
    {
        return self::getInstance()::$templates->{$method}(...$args);
    }

    public function __get(string $name)
    {
        return self::getInstance()::$templates->{$name};
    }

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a singleton.");
    }

    protected function __clone()
    {
    }

    public static function template(): Engine
    {
        return self::getInstance()::$templates;
    }
}
