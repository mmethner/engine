<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
namespace Engine\Core;

abstract class Error
{

    /**
     *
     * @param mixed $message
     * @return void
     */
    public static function log($message): void
    {
        if (is_array($message) || is_object($message)) {
            error_log('engine-core: ');
            error_log(print_r($message, 1));
        } else {
            error_log('engine-core: '.$message);
        }
    }

}