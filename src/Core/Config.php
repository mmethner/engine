<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */

namespace Engine\Core;

use Spyc;

class Config
{

    /**
     *
     * @var array
     */
    private static $config = [];

    /**
     *
     * @var string
     */
    private $root = "";

    /**
     *
     * @var Router
     */
    private $router;

    /**
     * @param string $root must not end with a /
     */
    public function __construct(string $root = ".")
    {
        $this->root = $root;
        $this->router = new Router();

        $this->loadConfig($this->root . '/config/config.yml', $this->root . '/config/.config.yml');
        $this->loadRouter($this->root . '/config/routes.yml');

        Debug::message(static::$config, 'config');
    }

    /**
     *
     * @param string $ymlFile
     * @param string $localYmlFile
     *            optional local overwrite
     * @return void
     */
    private function loadConfig($ymlFile, $localYmlFile)
    {
        if (file_exists($ymlFile)) {
            static::$config = Spyc::YAMLLoad($ymlFile);
        }

        static::$config['core']['url'] = $this->router->serverHost();

        if (file_exists($localYmlFile)) {
            foreach (Spyc::YAMLLoad($localYmlFile) as $index => $data) {
                foreach ($data as $key => $value) {
                    if (isset(static::$config[$index][$key])) {
                        static::$config[$index][$key] = $value;
                    }
                }
            }
        }
    }

    /**
     *
     * @param string $ymlFile
     * @return void
     */
    private function loadRouter($ymlFile)
    {
        // @todo find a solution for that
        //$this->router->map('/', 'Root::Controller', 'rootAction', 'Root::Controller::rootAction');

        $files = Spyc::YAMLLoad($ymlFile);
        foreach ($files as $component => $config) {
            $componentConfig = Spyc::YAMLLoad($this->root."/src" . $config['resource']);

            if(!isset($componentConfig['routes'])) {
                continue;
            }

            foreach ($componentConfig['routes'] as $name => $route) {
                $this->router->map($config['prefix'] . $route[0], $route[1], $route[2], $route[1] . '::' . $name);
            }
        }
    }

    /**
     *
     * @return string
     */
    public static function protocol()
    {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://';
    }

    /**
     * @return bool
     */
    public static function isProd()
    {
        return static::get('core', 'environment') == 'prod';
    }

    /**
     *
     * @param string $parent
     * @param string $key
     * @return mixed
     */
    public static function get($parent, $key)
    {
        if (array_key_exists($parent, static::$config) && array_key_exists($key, static::$config[$parent])) {
            return static::$config[$parent][$key];
        }
        return '';
    }

    /**
     *
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }
}