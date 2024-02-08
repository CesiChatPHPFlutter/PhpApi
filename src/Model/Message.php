<?php
namespace App\Model;
use App\Model\User;

class Message {
    private ?int $MessageId = null;
    private String $Content;
    private User $Sender;
    private User $receiver;
    private datetime $Timestamp;

    public function getId(): ?int{
        return $this->Id;
    }

    public function setId(?int $Id): Message
    {
        $this->Id = $Id;
        return $this;
    }

    public function getContent(): ?String
    {
        return $this->Content;
    }

    public function setContent(?String $Content): Message
    {
        $this->Content = $Content;
        return $this;
    }

    public function getSender(): ?User
    {
        return $this->Sender;
    }

    public function getSenderId(): ?int
    {
        return $this->Sender->getId();
    }

    public function setSender(?int $SenderId): Message
    {
        $this->Sender = User::SqlGetById($SenderId);
        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function getReceiverId(): ?int
    {
        return $this->receiver->getId();
    }

    public function setReceiver(?int $receiverId): Message
    {
        $this->receiver = User::SqlGetById($receiverId);
        return $this;
    }

    public function getTimestamp(): ?datetime
    {
        return $this->Timestamp;
    }

    public function setTimestamp(?datetime $Timestamp): Message
    {
        $this->Timestamp = $Timestamp;
        return $this;
    }

    public static function SqlAdd(Message $message) :array{
        $bdd = BDD::getInstance();
        try{
            $req = $bdd->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES(:SenderId, :receiverId, :Content)");
            $req->execute([
                "SenderId" => $message->getSenderId(),
                "receiverId" => $message->getReceiverId(),
                "Content" => $message->getContent(),
            ]);

            return [0,"Insertion OK", $bdd->lastInsertId()];
        }catch (\Exception $e){
            return [1,"ERROR => {$e->getMessage()}"];
        }
    }


    public static function SqlGetBySenderId(int $senderId) : ?array{
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare('SELECT * FROM messages WHERE sender_id=:senderId');
        $requete->execute([
            "senderId"=> $senderId
        ]);

        $messages = array();
        while ($messageSql = $requete->fetch(\PDO::FETCH_ASSOC)) {
            $message = new Message();
            $message ->setContent($messageSql ["content"])
                ->setSender($messageSql ["sender_id"])
                ->setReceiver($messageSql ["receiver_id"])
                ->setId($messageSql ["id"]);
            $messages[] = $message;
        }
        return $messages ?: null;
    }

    public static function SqlGetByReceiverId(int $receiverId) : ?array{
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare('SELECT * FROM messages WHERE receiver_id=:receiverId');
        $requete->execute([
            "receiver_id"=> $receiverId
        ]);
        
        $messages = array();
        while ($messageSql = $requete->fetch(\PDO::FETCH_ASSOC)) {
            $message = new Message();
            $message ->setContent($messageSql ["content"])
                ->setSender($messageSql ["sender_id"])
                ->setReceiver($messageSql ["receiver_id"])
                ->setId($messageSql ["id"]);
            $messages[] = $message;
        }
        return $messages ?: null;
    }

    public static function SqlUpdate($messageId, $updatedMessage) 
    {
        $oldMessage = Message::SqlGetById($messageId);
        if($oldMessage == null)
            return null;

        $bdd = BDD::getInstance();
        try{
            $requete = $bdd->prepare('UPDATE messages SET sender_id=:senderId, receiver_id=:receiverId, content=:content WHERE message_id=:messageId');
            $requete->execute([
                "messageId" => $messageId,
                "content" => $updatedMessage->getContent() ?: $oldMessage->getContent(),
                "senderId" => $updatedMessage->getSenderId() ?: $oldMessage->getSenderId(),
                "receiverId" => $updatedMessage->getReceiverId() ?: $oldMessage->getReceiverId(),
            ]);

            return [0, 'OK', Message::SqlGetById($messageId)];
        } catch(Exception $e) {
            return [-1, 'NOOK', Message::SqlGetById($messageId)];
        }
    }
}