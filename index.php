<?php

/**
  include_once 'examples.php';

  exampleBasicInstructions();
  exampleFull();
  exampleQueryParcels();
 * 
 */
include_once "autoload.inc.php";

$moip = new Moip();
$moip->setEnvironment('test');
$moip->setCredential(array(
    'key' => 'ABABABABABABABABABABABABABABABABABABABAB',
    'token' => '01010101010101010101010101010101'
));

$moip->setUniqueID(false);
$moip->setValue('100.00');
$moip->setReason('Teste do Moip-PHP');

$moip->validate('Basic');

$moip->send();
print_r($moip->getAnswer());

?>
