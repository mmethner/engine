<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
namespace Engine\Base\Controller;

use Engine\Tools\Http;

class Controller extends \Engine\Core\Controller
{

    /**
     * default action if user hits /
     * @return void
     */
    public function defaultAction(): void
    {
        $rootUrl = Http::current() . '/' . $this->view->getLanguage()->getAppliedLang();
        $this->view->header(http::redirect($rootUrl));
    }

    /**
     *
     * @return void
     */
    public function documentationAction(): void
    {
        $this->view->snippet('Base::content/documentation.phtml');
    }

    /**
     * default action for unknown pages
     * @return void
     */
    public function http404Action(): void
    {
        $header = Http::status(404);
        $this->view->header($header);
        $this->view->snippet('Base::error/http-404.phtml');
        $this->view->assign('message', $header);
    }

    /**
     * default action for misconfigured routes: component || action
     * @return void
     */
    public function frameworkAction(): void
    {
        $header = Http::status(501);
        $this->view->header($header);
        $this->view->snippet('Base::error/framework-error.phtml');
        $this->view->assign('message', $header);
    }
}