<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
namespace Engine\Test\Core;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    /**
     * @var \Engine\Test\Core\Fixture\Router
     */
    protected $router;

    /**
     * @covers \Engine\Core\Router::map
     */
    public function testMap()
    {
        $route = '/[:locale]';
        $controller = 'Website::Controller';
        $action = 'homeAction';
        $name = 'Website::Controller::index';

        $this->router->map($route, $controller, $action, $name);

        $routes = $this->router->getRoutes();

        $this->assertEquals([
            $route,
            $controller,
            $action,
            $name
        ], $routes[0]);
    }

    /**
     * @covers \Engine\Core\Router::map
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp #Can not redeclare route.*#
     */
    public function testMapWithNameDuplicate()
    {
        $route = '/[:locale]';
        $controller = 'Website::Controller';
        $action = 'homeAction';
        $name = 'Website::Controller::index';

        $this->router->map($route, $controller, $action, $name);
        $this->router->map($route, $controller, $action, $name);
    }

    /**
     * @covers \Engine\Core\Router::generate
     */
    public function testGenerate()
    {
        $this->router->map('/[:locale]/camp/edit/[i:id]', 'Camp::Controller', 'editAction', 'Camp::Controller::edit');

        $params = [
            'locale' => 'fr',
            'id' => '666'
        ];

        $this->assertEquals('/fr/camp/edit/666', $this->router->generate('Camp::Controller::edit', $params));
    }

    /**
     * @covers \Engine\Core\Router::generate
     */
    public function testGenerateIgnoresUnknownParams()
    {
        $this->router->map('/[:locale]/camp/edit/[i:id]', 'Camp::Controller', 'editAction', 'Camp::Controller::edit');

        $params = [
            'locale' => 'fr',
            'id' => '666',
            'unknown' => 'unknown'
        ];

        $this->assertEquals('/fr/camp/edit/666', $this->router->generate('Camp::Controller::edit', $params));
    }

    /**
     * @covers \Engine\Core\Router::generate
     */
    public function testGenerateWithOptionalUrlParts()
    {
        $this->router->map('/[:locale]/camp/edit/[:optional]?', 'Camp::Controller', 'editAction',
            'Camp::Controller::edit');

        $params = [
            'locale' => 'fr'
        ];

        $this->assertEquals('/fr/camp/edit', $this->router->generate('Camp::Controller::edit', $params));

        $params = [
            'locale' => 'fr',
            'optional' => 'optional'
        ];

        $this->assertEquals('/fr/camp/edit/optional', $this->router->generate('Camp::Controller::edit', $params));
    }

    /**
     * @covers \Engine\Core\Router::generate
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp #Route .* does not exist.#
     */
    public function testGenerateWithNonexistingRoute()
    {
        $this->router->generate('nonexisting_route');
    }

    /**
     * @covers \Engine\Core\Router::generate
     */
    public function testGenerateWithListOfUrlParts()
    {
        $this->router->map('/camp/[summary|options:step]', 'Camp::Controller', 'editAction', 'routeName');

        $params = [
            'step' => 'options'
        ];

        $this->assertEquals('/camp/options', $this->router->generate('routeName', $params));
    }

    /**
     * @covers \Engine\Core\Router::match
     * @covers \Engine\Core\Router::compileRoute
     */
    public function testMatch()
    {
        $this->router->map('/camp/edit/[i:id]', 'Camp::Controller', 'editAction', 'Camp::Controller::edit');

        $_SERVER['REQUEST_URI'] = '/camp/edit/666?test=test';

        $this->assertEquals([
            'controller' => 'Camp::Controller',
            'action' => 'editAction',
            'params' => [
                'id' => '666'
            ],
            'name' => 'Camp::Controller::edit'
        ], $this->router->match());
    }

    /**
     * @covers \Engine\Core\Router::match
     * @covers \Engine\Core\Router::compileRoute
     */
    public function testMatchWithFixedParamValues()
    {
        $this->router->map('/camp/[delete|update:action]/[i:id]', 'Camp::Controller', 'editAction',
            'Camp::Controller::edit');

        $_SERVER['REQUEST_URI'] = '/camp/delete/666';

        $this->assertEquals([
            'controller' => 'Camp::Controller',
            'action' => 'editAction',
            'params' => [
                'id' => '666',
                'action' => 'delete'
            ],
            'name' => 'Camp::Controller::edit'
        ], $this->router->match());

        $_SERVER['REQUEST_URI'] = '/camp/unknown/666';
        $this->assertEmpty($this->router->match());

        $_SERVER['REQUEST_URI'] = '/camp/delete/abc';
        $this->assertEmpty($this->router->match());
    }

    /**
     * @covers \Engine\Core\Router::match
     * @covers \Engine\Core\Router::compileRoute
     */
    public function testMatchWithOptionalUrlParts()
    {
        $this->router->map('/camp/[i:id]/[:step]?', 'controller', 'action', 'name');

        $_SERVER['REQUEST_URI'] = '/camp/666/optional';

        $this->assertEquals([
            'controller' => 'controller',
            'action' => 'action',
            'params' => [
                'id' => '666',
                'step' => 'optional'
            ],
            'name' => 'name'
        ], $this->router->match());
    }

    /**
     * @covers \Engine\Core\Router::match
     * @covers \Engine\Core\Router::compileRoute
     */
    public function testMatchWithWildcard()
    {
        $this->router->map('/edit', 'controller', 'action1', 'name1');
        $this->router->map('*', 'controller', 'action2', 'name2');

        $_SERVER['REQUEST_URI'] = '/wildcard';

        $this->assertEquals([
            'controller' => 'controller',
            'action' => 'action2',
            'params' => [],
            'name' => 'name2'
        ], $this->router->match());

        $_SERVER['REQUEST_URI'] = '/edit';

        $this->assertEquals([
            'controller' => 'controller',
            'action' => 'action1',
            'params' => [],
            'name' => 'name1'
        ], $this->router->match());
    }

    /**
     * @covers \Engine\Core\Router::match
     * @covers \Engine\Core\Router::compileRoute
     */
    public function testMatchWithCustomRegexp()
    {
        $this->router->map('@^/[a-z]*$', 'controller', 'action', 'name');

        $_SERVER['REQUEST_URI'] = '/regex';

        $this->assertEquals([
            'controller' => 'controller',
            'action' => 'action',
            'params' => [],
            'name' => 'name'
        ], $this->router->match());

        $_SERVER['REQUEST_URI'] = '/regexp-not-matching';
        $this->assertEmpty($this->router->match());
    }

    protected function setUp()
    {
        $this->router = new Fixture\Router();
    }
}
