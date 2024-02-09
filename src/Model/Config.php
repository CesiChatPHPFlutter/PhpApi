<?php
namespace App\Model;

class Config{
    private static $_config = null;
    public static string $DBHOSTNAME;
    public static string $DBUSERNAME;
    public static string $DBPASSWORD;
    public static string $DBNAME;
    public static string $DBPORT;

    public static function load() {
        if(SELF::$_config == null)
        {
            SELF::$_config = parse_ini_file('' . getcwd() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.ini');
            if(SELF::$_config === false)
            {
                http_response_code(500);
                echo 'config.ini couldn\'t be loaded';
                die();
            }
        }

        SELF::$DBHOSTNAME = SELF::$_config["DBHostname"];
        SELF::$DBUSERNAME = SELF::$_config["DBUsername"];
        SELF::$DBPASSWORD = SELF::$_config["DBPassword"];
        SELF::$DBNAME = SELF::$_config["DBName"];
        SELF::$DBPORT = SELF::$_config["DBPort"];
    }    
}