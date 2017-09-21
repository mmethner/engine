<?php

/**
 * This material may not be reproduced, displayed, modified or distributed
 * without the express prior written permission of the copyright holder.
 *
 * Copyright (c) Mathias Methner
 */
class Trans
{

    /**
     *
     * @var \Engine\Core\Language
     */
    private static $language;

    /**
     *
     * @param \Engine\Core\Language $language
     * @return \Trans
     */
    public function __construct(\Engine\Core\Language $language)
    {
        static::$language = $language;
    }

    /**
     *
     * @param string $string
     * @return string
     */
    public static function late($string)
    {
        return static::$language ? static::$language->translate($string) : '';
    }

    /**
     *
     * @param string $string
     * @param array $replace
     * @return string
     */
    public static function replace($string, array $replace = [])
    {
        if (!static::$language) {
            return '';
        }

        $trans = static::$language->translate($string);
        foreach ($replace as $replacement) {
            $trans = preg_replace('/%s/', $replacement, $trans, 1);
        }

        return $trans;
    }
}