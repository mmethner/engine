<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
namespace Engine\Test\Core\Fixture;

class Router extends \Engine\Core\Router
{
    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
