<?php

namespace Lemmon\Http;

class Request
{


    static function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }


    static function redir($url, $code = NULL)
    {
        if (isset($code)) {
            header('Location: ' . $url, TRUE, $code);
        } else {
            header('Location: ' . $url);
        }
        exit;
    }
}