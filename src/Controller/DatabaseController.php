<?php

namespace App\Controller;

use App\Model\BDD;
use App\Model\Config;

class BDDController {

    public function init(){
        $requestBody = json_decode(file_get_contents('php://input'), true);
        Config::load();
        if($requestBody === null || $requestBody['dbPassword'] === null || $requestBody['dbPassword'] !== Config::$DBPASSWORD)
        {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            return json_encode('Unauthorized');
        }

        BDD::init();
        //header('Content-Type: application/json; charset=utf-8');
        return json_encode('Database (re)initiated');
    }
}