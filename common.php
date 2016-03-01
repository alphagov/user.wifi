<?php


spl_autoload_register(function ($class_name)
{
    require ('../class-' . $class_name . '.php'); }
);

?>
