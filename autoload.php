<?php

return function($c){
    $app_dir = BASE_DIR;
    // bundles
    if (preg_match('#(\w+)Bundle\\\([\w\\\]+)#i', $c, $m)) {
        $app_dir .= '/bundles/' . $m[1];
        $c = $m[2];
    }
    // config
    if ('Cfg\\' == substr($c, 0, 4)) {
        include $app_dir . '/cfg/' . \Lemmon\Strings\Inflector::underscore(substr($c, 4)) . '.php';
    }
    // controllers
    if ('Controller' == substr($c, -10)) {
        include $app_dir . '/app/controllers/' . $c . '.php';
    }
    // application
    elseif ('Application' == $c) {
        include $app_dir . '/app/controllers/Application.php';
    }
    // services
    elseif ('Services\\' == substr($c, 0, 9)) {
        include $app_dir . '/app/services/' . substr($c, 9) . '.php';
    }
    // mailers
    elseif ('Mailers\\' == substr($c, 0, 8)) {
        include $app_dir . '/app/mailers/' . substr($c, 8) . '.php';
    }
    // models
    elseif (file_exists($res = $app_dir . '/app/models/' . $c . '.php')) {
        include $res;
    }
    // PSR-0 lib
    elseif ('lib\\' == substr($c, 0, 4) and file_exists($res = BASE_DIR . '/' . preg_replace('#\\\|_(?!.+\\\)#', '/', $c) . '.php')) {
        include $res;
    }
    elseif (file_exists($res = BASE_DIR . '/lib/' . preg_replace('#\\\|_(?!.+\\\)#', '/', $c) . '.php')) {
        include $res;
    }
};