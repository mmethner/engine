<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
namespace Engine\Core;

use Engine\Core\Tools\Path;
use Trans;

class Language
{

    /**
     * switch to enable a global shortcut translation object
     *
     * @var bool
     */
    protected $enableGlobalTrans = true;

    /**
     *
     * @var string e.g. 'en'
     */
    private $default = 'en';

    /**
     *
     * @var string e.g. 'en'
     */
    private $forced = '';

    /**
     *
     * @var string e.g. 'en'
     */
    private $applied = '';

    /**
     *
     * @var array
     */
    private $locales = [];

    /**
     * Normally, if you use the getUserLangs-method this array will be filled in like this:
     * 1.
     * Forced language
     * 2. Fallback language
     *
     * @var array
     */
    private $userLangs = [];

    /**
     * This is the path for the language files are.
     * You must use the '{LANGUAGE}' placeholder for the language
     * or the script wont find any language files.
     *
     * @var string e.g. /src/Root/Resources/Language/lang_{LANGUAGE}.php
     */
    private $filePath = '';

    /**
     *
     * @var string
     */
    private $langFilePath = null;

    /**
     *
     * @var array
     */
    private $translations = [];

    /**
     *
     * @param string $filePath
     *            You must use the '{LANGUAGE}' placeholder for the language.
     * @param array $locales
     *            available locales
     * @param string $default
     *            fallback locale
     * @param string $forced
     *            forced locale
     */
    public function __construct($filePath, array $locales, $default, $forced = '')
    {
        $this->filePath = $filePath;
        $this->locales = $locales;
        $this->forced = $forced;

        $this->default = $this->fallback($default);
        $this->applied = $forced == '' ? $this->default : $forced;

        $this->init();

        if ($this->enableGlobalTrans) {
            new Trans($this);
        }
    }

    /**
     * @param string $default
     * @return string
     */
    private function fallback($default)
    {
        $langs = $this->detect();
        if (!empty($langs) && in_array($langs[0], $this->locales)) {
            return $langs[0];
        } else {
            return $default;
        }
    }

    /**
     *
     * @return array
     */
    private function detect()
    {
        $langs = [];
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $part) {
                $langs[] = strtolower(substr($part, 0, 2));
            }
        }
        return $langs;
    }

    /**
     *
     * @return void
     * @throws \RuntimeException
     * @throws \Exception
     */
    private function init()
    {
        // only if $this->forced is empty the framework has to decide which lang to select
        if ('' == $this->forced) {
            $this->userLangs = $this->getUserLangs();
        } else {
            $this->userLangs = [
                $this->forced
            ];
        }

        foreach ($this->userLangs as $langcode) {
            $this->langFilePath = str_replace('{LANGUAGE}', $langcode, $this->filePath);
            if (file_exists($this->langFilePath)) {
                $this->applied = $langcode;
                break;
            }
        }

        if (empty($this->applied)) {
            Debug::message('no language file was found', 'warning');
        }

        $this->mergeTranslation($this->langFilePath);
    }

    /**
     *
     * @return array
     */
    private function getUserLangs()
    {
        $userLangs = [];

        // Highest priority: forced language
        if ($this->forced && in_array($this->forced, $this->locales)) {
            $userLangs[] = $this->forced;
        }

        // Lowest priority: fallback
        $userLangs[] = $this->default;

        $userLangs = array_unique($userLangs);

        foreach ($userLangs as $key => $value) {
            $userLangs[$key] = preg_replace('/[^a-zA-Z0-9_-]/', '', $value);
        }

        return $userLangs;
    }

    /**
     *
     * @param string $filePath
     * @return void
     */
    private function mergeTranslation($filePath)
    {
        if (!file_exists($filePath)) {
            return;
        }

        $trans = [];
        $extend = null;

        /** @noinspection PhpIncludeInspection */
        include($filePath);

        if (is_array($trans)) {
            $this->translations = array_merge($trans, $this->translations);

            if (!is_null($extend)) {
                $this->mergeTranslation(Path::language($extend));
            }
        }
    }

    /**
     * @param string $locale e.g. 'en'
     * @return string
     */
    public static function localeToISO($locale)
    {
        switch ($locale) {
            case 'de':
                return 'de-DE';
            default;
            case 'en':
                return 'en-GB';
        }
    }

    /**
     *
     * @return string
     */
    public function getAppliedLang()
    {
        return $this->applied;
    }

    /**
     *
     * @param string $key
     * @return string
     */
    public function translate($key)
    {
        if (array_key_exists($key, $this->translations)) {
            return $this->translations[$key];
        } else {
            return $key;
        }
    }
}
