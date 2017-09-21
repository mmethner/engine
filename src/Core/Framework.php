<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
namespace Engine\Core;

class Framework
{

    /**
     *
     * @var \Engine\Core\Config
     */
    private $config;

    /**
     *
     * @var \Engine\Core\Router
     */
    private $router;

    /**
     *
     * @return void
     */
    public static function run()
    {
        $framework = static::init();
        Controller::dispatch($framework->router);
    }

    /**
     *
     * @return Framework
     */
    public static function init()
    {
        Debug::init();

        $framework = new self();
        $framework->config = new Config(ENGINE_APP_ROOT);
        $framework->router = $framework->config->getRouter();

        $framework->setup();

        return $framework;
    }

    /**
     *
     * @return void
     */
    private function setup()
    {
        if (!Config::get('debugbar', 'enable')) {
            Debug::disable();
        }

        if (!Config::get('database', 'enable')) {
            Database::disable();
        }
    }
}