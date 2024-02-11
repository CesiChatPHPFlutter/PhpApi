<?php
namespace App\Model;
use PDO;
use App\Model\Config;
use Exception;

class BDD
{
    private static $_instance = null;
    private function __construct() {}
    private function __clone() {}

    public static function getInstance() : PDO{
        Config::load();
        if(SELF::$_instance == null){
            /* Database Connexion */
             try{
                SELF::$_instance = new PDO(
                    dsn: "mysql:host=".Config::$DBHOSTNAME.";port=".Config::$DBPORT.";dbname=".Config::$DBNAME.";charset=utf8",
                    username: Config::$DBUSERNAME,
                    password: Config::$DBPASSWORD
                );
                 SELF::$_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }catch (Exception $e){
                die("Erreur : {$e->getMessage()}");
            }
        }

        return SELF::$_instance;
    }

    public static function init(){
        $bdd = SELF::getInstance();

        $database_init_query = file_get_contents('' . getcwd() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database_init.sql');
        $eee = $bdd->query($database_init_query);
        $eee->closeCursor();

        $init_data_str = file_get_contents('' . getcwd() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'init_data.json');
        $init_data = json_decode($init_data_str);

        foreach($init_data->users as $user_data){
            $user = new User();

            $user->setName($user_data->name);
            $user->setMail($user_data->mail);
            $hashpass = password_hash($user_data->password, PASSWORD_BCRYPT);
            $user->setPassword($hashpass);

            User::SqlAdd($user);
        }

        foreach($init_data->messages as $message_data){
            $message = new Message();

            $message->setContent($message_data->content);
            $message->setSender($message_data->senderId);
            $message->setReceiver($message_data->receiverId);

            Message::SqlAdd($message);
        }
    }
}