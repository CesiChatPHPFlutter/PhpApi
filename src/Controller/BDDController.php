<?php

namespace App\Controller;

use App\Model\BDD;

class BDDController {

    public function init(){
        BDD::init();
    }
}