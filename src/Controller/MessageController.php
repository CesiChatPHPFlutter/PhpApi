<?php
namespace App\Controller;

use App\Model\Message;
use App\Service\JwtService;

class MessageController extends AbstractController {

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
            $updatedMessage->setName($name);
        }
        if ($receiverId = $requestBody['receiverId'] ?? null) {
            $updatedMessage->setMail($mail);
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