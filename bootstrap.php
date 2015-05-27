<?php

//
// autoloading
spl_autoload_register(include __DIR__ . '/autoload.php');

//
// session
session_start();

//
// functions
function &session($_ns = NULL) { static $ns = '__LEMMON'; if (isset($_ns)) $ns = $_ns; return $_SESSION[$ns]; }
