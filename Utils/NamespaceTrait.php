<?php

namespace Lemmon\Utils;

trait NamespaceTrait
{
    private $_utilsNamespacePrefix;


    function ns()
    {
        if (isset($this->_utilsNamespacePrefix)) {
            return $this->_utilsNamespacePrefix;
        } elseif (preg_match('#(\w+Bundle\\\)([\w\\\]+)#i', get_class($this), $m)) {
            return $this->_utilsNamespacePrefix = $m[1];
        } else {
            return $this->_utilsNamespacePrefix = '';
        }
    }
}