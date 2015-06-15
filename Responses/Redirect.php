<?php

namespace Lemmon\Responses;

class Redirect implements ResponseInterface, \JsonSerializable
{
    private $_router;
    private $_uri;
    private $_code;
    private $_flash;


    function __construct(\Lemmon\Routing\RouterInterface $router, $link = NULL, $code = NULL)
    {
        $this->_router = $router;
        if ($link) {
            $this->to($link);
        }
        if ($code) {
            $this->setStatus($code);
        }
    }


    function __toString()
    {
        return $this->toString();
    }


    function toString()
    {
        if ($this->_flash) {
            $uid = substr(md5($this->_router->getSelf()), -8) . substr(uniqid(), -8);
            @session()['flash'][$uid] = $flash;
            $this->_uri->query(['flash' => $uid]);
        }
        return (string)$this->_uri;
    }


    function toArray()
    {
        $res = [':location' => $this->toString()];
        if ($this->_flash) {
            $res[':flash'] = $this->getFlash();
        }
        return $res;
    }


    function jsonSerialize()
    {
        return $this->toArray();
    }


    function to($link)
    {
        if (is_string($link)) {
            $this->_uri = $this->_router->uri($link);
        } elseif (is_array($link)) {
            $this->_uri = call_user_func_array([$this->_router, 'uri'], $link);
        } elseif (is_object($link) and $link instanceof \Lemmon\Routing\Uri) {
            $this->_uri = $link;
        } else {
            throw new \Exception(sprintf('Unknown link type: %s', gettype($link)));
        }
        return $this;
    }


    function setStatus($code)
    {
        $this->_code = $code;
        return $this;
    }


    function getStatus($code)
    {
        return $this->_code;
    }


    function setFlash($flash)
    {
        $flash = is_string($flash) ? ['notice' => [$flash]] : $flash;
        if (TRUE === $flash) {
            // keep the old flash
            if ($_ref = @$_GET['flash'])
                $this->_uri->query(['flash' => $_ref]);
        } else {
            // new flash
            $this->_flash = $flash;
        }
    }


    function getFlash()
    {
        return $this->_flash;
    }
}