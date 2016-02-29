<?php


spl_autoload_register(function ($class_name)
{
    require ('../class-' . $class_name . '.php'); }
);

function loadconfiguration()
{
    global $configuration;
    $configuration = parse_ini_file("/etc/enrollment.cfg", "TRUE");
}

function check_db_env()
{
    # Environment variables should be set by docker
    # Workaround if run by a different user - loads from files
    if (getenv("DB_HOSTNAME") == "") {
        putenv("DB_HOSTNAME=" . trim(file_get_contents("/etc/DB_HOSTNAME")));
        putenv("DB_USER=" . trim(file_get_contents("/etc/DB_USER")));
        putenv("DB_PASS=" . trim(file_get_contents("/etc/DB_PASS")));
    }
}

function db_connect()
{
    global $dblink;
    check_db_env();
    $dblink = new \PDO('mysql:host=' . getenv('DB_HOSTNAME') .
        '; dbname=radius; charset=utf8mb4', getenv('DB_USER'), getenv('DB_PASS'), array
        (\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_PERSISTENT => false));
}

?>
