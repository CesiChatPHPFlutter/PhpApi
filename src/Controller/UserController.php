<?php
namespace App\Controller;

use App\Model\User;
use App\Service\JwtService;

class UserController extends AbstractController {

    // public function create(){
    //     if(isset($_POST["nomprenom"]) && isset($_POST["mail"]) && isset($_POST["password"]) && isset($_POST["roles"])){
    //         $user = new User();
    //         $hashpass = password_hash($_POST["password"], PASSWORD_BCRYPT, ["cost"=>12]);
    //         $user->setNomPrenom($_POST["nomprenom"])
    //             ->setMail($_POST["mail"])
    //             ->setPassword($hashpass)
    //             ->setRoles($_POST["roles"]);
    //         $result = User::SqlAdd($user);

    //         header("location:/");
    //     }
    //     return $this->getTwig()->render("User/create.html.twig");
    // }

    // public function login(){
    //     if(isset($_POST["mail"]) && isset($_POST["password"])) {
    //         $user = User::SqlGetByMail($_POST["mail"]);
    //         if($user!=null){
    //             //Comparaison des mots de passe
    //             if (password_verify($_POST["password"], $user->getPassword())) {
    //                 $_SESSION["login"] = [
    //                     "mail" => $user->getMail(),
    //                     "nomprenom" => $user->getNomPrenom(),
    //                     "roles" => $user->getRoles()
    //                 ];
    //                 header("location:/Article/all");
    //             } else {
    //                 throw new \Exception("Erreur User/Password");
    //             }
    //         }else{
    //             throw new \Exception("Aucun user avec ce mail");
    //         }
    //     }else{
    //         return $this->getTwig()->render("User/login.html.twig");
    //     }

    // }

    // public static function protect(array $rolescompatibles){
    //     if(!isset($_SESSION["login"]) || !isset($_SESSION["login"]["roles"] )){
    //         throw new \Exception("Vous devez vous authentifier pour acceder à cette page");
    //     }

    //     //Comparaison Role par Role
    //     $rolefound = false;
    //     foreach($_SESSION["login"]["roles"] as $role){
    //         if(in_array($role,$rolescompatibles )){
    //             $rolefound = true;
    //             break;
    //         }
    //     }
    //     if(!$rolefound){
    //         throw new \Exception("Vous n'avez pas les droits d'accéder à cette page");
    //     }

    // }

    // public function logout(){
    //     if(isset($_SESSION["login"])){
    //         unset($_SESSION["login"]);
    //     }
    //     header("location:/");
    // }

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
        // $test = JwtService::checkToken();
        // var_dump($test);

        $requestBody = json_decode(file_get_contents('php://input'));
        
        $updatedUser = new User();
        if(isset($requestBody->{"Name"}))
            $updatedUser->setName($requestBody->{"Name"});
        else 
            $updatedUser->setName("");
        if(isset($requestBody->{"Mail"}))
            $updatedUser->setMail($requestBody->{"Mail"});
        else 
            $updatedUser->setMail("");  
        if(isset($requestBody->{"Password"}))
            $updatedUser->setPassword($requestBody->{"Password"});
        else 
            $updatedUser->setPassword("");

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

        $mail = $requestBody['mail'] ?: "";
        $password = $requestBody['password'] ?: "";

        $user = User::SqlGetByMail($mail);
        if($user != null && password_verify($requestBody["password"], $user->getPassword())) {
            return json_encode([ 
                "User" => $user,
                "JwtToken" => JwtService::createToken([
                    "Mail" => $user->getMail(),
                    "Name" => $user->getName()
                ])
            ]);
        }          
        else return "Mail and/or Password are incorrect";
    }
}