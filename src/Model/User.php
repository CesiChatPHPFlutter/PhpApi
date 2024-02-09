<?php
namespace App\Model;

class User implements \JsonSerializable {
    private ?int $UserId = null;
    private ?String $Name = null;
    private ?String $Mail = null;
    private ?String $Password = null;

    public function getId(): ?int
    {
        return $this->UserId;
    }

    public function setId(?int $UserId): User
    {
        $this->UserId = $UserId;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): User
    {
        $this->Name = $Name;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->Password;
    }

    public function setPassword(string $Password): User
    {
        $this->Password = $Password;
        return $this;
    }

    public function getMail(): string
    {
        return $this->Mail;
    }

    public function setMail(?string $Mail): User
    {
        $this->Mail = $Mail;
        return $this;
    }

    public static function SqlAdd(User $user) :array{
        $u = User::SqlGetByMail($user->getMail());
        if($u != null)
            return [2,"NOOK","User already exist"];

        $bdd = BDD::getInstance();
        try{
            $req = $bdd->prepare("INSERT INTO users (name, mail, password) VALUES(:Name, :Mail, :Password)");
            $req->execute([
                "Name" => $user->getName(),
                "Mail" => $user->getMail(),
                "Password" => $user->getPassword(),
            ]);

            return [0,"OK", User::SqlGetByMail($user->getMail())];
        }catch (\Exception $e){
            return [1,"NOOK","{$e->getMessage()}"];
        }
    }

    public static function SqlGetByMail(string $mail) : ?User{
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare('SELECT * FROM users WHERE Mail=:mail');
        $requete->execute([
            "mail"=> $mail
        ]);

        $userSql = $requete->fetch(\PDO::FETCH_ASSOC);
        if($userSql!= false){
            $user = new User();
            $user ->setMail($userSql ["mail"])
                ->setName($userSql ["name"])
                ->setId($userSql ["user_id"])
                ->setPassword($userSql ["password"]);
            return $user;
        }
        return null;
    }

    public static function SqlGetById(int $userId) : ?User{
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare('SELECT * FROM users WHERE user_id=:userId');
        $requete->execute([
            "userId"=> $userId
        ]);

        $userSql = $requete->fetch(\PDO::FETCH_ASSOC);
        if($userSql!= false){
            $user = new User();
            $user ->setMail($userSql ["mail"])
                ->setName($userSql ["name"])
                ->setId($userSql ["user_id"])
                ->setPassword($userSql ["password"]);
            return $user;
        }
        return null;
    }

    public static function SqlGetAll(): ?array {
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare('SELECT * FROM users');
        $requete->execute();
    
        $users = array();

        while ($userSql = $requete->fetch(\PDO::FETCH_ASSOC)) {
            $user = new User();
            $user->setMail($userSql["mail"])
                 ->setName($userSql["name"])
                 ->setId($userSql["user_id"])
                 ->setPassword($userSql["password"]);
            $users[] = $user;
        }
    
        return $users ?? null;
    }

    public static function SqlDelete(int $userId): array {
        $bdd = BDD::getInstance();
        try{
            $requete = $bdd->prepare('DELETE FROM users WHERE user_id=:userId');
            $requete->execute([
                "userId" => $userId 
            ]);
            return [0, 'OK'];
        } catch(Exception $e) {
            return [-1, 'NOOK'];
        }

        return $requete;
    }

    public static function SqlUpdate(int $userId, User $updatedUser) : ?array {
        $oldUser = User::SqlGetById($userId);
        if($oldUser == null)
            return null;

        $bdd = BDD::getInstance();
        try{
            $requete = $bdd->prepare('UPDATE users SET name=:name, mail=:mail, password=:password WHERE user_id=:userId');
            $requete->execute([
                "userId" => $userId,
                "name" => ($updatedUser->getName() ?? '') ?: $oldUser->getName(),
                "mail" => ($updatedUser->getMail() ?? '') ?: $oldUser->getMail(),
                "password" => ($updatedUser->getPassword() ?? '') ?: $oldUser->getPassword(),
            ]);

            return [0, 'OK', User::SqlGetById($userId)];
        } catch(Exception $e) {
            return [-1, 'NOOK', User::SqlGetById($userId)];
        }
    }

    public function jsonSerialize(): mixed{
        return [
            'userId' => $this->UserId,
            'name' => $this->Name,
            'mail' => $this->Mail,
        ];
    }
}