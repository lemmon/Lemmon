<?php

namespace Lemmon\Auth;

abstract class AbstractAuth
{
    private $_identity;


    final function __construct()
    {
        if (is_callable([$this, '__init'])) {
            call_user_func_array([$this, '__init'], func_get_args());
        }
    }


    abstract protected function __authenticate($username, $password);
    abstract protected function __storeIdentity($id, $permanent = false);
    abstract protected function __identity($id);
    abstract protected function __clearIdentity();


    function passwordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }


    final function authenticate($username, $password)
    {
        return ($this->_identity = $this->__authenticate($username, $password)) ? TRUE : FALSE;
    }


    final function hasIdentity()
    {
        return $this->_identity ? TRUE : FALSE;
    }


    final function setIdentity($identity)
    {
        $this->_identity = $identity;
        return $this;
    }


    final function storeIdentity($permanent = FALSE)
    {
        $this->__storeIdentity($this->_identity, $permanent);
        return $this;
    }


    final function getIdentity()
    {
        return ($_ = $this->_identity) ? $this->__identity($_) : NULL;
    }


    final function clearIdentity()
    {
        $this->_identity = NULL;
        $this->__clearIdentity();
        return $this;
    }
}
