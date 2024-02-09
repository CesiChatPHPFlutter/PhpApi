<?php
namespace App\Controller;

use App\Model\User;
use App\Service\JwtService;

class UserController {

    public function loginJwt(){
        
        $requestBody = json_decode(file_get_contents('php://input'), true);

        if($_SERVER['REQUEST_METHOD'] != 'POST'){
            header('HTTP/1.1 404 Not Found');
            return json_encode('Erreur de méthode (POST attendu)');
        }

        if(!isset($requestBody['mail']) || !isset($requestBody['password'])){
            header('HTTP/1.1 404 Not Found');
            return json_encode('Erreur il manque des données)');
        }

        $user = User::SqlGetByMail($requestBody['mail']);
        if($user == null){
            return json_encode('Erreur user inconu');
        }

        if (!password_verify($requestBody["password"], $user->getPassword())) {
            return json_encode('Erreur User / Password');
        }

        $token = JwtService::createToken([
        'mail' => $user->getMail(),
        'nomprenom' => $user->getName()
        ]);

        header('Content-Type: application/json; charset=utf-8');
        return json_encode($token);
    }

    public function getAll() {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(User::SqlGetAll());
    }

    public function getById(int $userId) {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(User::SqlGetById($userId));
    }

    public function getByMail(string $mail) {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(User::SqlGetByMail($mail));
    }

    public function update(int $userId) {
        header('Content-Type: application/json; charset=utf-8');

        $requestBody = json_decode(file_get_contents('php://input'));
        
        $updatedUser = new User();

        if ($name = $requestBody['name'] ?? null) {
            $updatedUser->setName($name);
        }
        if ($mail = $requestBody['mail'] ?? null) {
            $updatedUser->setMail($mail);
        }
        if ($password = $requestBody['password'] ?? null) {
            $hashpass = password_hash($password, PASSWORD_BCRYPT);
            $updatedUser->setPassword($hashpass);
        }

        return json_encode(User::SqlUpdate($userId, $updatedUser));
    }
    
    public function create() {
        
        $requestBody = json_decode(file_get_contents('php://input'), true);
        $hashpass = password_hash($requestBody['password'], PASSWORD_BCRYPT);

        $newUser = new User();

        if ($name = $requestBody['name'] ?? null) {
            $newUser->setName($name);
        }
        if ($mail = $requestBody['mail'] ?? null) {
            $newUser->setMail($mail);
        }
        if ($password = $requestBody['password'] ?? null) {
            $hashpass = password_hash($password, PASSWORD_BCRYPT);
            $newUser->setPassword($hashpass);
        }
        
        $response = User::SqlAdd($newUser);
        return json_encode($response[2]);
    }

    public function delete(int $userId) {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(User::SqlDelete($userId));
    }

    public function login() {
        $requestBody = json_decode(file_get_contents('php://input'), true);

        $mail = $requestBody['mail'] ?? null;
        $password = $requestBody['password'] ?? null;

        $user = User::SqlGetByMail($mail);
        if($user != null && password_verify($requestBody["password"], $user->getPassword())) {
            header('Content-Type: application/json; charset=utf-8');
            return json_encode([ 
                "User" => $user,
                "JwtToken" => JwtService::createToken([
                    "userId" => $user->getId(),
                    "mail" => $user->getMail(),
                    "name" => $user->getName()
                ])
            ]);
        }          
        else {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(400);
            return "Mail and/or Password are incorrect";
        }
    }
}