<?php

namespace App\Controller;

use App\Model\Message;
use App\Service\JwtService;

class MessageController extends AbstractController {

    public function create () {
        $requestBody = json_decode(file_get_contents('php://input'), true);
        $newMessage = new Message();

        if ($senderId = $requestBody['senderId'] ?? null) {
            $newMessage->setSender($senderId);
        }
        if ($receiverId = $requestBody['receiverId'] ?? null) {
            $newMessage->setReceiver($receiverId);
        }
        if ($content = $requestBody['content'] ?? null) {
            $newMessage->setContent($content);
        }


        //header('Content-Type: application/json; charset=utf-8');
        return json_encode(Message::SqlAdd($newMessage));
    }

    public function getBySenderId(int $senderId) {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(Message::SqlGetBySenderId($senderId));
    }

    public function getByReceiverId(int $receiverId) {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(Message::SqlGetByReceiverId($receiverId));
    }

    public function update(int $messageId)
    {
        $requestBody = json_decode(file_get_contents('php://input'), true);
        $updatedMessage = new Message();

        if ($senderId = $requestBody['senderId'] ?? null) {
            $updatedMessage->setSender($senderId);
        }
        if ($receiverId = $requestBody['receiverId'] ?? null) {
            $updatedMessage->setReceiver($receiverId);
        }
        if ($content = $requestBody['content'] ?? null) {
        }

        header('Content-Type: application/json; charset=utf-8');
        return json_encode(message::SqlUpdate($messageId, $updatedMessage));
    }
    
    public function delete(int $messageId) {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(Message::SqlDelete($messageId));
    }



}