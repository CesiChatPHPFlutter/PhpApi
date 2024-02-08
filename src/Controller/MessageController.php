<?php
namespace src\Controller;

use src\Model\Message;
use src\Service\JwtService;

class MessageController extends AbstractController {

    public function GetBySenderId(int $senderId) {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(Message::SqlGetBySenderId($senderId));
    }

    public function GetByRecieverId(int $recieverId) {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(Message::SqlGetByRecieverId($recieverId));
    }

    
}