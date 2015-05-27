<?php

namespace Lemmon\Application;

abstract class AbstractController
{
    protected $app;

    public $data = [];


    final public function __construct(AbstractApplication $app)
    {
        // app
        $this->app = $app;
    }


    function __init(){}


    protected function redirect($link, ...$args)
    {
        $r = new \Lemmon\Responses\Redirect($this->app->router(), $link);
        // status
        if (isset($args[0]) and is_int($args[0])) {
            $r->setStatus(array_shift($args));
        }
        // flash
        if (isset($args[0])) {
            $r->setFlash(array_shift($args));
        }
        //
        return $r;
    }


    public function redir($link, $_ = NULL)
    {
        // url
        if (is_string($link)) {
            $uri = $this->route->uri($link);
        } elseif (is_array($link)) {
            $uri = call_user_func_array([$this->route, 'uri'], $link);
        } elseif (is_object($link) and $link instanceof \Lemmon\Routing\Uri) {
            $uri = $link;
        } else {
            throw new \Exception(sprintf('Unknown link type: %s', gettype($link)));
        }
        // args
        $args = array_slice(func_get_args(), 1);
        // code
        $code = (isset($args[0]) and is_int($args[0])) ? intval(array_shift($args)) : NULL;
        // flash
        if (isset($args[0])) {
            $flash = is_string($args[0]) ? ['notice' => [$args[0]]] : $args[0];
            if (TRUE === $flash) {
                // keep the old flash
                if ($_ref = @$_GET['flash'])
                    $uri->query(['flash' => $_ref]);
            } else {
                // assoc new flash ref id
                $uid = substr(md5($this->route->getSelf()), -8) . substr(uniqid(), -8);
                session()['flash'][$uid] = $flash;
                $uri->query(['flash' => $uid]);
            }
        }
        // redir
        if (\Lemmon\Http\Request::isAjax()) {
            // AJAX
            $res = ['redir' => (string)$uri];
            if (isset($flash)) {
                $res['flash'] = $flash;
            }
            return $res;
        } else {
            //
            \Lemmon\Http\Request::redir((string)$uri, $code);
        }
        //
        return [];
    }
}