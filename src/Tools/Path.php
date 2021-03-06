<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */

namespace Engine\Tools;

abstract class Path
{

    /**
     *
     * @param string $template
     * @param bool $url
     * @return array
     * @throws \RuntimeException
     */
    public static function separate(string $template, bool $url = false): array
    {
        if (!$url && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $template = str_replace('/', '\\', $template);
        }

        if (preg_match('/[a-zA-Z]+::[a-zA-Z]+/', $template)) {
            list ($component, $skeleton) = explode('::', $template);
            return [
                $component,
                $skeleton
            ];
        } else {
            throw new \RuntimeException('Wrong template definition');
        }
    }

    /**
     * creates the fully qualified include string for a language resource file
     * @param string $template
     * @return string
     */
    public static function language(string $template): string
    {
        list ($component, $file) = static::separate($template);
        $elements = [
            ENGINE_APP_ROOT,
            'src',
            $component,
            'Resources',
            'Language',
            $file
        ];
        return implode(DIRECTORY_SEPARATOR, $elements);
    }

    /**
     *
     * @param string $template
     * @return string
     */
    public static function snippet(string $template): string
    {
        list ($component, $file) = static::separate($template);
        $elements = [
            ENGINE_APP_ROOT,
            'src',
            $component,
            'Resources',
            'Snippets',
            $file
        ];
        return implode(DIRECTORY_SEPARATOR, $elements);
    }
}