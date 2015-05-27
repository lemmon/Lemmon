<?php

namespace Lemmon\Application;

use \Lemmon\Utils\NamespaceTrait;

abstract class AbstractApplication implements ApplicationInterface
{
    use NamespaceTrait;
    
    private $_env;
    private $_router;
    private $_db;
    private $_controllerName = 'Index';
    private $_actionName = 'index';
    private $_actionArgs = [];
    private $_render;


    final public function __construct()
    {
    }


    protected function __init(){}


    final public function run()
    {
        $this->__init();
        // names
        $_c = $this->ns() . $this->_controllerName . 'Controller';
        // create controller
        $controller = new $_c($this);
        // init controller
        $controller->__init();
        // execute action
        if (method_exists($controller, $this->_actionName)) {
            $this->res($controller, $this->trap(function() use ($controller){
                return call_user_func_array([$controller, $this->_actionName], $this->_actionArgs);
            }));
        } else {
            throw new \Exception(sprintf('Method %s::%s does NOT exist', $this->_controllerName, $this->_actionName));
        }
    }


    protected function trap(callable $fn)
    {
        return $fn();
    }


    function display($template)
    {
        $this->render()['template'] = $template;
    }


    function &render($input = NULL)
    {
        if ($input) {
            if (is_array($input)) {
                $this->_render = array_replace($this->_render ?: [], $input);
            } elseif (is_string($input)) {
                $this->_render['template'] = $input;
            }
        }
        return $this->_render;
    }


    protected function res(AbstractController $controller, $res)
    {
        $r = $this->_render ?: [];
        // redirects
        if (isset($r['redir']) and !isset($res)) {
            $res = new \Lemmon\Responses\Redirect($this->router(), $r['redir']);
            if (isset($r['flash'])) {
                $res->setFlash($r['flash']);
            }
        }
        // res
        if (isset($res)) {
            switch (gettype($res)) {
                case 'integer':
                    $r = array_replace_recursive([
                        'status' => $res,
                        'type' => 'text',
                        'data' => $res,
                    ], $r);
                    break;
                case 'string':
                    $r = array_replace_recursive([
                        'type' => 'text',
                        'data' => $res,
                    ], $r);
                    break;
                case 'array':
                    $r = array_replace_recursive([
                        'type' => 'json',
                        'data' => $res,
                    ], $r);
                    break;
                case 'object':
                    if ($res instanceof \stdClass) {
                        $r = array_replace_recursive([
                            'type' => 'json',
                            'data' => $res,
                        ], $r);
                    } elseif ($res instanceof \Lemmon\Responses\ResponseInterface) {
                        switch (get_class($res)) {
                            case 'Lemmon\Responses\Redirect':
                                $r = array_replace_recursive([
                                    'type' => 'redir',
                                    'data' => $res,
                                ], $r);
                                break;
                            default:
                                throw new \Exception(sprintf('Unknown response: %s', get_class($res)));
                        }
                    } else {
                        $r['data'] = $res;
                    }
                    break;
            }
        }
        // status
        if (@$r['status']) {
            http_response_code($r['status']);
        }
        //
        switch (@$r['type']) {
            case 'text':
                header('Content-type: text/plain; charset=utf-8');
                echo @$r['data'];
                break;
            case 'json':
                header('Content-Type: application/json');
                echo json_encode(@$r['data'], JSON_PRETTY_PRINT);
                break;
            case 'jsonp':
                header('Content-Type: application/json');
                echo @$r['fn'] ?: 'fn', '(', json_encode(@$r['data'], JSON_PRETTY_PRINT), ')';
                break;
            case 'redir':
                header('Location: ' . @$r['data']);
                exit;
                break;
            default:
                if (FALSE !== @$r['template']) {
                    // render template
                    $twig = $this->env()->getTemplate($this);
                    // template
                    $template = @$r['template'] ?: $this->getAction();
                    // data
                    if (isset($r['data']) and !is_array($r['data'])) {
                        $data = array_replace($controller->data, ['res' => $r['data']]);
                    } else {
                        $data = $controller->data;
                    }
                    // app
                    $data['app'] = [
                        'controller' => $this->getController(),
                        'action' => $this->getAction(),
                    ];
                    // flash
                    if ($_ref = @$this->router()->getQuery()['flash'] and $_flash = session()['flash'][$_ref]) {
                        $data['flash'] = $_flash;
                    }
                    // display
                    $twig->display($template . '.html', $data);
                }
                break;
        }
    }


    final public function setController($name)
    {
        $this->_controllerName = \Lemmon\Strings\Inflector::camelize($name);
        return $this;
    }


    final public function getController()
    {
        return $this->_controllerName;
    }


    final public function setAction($name, array $args = [])
    {
        $name = preg_replace_callback('#[\-\_]+(\w)#', function($m){
            return strtoupper($m[1]);
        }, $name);
        $name{0} = strtolower($name{0});
        $this->_actionName = $name;
        $this->_actionArgs = $args;
        return $this;
    }


    final public function getAction()
    {
        return \Lemmon\Strings\Inflector::underscore($this->_actionName);
    }


    final public function env($class = 'Cfg\Env')
    {
        return $this->param($this->_env, $class);
    }


    final public function router($e = NULL, array $options = NULL, $class = __NAMESPACE__ . '\Router')
    {
        return $this->param($this->_router, function() use ($e, $options, $class){
            return new $class($this, $e, $options);
        });
    }


    final public function db($class = 'Cfg\Db')
    {
        return $this->param($this->_db, $class, function($db){
            $db->setNamespace($this->ns());
        });
    }


    final public function param(&$var, $class = FALSE, \Closure $callback = NULL)
    {
        if (isset($var)) {
            return $var;
        } elseif (is_string($class)) {
            $var = new $class($this);
        } elseif (is_callable($class)) {
            $var = $class();
        } elseif (is_object($class)) {
            $var = $class;
        }
        if (is_callable($callback)) {
            $callback($var);
        }
        return $this;
    }
}