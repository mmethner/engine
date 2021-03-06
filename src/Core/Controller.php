<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
namespace Engine\Core;

use Engine\Tools\Path;

abstract class Controller
{

    /**
     *
     * @var \Engine\Core\View
     */
    protected $view;

    /**
     *
     * @var array
     */
    protected $params;

    /**
     *
     * @var string
     */
    protected $action = '';

    /**
     *
     * @param \Engine\Core\Router $router
     * @param array $params
     */
    public function __construct(Router $router, array $params = [])
    {
        $this->params = $params;

        $this->initView($router);
    }

    /**
     *
     * @param \Engine\Core\Router $router
     * @param string $namespace
     * @return void
     */
    protected function initView(Router $router, string $namespace = ''): void
    {
        $module = str_replace('\Controller\Controller', '', get_class($this));
        $module = str_replace('Engine\\', '', $module);

        $locale = isset($this->params['locale']) ? $this->params['locale'] : '';
        $view = '\\Engine\\' . $module . '\\View\\View';

        $component = str_replace('\\', '/', $module);
        $this->view = new $view($component, $router, $locale);

        $this->view->assign('locale', $locale);
        $this->view->assign('localeISO', Language::localeToISO($locale));
    }

    /**
     *
     * @param Router $router
     * @param string $namespace
     * @return void
     */
    public static function dispatch(Router $router, string $namespace = ''): void
    {
        $route = $router->match();

        if (empty($route)) {
            $controller = new \Engine\Base\Controller\Controller($router);
            $controller->http404Action();
            $controller->action = 'http404Action';
            $controller->run();
        } else {
            list ($component, $controller) = Path::separate($route['controller']);
            // e.g /Engine/Documentation/Controller/Controller
            $class = '/Engine/' . $component . '/Controller/' . $controller;

            // linux / window tweak
            // e.g. \Engine\Documentation\Controller\Controller
            $class = str_replace('/', '\\', $class);

            // enable apps to use own namespace
            // e.g. \Engine\App\Documentation\Controller\Controller
            $class = str_replace('\Engine', $namespace, $class);

            if (!class_exists($class)) {
                $controller = new \Engine\Base\Controller\Controller($router, $route['params']);
                $controller->frameworkAction();
                $controller->action = 'frameworkAction';
                $controller->run();
            } elseif (!is_callable([
                $class,
                $route['action']
            ])
            ) {
                $controller = new \Engine\Base\Controller\Controller($router, $route['params']);
                $controller->frameworkAction();
                $controller->action = 'frameworkAction';
                $controller->run();
            } else {
                /* @var $controller \Engine\Core\Controller */
                $controller = new $class($router, $route['params']);
                $controller->{$route['action']}();
                $controller->action = $route['action'];
                $controller->run();
            }
        }
    }

    /**
     *
     * @return void
     */
    public function run(): void
    {
        $this->view->render();
    }
}