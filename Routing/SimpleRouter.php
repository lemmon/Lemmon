<?php

namespace Lemmon\Routing;

class SimpleRouter extends AbstractRouter
{


    public function match(...$args)
    {
        // method
        $method = is_int($args[0]) ? array_shift($args) : null;
        // pattern
        $pattern = array_shift($args);
        // masks
        $mask = is_array($args[0]) ? array_shift($args) : [];
        // match
        if ($this->matchPattern($pattern, $mask, $method, $matches)) {
            if (is_callable($args[0]) and FALSE !== ($res = $args[0]($this, $matches))) {
                return $res;
            }
            return TRUE;
        }
    }


    public function redir($link)
    {
        header('Location: ' . $this->to($link));
        exit;
    }
}