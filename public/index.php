<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "../vendor/autoload.php";

$urls = explode("/",$_GET["url"]);
$controller = $urls[0] ?? '';
$action = (isset($urls[1])) ? $urls[1] : '';
$param = (isset($urls[2])) ? $urls[2] : '';

//header('Content-Type: application/json; charset=utf-8');

if($controller != ''){
    try {
        $class = "App\Controller\\".$controller."Controller";

        if (class_exists($class)) {
            $controller = new $class();
            if (method_exists($class, $action)) {
                echo $controller->$action($param);
            }else { echo'Acion n\'existe pas pour ce controller !';}
        }else { echo 'Le controlleur n\'existe pas pour cette url !';}
    }
    catch(Exception $e) {
        // Penser à Gérer l’exception
        echo $e->getMessage();
    }
}else {
    //Route par défaut (/)
    $controller = new \App\Controller\UserController();
    echo $controller->getAll();
}


