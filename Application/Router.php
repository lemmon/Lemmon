<?php

namespace Lemmon\Application;

class Router extends \Lemmon\Routing\AbstractRouter
{
    private $_app;
    private $_routes = [];
    private $_matchedRoute;
    private $_matchedParams;


    public function __construct($app, $callback = NULL, array $options = NULL)
    {
        $this->_app = $app;
        //
        parent::__construct($options);
        // callback
        if (is_callable($callback)) {
            $callback($this);
        }
        // match route
        foreach ($this->_routes as $_route) {
            if (FALSE !== ($res = call_user_func_array([$this, '_match'], $_route))) {
                $this->_matchedRoute = $res['pattern'];
                $this->_matchedParams = $res['matches'];
                // controller
                if (@$res['matches']['controller']) {
                    $app->setController($res['matches']['controller']);
                } elseif (@$res['action'][0]) {
                    $app->setController(preg_replace_callback('/{(?<name>\w+)}/', function($m) use ($res){
                        return $res['matches'][$m['name']];
                    }, $res['action'][0]));
                }
                // action
                if (@$res['matches']['action']) {
                    $app->setAction($res['matches']['action'], $res['pass']);
                } elseif (@$res['action'][1]) {
                    $app->setAction($res['action'][1], $res['pass']);
                } elseif ($res['pass']) {
                    $app->setAction('index', $res['pass']);
                }
                break;
            }
        }
    }


    public function __isset($name)
    {
        return (isset($this->_matchedParams[$name]));
    }


    public function __get($name)
    {
        return (isset($this->_matchedParams[$name])) ? $this->_matchedParams[$name] : NULL;
    }


    protected function app()
    {
        return $this->_app;
    }


    private function _match(...$args)
    {
        // method
        $method = is_int($args[0]) ? array_shift($args) : NULL;
        // pattern
        $pattern = array_shift($args);
        // masks
        $mask = (isset($args[0]) and is_array($args[0]) and !isset($args[0][0])) ? array_shift($args) : [];
        // match
        if (is_string($pattern) and $this->matchPattern($pattern, $mask, $method, $m, $defaults)) {
            $matches = $m;
            if (is_array($defaults)) {
                $matches = array_replace($defaults, $matches);
            }
        } elseif (is_callable($pattern) and is_array($m = $pattern($this))) {
            $matches = $m;
        } else {
            return FALSE;
        }
        // controller/action
        $action = (isset($args[0]) and is_string($args[0])) ? explode(':', array_shift($args)) : NULL;
        // action params
        $pass = [];
        if (isset($args[0]) and is_array($args[0]) and $_pass = array_shift($args)) {
            foreach ($_pass as $_key) {
                $pass[$_key] = isset($matches[$_key]) ? $matches[$_key] : NULL;
            }
        }
        // callback
        if (isset($args[0]) and is_callable($args[0]) and FALSE === ($res = $args[0]($this, $matches))) {
            return FALSE;
        }
        // res
        return [
            'pattern' => $pattern,
            'matches' => $matches,
            'action'  => $action,
            'pass'    => $pass,
        ];
    }


    final public function match(...$args)
    {
        $this->_routes[] = func_get_args();
    }


    private function _paste($pattern, $args, $force_default = FALSE)
    {
        foreach ($args as $key => $val) {
            $pattern = preg_replace_callback('/{(?<param>' . $key . ')(?::(?<type>\w+))?(?:=(?<default>\w+))?}/', function($m) use ($val, $force_default){
                return ($val == @$m['default'] ? '@' : '') . $val . '!';
            }, $pattern);
        }
        return $pattern;
    }


    public function to($link, ...$args)
    {
        if (is_array($link)) {
            $pattern = $this->_matchedRoute;
            $pattern = $this->_paste($pattern, $link);
            if ($i = strrpos($pattern, '!')) {
                $pattern = substr($pattern, 0, $i) . preg_replace('/{.+$/U', '', substr($pattern, $i));
            }
            $pattern = $this->_paste($pattern, $this->_matchedParams);
            do {
                $pattern = rtrim($pattern, '/');
                $pattern = preg_replace('#\([^\(\)]*@[\w\-]+![^\(\)]*\)#', '', $pattern, -1, $n);
            } while ($n);
            do {
                $pattern = rtrim($pattern, '/');
                $pattern = preg_replace('#@[^/]+$#', '', $pattern, -1, $n);
            } while ($n);
            $pattern = str_replace(['!', '@', '(', ')'], '', $pattern);
            return $this->to($pattern);
        } else {
            return call_user_func_array('parent::to', func_get_args());
        }
    }
}