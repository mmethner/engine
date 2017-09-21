<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */

namespace Engine\Core;

class Exception
{

    /**
     *
     * @param \Throwable $exception
     * @return void
     */
    public static function exception(\Throwable $exception)
    {
        $trace = $exception->getTrace();
        foreach ($trace as $key => $stackPoint) {
            // Converting arguments to their type e.g.
            // prevents passwords from ever getting logged as anything other than 'string'
            if (isset($trace[$key]['args'])) {
                $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
            }
        }

        $stackTrace = [];
        foreach ($trace as $key => $stackPoint) {
            $args = isset($stackPoint['args']) ? implode(', ', $stackPoint['args']) : '';
            $file = isset($stackPoint['file']) ? $stackPoint['file'] : 'file unknown';
            $line = isset($stackPoint['line']) ? $stackPoint['line'] : 'line unknown';
            $stackTrace[] = sprintf("%s(%s): %s(%s)", $file, $line, $stackPoint['function'], $args);
        }
        $stackTrace[] = '{main}';

        $params = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $stackTrace
        ];
        static::out($params);
    }

    /**
     * collects data for current request
     *
     * @return string
     */
    private static function collect()
    {
        $message = '';

        $message .= sprintf("\r\nURL: %s\r\n", $_SERVER['REQUEST_URI']);

        if (!empty($_POST)) {
            $message .= "\r\n";
            foreach ($_POST as $key => $value) {
                if (in_array($key, [
                    'password',
                    'password2'
                ])) {
                    continue;
                }
                $message .= 'post #' . $key . ': ' . (is_array($value) ? serialize($value) : $value) . "\r\n";
            }
        }

        if (!empty($_GET)) {
            $message .= "\r\n";
            foreach ($_GET as $key => $value) {
                if (in_array($key, [
                    'password',
                    'password2'
                ])) {
                    continue;
                }
                $message .= 'get #' . $key . ': ' . $value . "\r\n";
            }
        }

        if (!empty($_COOKIE)) {
            $message .= "\r\n";
            foreach ($_COOKIE as $key => $value) {
                $message .= 'cookie #' . $key . ': ' . $value . "\r\n";
            }
        }

        if (!empty($_SERVER)) {
            $message .= "\r\n";
            foreach ($_SERVER as $key => $value) {
                if (is_array($value)) {
                    continue;
                }
                $message .= 'server #' . $key . ': ' . $value . "\r\n";
            }
        }

        if (!empty($_SESSION)) {
            $message .= "\r\n";
            foreach ($_SESSION as $key => $value) {
                $message .= 'session #' . $key . ': ' . (is_array($value) ?
                        serialize($value) : print_r($value,1)) . "\r\n";
            }
        }

        return $message;
    }

    /**
     *
     * @return void
     */
    public static function fatal()
    {
        $error = error_get_last();

        if (!$error) {
            return;
        }

        if ($error['type'] === E_ERROR || $error['type'] === E_RECOVERABLE_ERROR || $error['type'] === E_COMPILE_ERROR) {

            $stackTrace = [];
            foreach (debug_backtrace() as $key => $stackPoint) {
                $stackTrace[] = sprintf('%s: %s(%s)', $stackPoint['class'], $stackPoint['function'],
                    implode(', ', $stackPoint['args']));
            }
            $stackTrace[] = sprintf('File %s :: Line %s', $error['file'], $error['line']);
            $stackTrace[] = '{main}';

            $params = [
                'type' => 'Fatal Error',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'trace' => $stackTrace
            ];
            static::out($params);
        }
    }

    /**
     *
     * @param array $params
     * @return void
     */
    private static function out(array $params)
    {
        // clean all content from output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        $context = static::collect();

        $out = <<<HTML
<!DOCTYPE html>
<html>
<head>
<title>Engine\Core</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="robots" content="noindex, nofollow" />
<meta http-equiv="content-type" content="text/html;charset=utf-8">
<meta http-equiv="pragma" content="no-cache">
<link rel="icon" href="/root/img/favicon.png" type="image/png">
<style>
body {
	font-family: Arial, sans-serif;
	font-size: 18px;
	text-align: center;
	color: #ffffff;
	background-color: #2C3E50;
	margin: 10px;
}

h1 {
	font-size: 100px;
	color: #ff0000;
}

h2 {
	font-size: 40px;
	color: #ffffff;
}

h3 {
	color: #2C3E50;
}
</style>
</head>
<body>
	<h1>Oh Shit.</h1>
	<h2>The internet is broken.<br/> Engine detected an unrecoverable error.</h2>
	<h3>{$params['message']}</h3>
	<pre>
	    {$context}
    </pre>
</body>
</html>
HTML;
        echo $out;
    }
}