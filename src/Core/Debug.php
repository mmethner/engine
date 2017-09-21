<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
namespace Engine\Core;

use DebugBar\StandardDebugBar;

class Debug
{

    /**
     *
     * @var \DebugBar\StandardDebugBar
     */
    private static $debugbar;

    /**
     *
     * @var bool
     */
    private static $enabled = true;

    /**
     *
     * @return void
     */
    public static function init()
    {
        static::$debugbar = new StandardDebugBar();
    }

    /**
     *
     * @return void
     */
    public static function disable()
    {
        static::$enabled = false;
    }

    /**
     *
     * @param mixed $msg
     * @param string $key
     * @return void
     */
    public static function message($msg, $key = 'info')
    {
        if (static::$enabled && static::$debugbar['messages']) {
            static::$debugbar['messages']->addMessage($msg, $key);
        }
    }

    /**
     *
     * @return string
     */
    public static function header()
    {
        if (!static::$enabled) {
            return '';
        }

        return static::bar()->getJavascriptRenderer()
            ->setBaseUrl(Config::get('debugbar', 'url'))
            ->renderHead();
    }

    /**
     *
     * @return \DebugBar\StandardDebugBar
     */
    public static function bar()
    {
        return static::$debugbar;
    }

    /**
     *
     * @return string
     */
    public static function footer()
    {
        if (!static::$enabled) {
            return '';
        }

        return static::bar()->getJavascriptRenderer()
            ->setBaseUrl(Config::get('debugbar', 'url'))
            ->render();
    }

    /**
     *
     * @param mixed $data
     * @return void
     */
    public static function dbo($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}