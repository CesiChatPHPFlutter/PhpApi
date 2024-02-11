<?php

namespace App\Controller;

use App\Model\Message;
use App\Service\JwtService;

class MessageController {
    public function getBySenderId(int $senderId) {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(Message::SqlGetBySenderId($senderId));
    }

    public function getByReceiverId(int $receiverId) {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(Message::SqlGetByReceiverId($receiverId));
    }

    public function getMessagesBetweenUsers() {
        $requestBody = json_decode(file_get_contents('php://input'));
        $userId1 = $requestBody->userId1;
        $userId2 = $requestBody->userId2;
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(Message::SqlGetMessagesBetweenUsers($userId1, $userId2));
    }

    public function getChats(){
        $requestBody = json_decode(file_get_contents('php://input'), true);
        $jwtToken = $requestBody['jwtToken'] ?? null;
        if($jwtToken == null) {
            http_response_code(400);
            return "Missing jwtToken";
        }
        
        $datas = JwtService::decryptToken($jwtToken);
        if($datas == null || $datas->userId == null) {
            http_response_code(400);
            return "Invalid jwtToken";
        }

        $array = Message::SqlGetChats($datas->userId);

        header('Content-Type: application/json; charset=utf-8');
        return json_encode($array);
    }

    public function create () {
        $requestBody = json_decode(file_get_contents('php://input'), true);
        
        http_response_code(400);

        $newMessage = new Message();
        if ($senderId = $requestBody['senderId'] ?? null) {
            $newMessage->setSender($senderId);
        } else return 'Missing Sender';
        if ($receiverId = $requestBody['receiverId'] ?? null) {
            $newMessage->setReceiver($receiverId);
        } else return 'Missing Receiver';
        if ($content = $requestBody['content'] ?? null) {
            $newMessage->setContent($content);
        } else $newMessage->setContent(' ');

        $response = Message::SqlAdd($newMessage);

        if($response[0] == 0)
            http_response_code(200);            
        else if ($response[0] == 1) 
            http_response_code(500);   
        
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($response[2]);
    }

    public function update(int $messageId)
    {
        $requestBody = json_decode(file_get_contents('php://input'), true);
        if(!($messageId = $requestBody['messageId'] ?? null)){
            http_response_code(400);
            return "No valid messageId found";
        }

        $updatedMessage = new Message();
        if ($senderId = $requestBody['senderId'] ?? null) {
            $updatedMessage->setSender($senderId);
        }
        if ($receiverId = $requestBody['receiverId'] ?? null) {
            $updatedMessage->setReceiver($receiverId);
        }
        if ($content = $requestBody['content'] ?? null) {
        }

        $response = Message::SqlUpdate($messageId, $updatedMessage);
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($response[0] == 0 ? 200 : 400);
        return json_encode($response[2]);
    }
    
    public function delete(int $messageId) {
        $response = Message::SqlDeleteByMessageId($messageId);
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($response[0] == 0 ? 200 : 400);
        return json_encode($response[1]);
    }

}