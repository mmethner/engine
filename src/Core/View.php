<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */

namespace Engine\Core;

use Engine\Tools\Path;

class View
{

    /**
     *
     * @var Language
     */
    protected $language;

    /**
     *
     * @var Router
     */
    protected $router;

    /**
     *
     * @var array
     */
    protected $structure = [];

    /**
     *
     * @var array
     */
    protected $snippets = [];

    /**
     *
     * @var array
     */
    protected $header = [];

    /**
     *
     * @var array
     */
    protected $data = [];

    /**
     *
     * @var string
     */
    protected $content = '';

    /**
     *
     * @param string $component
     * @param Router $router
     * @param string $locale
     */
    public function __construct($component, Router $router, $locale = '')
    {
        $lang = Path::language($component . '::lang_{LANGUAGE}.php');
        $this->language = new Language(
            $lang,
            Config::get('locale', 'available'),
            Config::get('locale', 'default'),
            $locale
        );

        $this->router = $router;
    }

    /**
     * hides undefined property notice, because view assigns dynamic content as property
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            Error::log('undefined property ' . $name);
            return '';
        }
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     * @param bool $force
     * @throws \RuntimeException
     */
    public function assign($key, $value, $force = false)
    {
        if (!$force && (property_exists($this, $key) || array_key_exists($key, $this->data))) {
            throw new \RuntimeException('Tried to assign content twice: [' . $key . ']');
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     *
     * @param string $header
     * @return void
     */
    public function header($header)
    {
        $this->header[] = $header;
    }

    /**
     *
     * @param string $template
     *            e.g. Framework::skeleton-content.phtml
     * @return void
     */
    public function snippet($template)
    {
        $path = Path::snippet($template);
        if (file_exists($path)) {
            $this->snippets[] = $path;
        }
    }

    /**
     *
     * @param string $template
     *            e.g. Framework::skeleton-content.phtml
     * @return void
     */
    public function extend($template)
    {
        $backtrace = debug_backtrace();
        $snippet = $backtrace[0]['file'];

        $path = Path::snippet($template);
        if (file_exists($path)) {
            $this->structure[$snippet] = $path;
        }
    }

    /** @noinspection PhpUnusedParameterInspection *
     * @param string $template
     * @param array $content
     */
    public function renderSnippet(string $template, array $content = [])
    {
        if (!$template) {
            return;
        }

        $path = Path::snippet($template);

        ob_start();

        if (file_exists($path)) {
            /** @noinspection PhpIncludeInspection */
            include($path);
        }

        ob_end_flush();
    }

    /**
     *
     * @return void
     */
    public function render()
    {
        foreach ($this->header as $header) {
            header($header);
        }

        ob_start();
        foreach ($this->snippets as $______snippet______) {
            /** @noinspection PhpIncludeInspection */
            include($______snippet______);
            $this->content .= ob_get_contents();
        }
        ob_end_clean();

        if (empty($this->structure)) {
            echo $this->content;
        } else {
            ob_start();
            foreach ($this->snippets as $______snippet______) {
                if (isset($this->structure[$______snippet______])) {
                    /** @noinspection PhpIncludeInspection */
                    include($this->structure[$______snippet______]);
                }
            }
            ob_end_flush();
        }
    }

    /**
     *
     * @param string $name
     * @param array $params
     * @return string
     */
    public function route($name, array $params = [])
    {
        return $this->router->generate($name, $params);
    }

    /**
     *
     * @return \Engine\Core\Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     *
     * @return string
     */
    protected function content(): string
    {
        return $this->content;
    }
}