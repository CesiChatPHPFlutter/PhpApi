<?php
namespace src\Controller;

use src\Model\User;
use src\Service\JwtService;

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

    // public function loginJwt(){
    //     header('Content-Type: application/json; charset=utf-8');

    //     if($_SERVER["REQUEST_METHOD"] != "POST"){
    //         header("HTTP/1.1 404 Not Found");
    //         return json_encode("Erreur de méthode (POST attendu)");
    //     }

    //     if(!isset($_POST["mail"]) || !isset($_POST["password"])){
    //         header("HTTP/1.1 404 Not Found");
    //         return json_encode("Erreur il manque des données)");
    //     }

    //     $user = User::SqlGetByMail($_POST["mail"]);
    //     if($user==null){
    //         return json_encode("Erreur user inconu");
    //     }

    //     if (!password_verify($_POST["password"], $user->getPassword())) {
    //         return json_encode("Erreur User / Password");
    //     }

    //     echo JwtService::createToken([
    //        "mail" => $user->getMail(),
    //        "roles" => $user->getRoles(),
    //        "nomprenom" => $user->getNomPrenom()
    //     ]);
    // }

    public function GetAll() {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(User::SqlGetAll());
    }

    public function GetById(int $userId) {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(User::SqlGetById($userId));
    }

    public function GetByMail(string $mail) {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(User::SqlGetByMail($mail));
    }

    public function Update(int $userId) {
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
    
    public function Create() {
        header('Content-Type: application/json; charset=utf-8');
        $requestBody = json_decode(file_get_contents('php://input'));
        
        $newUser = new User();
        if(isset($requestBody->{"Name"}))
            $newUser->setName($requestBody->{"Name"});
        else 
            $newUser->setName("");
        if(isset($requestBody->{"Mail"}))
            $newUser->setMail($requestBody->{"Mail"});
        else 
            $newUser->setMail("");  
        if(isset($requestBody->{"Password"}))
            $newUser->setPassword($requestBody->{"Password"});
        else 
            $newUser->setPassword("");

        return json_encode(User::SqlAdd($newUser));
    }

    public function Delete(int $userId) {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(User::SqlDelete($userId));
    }

    public function Login() {
        $requestBody = json_decode(file_get_contents('php://input'));

        $mail = $requestBody->{"Mail"} ?: "";
        $password = $requestBody->{"Password"} ?: "";

        $user = User::SqlGetByMail($mail);
        if($user != null && $user->getPassword() == $password) {
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