<?php
namespace App\Model;
use App\Model\User;

class Message implements \JsonSerializable {
    private ?int $MessageId = null;
    private String $Content;
    private User $Sender;
    private User $receiver;
    private string $Timestamp;

    public function getId(): ?int{
        return $this->MessageId;
    }

    public function setId(?int $Id): Message
    {
        $this->MessageId = $Id;
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

    public function getTimestamp(): ?string
    {
        return $this->Timestamp;
    }

    public function setTimestamp(?string $Timestamp): Message
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

            return [0,"OK", $bdd->lastInsertId()];
        }catch (\Exception $e){
            return [1,'NOOK',"{$e->getMessage()}"];
        }
    }

    public static function SqlGetMessagesBetweenUsers(int $userId1, int $userId2): ?array {
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare('SELECT * FROM messages WHERE (sender_id=:userId1 AND receiver_id=:userId2) OR (sender_id=:userId2 AND receiver_id=:userId1) ORDER BY timestamp');
        $requete->execute([
            "userId1"=> $userId1,
            "userId2"=> $userId2
        ]);

        $messages = array();
        while ($messageSql = $requete->fetch(\PDO::FETCH_ASSOC)) {
            $message = new Message();
            $message->setContent($messageSql["content"])
                ->setSender($messageSql["sender_id"])
                ->setReceiver($messageSql["receiver_id"])
                ->setId($messageSql["message_id"])
                ->setTimestamp($messageSql["timestamp"]);
            $messages[] = $message;
        }
        return $messages ?: null;
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
        return $messages ?? null;
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
        return $messages ?? null;
    }

    public static function SqlUpdate($messageId, $updatedMessage) 
    {
        $oldMessage = Message::SqlGetById($messageId);
        if($oldMessage == null)
            return [2, 'NOOK', "Invalid messageId, message #{$userId} does not exist"];

        $bdd = BDD::getInstance();
        try{
            $requete = $bdd->prepare('UPDATE messages SET sender_id=:senderId, receiver_id=:receiverId, content=:content WHERE message_id=:messageId');
            $requete->execute([
                "messageId" => $messageId,
                "content" => ($updatedMessage->getContent() ?? '') ?: $oldMessage->getContent(),
                "senderId" => ($updatedMessage->getSenderId() ?? '') ?: $oldMessage->getSenderId(),
                "receiverId" => ($updatedMessage->getReceiverId() ?? '') ?: $oldMessage->getReceiverId(),
            ]);

            return [0, 'OK', Message::SqlGetById($messageId)];
        } catch(Exception $e) {
            return [1, 'NOOK', Message::SqlGetById($messageId)];
        }
    }

    public function jsonSerialize(): mixed {

        return [
            "message_id" => $this->MessageId,
            "content" => $this->Content,
            "sender_id" => $this->Sender->getId(),
            "receiver_id" => $this->receiver->getId(),
            "timestamp" => $this->Timestamp??null
        ];
    }
  
    public static function SqlGetChats(int $userId): ?array 
    {
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare(
            'SELECT * FROM users WHERE user_id IN (
                SELECT sender_id AS related_user_id
                FROM Messages
                WHERE receiver_id = :userId
                UNION
                SELECT receiver_id AS related_user_id
                FROM Messages
                WHERE sender_id = :userId
            );'
        );
        $requete->execute([
            "userId"=> $userId
        ]);
        
        $users = array();
        while ($userSql = $requete->fetch(\PDO::FETCH_ASSOC)) {
            $user = new User();
            $user ->setId($userSql ["user_id"])
            ->setName($userSql ["name"])
            ->setMail($userSql ["mail"]);
            $users[] = $user;
        }
        return $users ?? null;
    }

    public static function SqlDeleteByMessageId(int $messageId): ?array
    {
        $bdd = BDD::getInstance();
        try{
            $requete = $bdd->prepare('DELETE FROM messages WHERE message_id=:messageId');
            $requete->execute([
                "messageId" => $messageId 
            ]);
            return [0, 'OK'];
        } catch(Exception $e) {
            return [-1, 'NOOK'];
        }

        return $requete;
    }

    public static function SqlDeleteByUserId(int $userId): ?array
    {
        $bdd = BDD::getInstance();
        try{
            $requete = $bdd->prepare('DELETE FROM messages WHERE sender_id=:userId OR receiver_id=:userId');
            $requete->execute([
                "userId" => $userId 
            ]);
            return [0, 'OK'];
        } catch(Exception $e) {
            return [-1, 'NOOK'];
        }

        return $requete;
    }
}