<?php
namespace src\Model;
use src\Model\User;

class Message {
    private ?int $MessageId = null;
    private String $Content;
    private User $Sender;
    private User $Reciever;
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
        $this->Sender = User->SqlGetById($SenderId);
        return $this;
    }

    public function getReciever(): ?User
    {
        return $this->Reciever;
    }

    public function getRecieverId(): ?int
    {
        return $this->Reciever->getId();
    }

    public function setReciever(?int $RecieverId): Message
    {
        $this->Reciever = User->SqlGetById($RecieverId);
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
            $req = $bdd->prepare("INSERT INTO messages (sender_id, reciever_id, content) VALUES(:SenderId, :RecieverId, :Content)");
            $req->execute([
                "SenderId" => $message->getSenderId(),
                "RecieverId" => $message->getRecieverId(),
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
                ->setReciever($messageSql ["reciever_id"])
                ->setId($messageSql ["id"]);
            $messages[] = $message;
        }
        return $messages ?: null;
    }

    public static function SqlGetByRecieverId(int $recieverId) : ?array{
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare('SELECT * FROM messages WHERE reciever_id=:recieverId');
        $requete->execute([
            "reciever_id"=> $recieverId
        ]);
        
        $messages = array();
        while ($messageSql = $requete->fetch(\PDO::FETCH_ASSOC)) {
            $message = new Message();
            $message ->setContent($messageSql ["content"])
                ->setSender($messageSql ["sender_id"])
                ->setReciever($messageSql ["reciever_id"])
                ->setId($messageSql ["id"]);
            $messages[] = $message;
        }
        return $messages ?: null;
    }
}