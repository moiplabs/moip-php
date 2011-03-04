<?php

//Autoload function. Only charges a class if they required.
function __autoload($classe){
	include_once "lib/{$classe}.php";
}

?>
