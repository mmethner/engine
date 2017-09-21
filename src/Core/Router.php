<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
namespace Engine\Core;

/**
 * code is copied and adjusted from
 *
 * @see https://github.com/dannyvankooten/AltoRouter Route Example Match Variables
 *      /contact/ /contact/
 *      /users/[i:id]/ /users/12/ $id: 12
 *      /[a:c]/[a:a]?/[i:id]? /controller/action/21 $c: "controller", $a: "action", $id: 21
 *
 *      matchers
 *      * // Match all request URIs
 *      [i] // Match an integer
 *      [i:id] // Match an integer as 'id'
 *      [a:action] // Match alphanumeric characters as 'action'
 *      [h:key] // Match hexadecimal characters as 'key'
 *      [:action] // Match anything up to the next / or end of the URI as 'action'
 *      [create|edit:action] // Match either 'create' or 'edit' as 'action'
 *      [*] // Catch all (lazy, stops at the next trailing slash)
 *      [*:trailing] // Catch all as 'trailing' (lazy)
 *      [**:trailing] // Catch all (possessive - will match the rest of the URI)
 *      .[:format]? // Match an optional parameter 'format' - a / or . before the block is also optional
 */
class Router
{

    /**
     *
     * @var array
     */
    protected $routes = [];

    /**
     *
     * @var array
     */
    protected $namedRoutes = [];

    /**
     *
     * @var array
     */
    protected $matchTypes = [
        'i' => '[0-9]++',
        'a' => '[0-9A-Za-z]++',
        'h' => '[0-9A-Fa-f]++',
        '*' => '.+?',
        '**' => '.++',
        '' => '[^/\.]++'
    ];

    /**
     *
     * @param string $route
     *            The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
     * @param string $controller
     *            The target controller where this route should point to. Can be anything.
     * @param string $action
     * @param string $name
     *            The name of this route. To reverse route this url in your application.
     * @return void
     * @throws \Exception
     */
    public function map($route, $controller, $action, $name)
    {
        $this->routes[] = [
            $route,
            $controller,
            $action,
            $name
        ];

        if (isset($this->namedRoutes[$name])) {
            throw new \Exception("Can not redeclare route '{$name}'");
        } else {
            $this->namedRoutes[$name] = $route;
        }
    }

    /**
     * Reversed routing
     *
     * Generate the URL for a named route. Replace regexes with supplied parameters
     *
     * @param string $routeName
     *            The name of the route.
     * @param array $params Associative array of parameters to replace placeholders with.
     * @return string The URL of the route with named parameters in place.
     * @throws \Exception
     */
    public function generate($routeName, array $params = [])
    {
        if (!isset($this->namedRoutes[$routeName])) {
            throw new \Exception("Route '{$routeName}' does not exist.");
        }

        $route = $this->namedRoutes[$routeName];

        $url = $route;

        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {

            foreach ($matches as $match) {
                list ($block, $pre, $type, $param, $optional) = $match;

                if ($pre) {
                    $block = substr($block, 1);
                }

                if (isset($params[$param])) {
                    $url = str_replace($block, $params[$param], $url);
                } elseif ($optional) {
                    $url = str_replace($pre . $block, '', $url);
                }
            }
        }

        return $url;
    }

    /**
     * Match a given Request Url against stored routes
     *
     * @return array
     */
    public function match()
    {
        $params = [];

        $requestUrl = $this->requestUrl();

        foreach ($this->routes as $handler) {
            list ($_route, $controller, $action, $name) = $handler;

            if (false !== strpos($_route, '[:locale]')) {
                $_route = str_replace('[:locale]', '[' . implode('|', Config::get('locale', 'available')) . ':locale]',
                    $_route);
            }

            if ($_route === '*') {
                $match = true;
            } elseif (isset($_route[0]) && $_route[0] === '@') {
                $match = preg_match('`' . substr($_route, 1) . '`u', $requestUrl, $params);
            } else {
                $route = null;
                $regex = false;
                $j = 0;
                $n = isset($_route[0]) ? $_route[0] : null;
                $i = 0;

                // Find the longest non-regex substring and match it against the URI
                while (true) {
                    if (!isset($_route[$i])) {
                        break;
                    } elseif (false === $regex) {
                        $c = $n;
                        $regex = $c === '[' || $c === '(' || $c === '.';
                        if (false === $regex && false !== isset($_route[$i + 1])) {
                            $n = $_route[$i + 1];
                            $regex = $n === '?' || $n === '+' || $n === '*' || $n === '{';
                        }
                        if (false === $regex && $c !== '/' && (!isset($requestUrl[$j]) || $c !== $requestUrl[$j])) {
                            continue 2;
                        }
                        $j++;
                    }
                    $route .= $_route[$i++];
                }
                $regex = $this->compileRoute($route);
                $match = preg_match($regex, $requestUrl, $params);
            }

            if (($match == true || $match > 0)) {
                if ($params) {
                    foreach ($params as $key => $value) {
                        if (is_numeric($key)) {
                            unset($params[$key]);
                        }
                        if ($key === 'locale') {
                            foreach ($this->namedRoutes as $nameId => $namedRoute) {
                                $this->namedRoutes[$nameId] = str_replace('[:locale]', $value, $namedRoute);
                            }
                        }
                    }
                }

                return [
                    'controller' => $controller,
                    'action' => $action,
                    'params' => $params,
                    'name' => $name
                ];
            }
        }
        return [];
    }

    /**
     *
     * @return string
     */
    private function requestUrl()
    {
        $requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

        // Strip query string (?a=b) from Request Url
        if (($strpos = strpos($requestUrl, '?')) !== false) {
            $requestUrl = substr($requestUrl, 0, $strpos);
        }

        return $requestUrl;
    }

    /**
     * @param string $route
     * @return string
     */
    private function compileRoute($route)
    {
        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
            $matchTypes = $this->matchTypes;
            foreach ($matches as $match) {
                list ($block, $pre, $type, $param, $optional) = $match;

                if (isset($matchTypes[$type])) {
                    $type = $matchTypes[$type];
                }

                if ($pre === '.') {
                    $pre = '\.';
                }

                // Older versions of PCRE require the 'P' in (?P<named>)
                $pattern = '(?:' . ($pre !== '' ? $pre : null) . '(' . ($param !== '' ? "?P<$param>" : null) . $type . '))' . ($optional !== '' ? '?' : null);

                $route = str_replace($block, $pattern, $route);
            }
        }
        return "`^$route$`u";
    }

    /**
     *
     * @return string
     */
    public function serverHost()
    {
        return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    }
}
