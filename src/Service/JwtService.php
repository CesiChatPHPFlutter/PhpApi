<?php
namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService{
    public static String $secretKey = "cesichat";

    public static function createToken(array $datas) : String {
        $issuedAt = new \DateTime(); //Date de publication
        $expire = new \DateTime();
        $expire->modify('+6 hours');

        $data = [
          "iat" => $issuedAt->getTimestamp(), // Date création
            "iss" => "cesi.local",
            "nbf" => $issuedAt->getTimestamp(), //Utilisable pas avant ...
            "exp" => $expire->getTimestamp(),
            "datas" => $datas
        ];

        $jwt = JWT::encode($data, self::$secretKey, "HS512");

        return $jwt;

    }

    public static function checkToken($jwt) : array {

        if (! $jwt) {
            $result = [
                "code" => 1,
                "body" => "Aucun jeton n'a pu être extrait de l'en-tête d'autorisation."
            ];
            return $result;
        }

        try{
            //ça remonte une exception dès qu'il trouve une erreur on on veut catch l'erreur pour la donner en JSON
            $token = JWT::decode($jwt, new Key(self::$secretKey, 'HS512'));
        }catch (\Exception$e){
            $result = [
                "code" => 1,
                "body" => "Les données du jeton ne sont pas compatibles : {$e->getMessage()}"
            ];
            return $result;
        }

        $now = new \DateTimeImmutable();
        $serverName = "cesi.local";

        if ($token->iss !== $serverName ||
            $token->nbf > $now->getTimestamp() ||
            $token->exp < $now->getTimestamp())
        {
            $result = [
                "code" => 1,
                "body" => "Les données du jeton ne sont pas compatibles"
            ];
            return $result;
        }

        $result = [
            "code" => 0,
            "body" => "Token OK"
        ];
        return $result;

    }

    public static function decryptToken(string $jwtToken) : ?object {
        try{
            return JWT::decode($jwtToken, new Key(self::$secretKey, 'HS512'))->datas ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

}