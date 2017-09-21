<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
namespace Engine\Core;

use Spot\Locator;

class Database extends Locator
{

    /**
     *
     * @var bool
     */
    private static $enabled = true;

    /**
     *
     * @var array
     */
    private static $pool = [];

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
     * @param string $dbName
     * @throws \RuntimeException
     * @return \Spot\Locator
     */
    public static function get($dbName)
    {
        if (!isset(static::$pool[$dbName])) {
            $db = static::init($dbName);
            if (!is_null($db) && $db instanceof Database) {
                static::$pool[$dbName] = $db;
            } else {
                throw new \RuntimeException('Unable to connect to database');
            }
        }

        return static::$pool[$dbName];
    }

    /**
     *
     * @param string $dbName
     * @return null|\Spot\Locator
     */
    private static function init($dbName)
    {
        if ($dbName == '') {
            return null;
        }

        if (!static::$enabled) {
            return null;
        }

        $spotConfig = new \Spot\Config();
        /** @noinspection PhpParamsInspection */
        $spotConfig->addConnection('mysql', [
            'dbname' => $dbName,
            'user' => Config::get('database', 'db-user'),
            'password' => Config::get('database', 'db-pw'),
            'host' => Config::get('database', 'db-host'),
            'driver' => Config::get('database', 'db-driver'),
            'charset' => 'utf8'
        ]);

        return new static($spotConfig);
    }
}
