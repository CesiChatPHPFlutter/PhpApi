<?php
namespace App\Controller;

use App\Model\User;
use App\Model\Message;
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

    public function update() {
        $requestBody = json_decode(file_get_contents('php://input'), true);
        if($userId = $requestBody['userId'] ?? null){}
        else if($jwtToken = $requestBody['jwtToken'] ?? null){

            $check = JwtService::checkToken($jwtToken);
            if($check["code"] == 1)
                http_response_code(401);

            $datas = JwtService::decryptToken($jwtToken);
            if($datas == null || $datas->userId == null) {
                http_response_code(400);
                return "Invalid jwtToken";
            }

            $userId = $datas->userId;
        } else {
            http_response_code(400);
            return "No valid userId found";
        }

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

        $response = User::SqlUpdate($userId, $updatedUser);
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($response[0] == 0 ? 200 : 400);
        return json_encode($response[2]);
    }
    
    public function create() {        
        $requestBody = json_decode(file_get_contents('php://input'), true);     
        
        http_response_code(400);
        
        $newUser = new User();
        if ($name = $requestBody['name'] ?? null) {
            $newUser->setName($name);
        } else return 'Missing Name';
        if ($mail = $requestBody['mail'] ?? null) {
            $newUser->setMail($mail);
        } else return 'Missing Mail';
        if ($password = $requestBody['password'] ?? null) {
            $hashpass = password_hash($password, PASSWORD_BCRYPT);
            $newUser->setPassword($hashpass);
        } else return 'Missing Mail';
        

        $response = User::SqlAdd($newUser);
        if($response[0] == 0)
            http_response_code(200);            
        else if ($response[0] == 1) 
            http_response_code(500);   
        
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($response[2]);
    }

    public function delete(int $userId) {
        $response = User::SqlDelete($userId);
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($response[0] == 0 ? 200 : 400);
        return json_encode($response[1]);
    }

    public function login() {
        $requestBody = json_decode(file_get_contents('php://input'), true);

        $mail = $requestBody['mail'] ?? null;
        $password = $requestBody['password'] ?? null;
        if($mail == null || $password == null) {
            http_response_code(400);
            return "No valid arguments";
        }

        $user = User::SqlGetByMail($mail);
        if($user != null && password_verify($requestBody["password"], $user->getPassword())) {
            header('Content-Type: application/json; charset=utf-8');
            return json_encode([ 
                "user" => $user,
                "jwtToken" => JwtService::createToken([
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

    public function getChatsFromUserToken(){
        $requestBody = json_decode(file_get_contents('php://input'), true);
        $jwtToken = $requestBody['jwtToken'] ?? null;
        if($jwtToken == null) {
            http_response_code(400);
            return "Missing jwtToken";
        }
        
        $check = JwtService::checkToken($jwtToken);
        if($check["code"] == 1)
            http_response_code(401);

        $datas = JwtService::decryptToken($jwtToken);
        if($datas == null || $datas->userId == null) {
            http_response_code(400);
            return "Invalid jwtToken";
        }

        $array = Message::SqlGetChats($datas->userId);

        header('Content-Type: application/json; charset=utf-8');
        return json_encode($array);
    }
}