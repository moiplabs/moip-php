<?php

//Autoload function. Only charges a class if they required.
if(!function_exists('classAutoLoader'))
{
    function classAutoLoader($classe)
    {
        include_once "lib/{$classe}.php";
    }
}
spl_autoload_register('classAutoLoader');

?>