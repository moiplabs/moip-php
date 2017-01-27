<?php

include_once "autoload.inc.php";

function exampleBasicInstructions() {
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
//print_r($moip->getXML());
}

function exampleIdentificationInstruction() {

    $moip = new Moip();
    $moip->setEnvironment('test');
    $moip->setCredential(array('key' => 'ABABABABABABABABABABABABABABABABABABABAB', 'token' => '01010101010101010101010101010101'));

    $moip->setUniqueID(false);
    $moip->setValue('100.00');
    $moip->setReason('Teste do Moip-PHP');

    $moip->setPayer(array('name' => 'Nome Sobrenome',
        'email' => 'email@cliente.com.br',
        'payerId' => 'id_usuario',
        'billingAddress' => array('address' => 'Rua do Zézinho Coração',
            'number' => '45',
            'complement' => 'z',
            'city' => 'São Paulo',
            'neighborhood' => 'Palhaço Jão',
            'state' => 'SP',
            'country' => 'BRA',
            'zipCode' => '01230-000',
            'phone' => '(11)8888-8888')));
    $moip->validate('Identification');

    print_r($moip->send());
}

function exampleQueryParcels() {

$moip = new Moip();
$moip->setEnvironment('test');
$moip->setCredential(array('key' => 'ABABABABABABABABABABABABABABABABABABABAB', 'token' => '01010101010101010101010101010101'));

print_r($moip->queryParcel('integracao@labs.moip.com.br', '4', '1.99', '100.00'));
}

function exampleAddParcel($example='1') {

    $moip = new Moip();
    $moip->setEnvironment('test');
    $moip->setCredential(array('key' => 'ABABABABABABABABABABABABABABABABABABABAB', 'token' => '01010101010101010101010101010101'));
    $moip->setUniqueID(false);
    $moip->setValue('100.00');
    $moip->setReason('Teste do Moip-PHP');

    $moip->setPayer(array('name' => 'Nome Sobrenome',
        'email' => 'email@cliente.com.br',
        'payerId' => 'id_usuario',
        'billingAddress' => array('address' => 'Rua do Zézinho Coração',
            'number' => '45',
            'complement' => 'z',
            'city' => 'São Paulo',
            'neighborhood' => 'Palhaço Jão',
            'state' => 'SP',
            'country' => 'BRA',
            'zipCode' => '01230-000',
            'phone' => '(11)8888-8888')));
    $moip->validate('Identification');

    if ($example == '1')
        $moip->addParcel('2', '12');
    else if ($example == '2')
        $moip->addParcel('2', '12', '1.99');
    else if ($example == '3')
        $moip->addParcel('2', '12', null, true);
    else if ($example == '4') {
        $moip->addParcel('2', '4');
        $moip->addParcel('5', '7', '1.00');
        $moip->addParcel('8', '12', null, true);
    }

    print_r($moip->send());
}

function exampleAddComission($example='1') {
    $moip = new Moip();
    $moip->setEnvironment('test');
    $moip->setCredential(array('key' => 'ABABABABABABABABABABABABABABABABABABABAB', 'token' => '01010101010101010101010101010101'));
    $moip->setUniqueID(false);
    $moip->setValue('100.00');
    $moip->setReason('Teste do Moip-PHP');

    $moip->setPayer(array('name' => 'Nome Sobrenome',
        'email' => 'email@cliente.com.br',
        'payerId' => 'id_usuario',
        'billingAddress' => array('address' => 'Rua do Zézinho Coração',
            'number' => '45',
            'complement' => 'z',
            'city' => 'São Paulo',
            'neighborhood' => 'Palhaço Jão',
            'state' => 'SP',
            'country' => 'BRA',
            'zipCode' => '01230-000',
            'phone' => '(11)8888-8888')));
    $moip->validate('Identification');

    if ($example == '1')
        $moip->addComission('Razão do Split', 'recebedor_secundario', '12.00');
    else if ($example == '2')
        $moip->addComission('Razão do Split', 'recebedor_secundario', '12.00', true);
    else if ($example == '3')
        $moip->addComission('Razão do Split', 'recebedor_secundario', '12.00', true, 'recebedor_secundario_3');
    else if ($example == '4') {
        $moip->addComission('Razão do Split', 'recebedor_secundario', '5.00');
        $moip->addComission('Razão do Split', 'recebedor_secundario', '2.00', true);
        $moip->addComission('Razão do Split', 'recebedor_secundario_2', '12.00', true, 'recebedor_secundario_3');
    }

    print_r($moip->send());
}

function exampleSetReceiver() {
    $moip = new Moip();
    $moip->setEnvironment('test');
    $moip->setCredential(array('key' => 'ABABABABABABABABABABABABABABABABABABABAB', 'token' => '01010101010101010101010101010101'));

    $moip->setUniqueID(false);
    $moip->setValue('100.00');
    $moip->setReason('Teste do Moip-PHP');

    $moip->setPayer(array('name' => 'Nome Sobrenome',
        'email' => 'email@cliente.com.br',
        'payerId' => 'id_usuario',
        'billingAddress' => array('address' => 'Rua do Zézinho Coração',
            'number' => '45',
            'complement' => 'z',
            'city' => 'São Paulo',
            'neighborhood' => 'Palhaço Jão',
            'state' => 'SP',
            'country' => 'BRA',
            'zipCode' => '01230-000',
            'phone' => '(11)8888-8888')));
    $moip->validate('Identification');

    $moip->setReceiver('integracao@labs.moip.com.br');

    print_r($moip->send());
}

function exampleConfigPaymentWay($param) {
    $moip = new Moip();
    $moip->setEnvironment('test');
    $moip->setCredential(array('key' => 'ABABABABABABABABABABABABABABABABABABABAB', 'token' => '01010101010101010101010101010101'));

    $moip->setUniqueID(false);
    $moip->setValue('100.00');
    $moip->setReason('Teste do Moip-PHP');

    $moip->setPayer(array('name' => 'Nome Sobrenome',
        'email' => 'email@cliente.com.br',
        'payerId' => 'id_usuario',
        'billingAddress' => array('address' => 'Rua do Zézinho Coração',
            'number' => '45',
            'complement' => 'z',
            'city' => 'São Paulo',
            'neighborhood' => 'Palhaço Jão',
            'state' => 'SP',
            'country' => 'BRA',
            'zipCode' => '01230-000',
            'phone' => '(11)8888-8888')));
    $moip->validate('Identification');

    $moip->addPaymentWay('creditCard');
    $moip->addPaymentWay('billet');
    $moip->setBilletConf("2011-04-06", true, array("Primeira linha", "Segunda linha", "Terceira linha"), "http://seusite.com.br/logo.gif");

    print_r($moip->send());
}

function exampleFull() {

    $moip = new Moip();
    $moip->setEnvironment('test');
    $moip->setCredential(array('key' => 'ABABABABABABABABABABABABABABABABABABABAB', 'token' => '01010101010101010101010101010101'));
    $moip->setUniqueID(false);
    $moip->setValue('100.00');
    $moip->setReason('Teste do Moip-PHP');

    $moip->setPayer(array('name' => 'Nome Sobrenome',
        'email' => 'email@cliente.com.br',
        'payerId' => 'id_usuario',
        'billingAddress' => array('address' => 'Rua do Zézinho Coração',
            'number' => '45',
            'complement' => 'z',
            'city' => 'São Paulo',
            'neighborhood' => 'Palhaço Jão',
            'state' => 'SP',
            'country' => 'BRA',
            'zipCode' => '01230-000',
            'phone' => '(11)8888-8888')));
    $moip->validate('Identification');

    $moip->setReceiver('integracao@labs.moip.com.br');

    $moip->addParcel('2', '4');
    $moip->addParcel('5', '7', '1.00');
    $moip->addParcel('8', '12', null, true);

    $moip->addComission('Razão do Split', 'recebedor_secundario', '5.00');
    $moip->addComission('Razão do Split', 'recebedor_secundario', '2.00', true);
    $moip->addComission('Razão do Split', 'recebedor_secundario_2', '12.00', true, 'recebedor_secundario_3');

    $moip->addPaymentWay('creditCard');
    $moip->addPaymentWay('billet');
    $moip->addPaymentWay('financing');
    $moip->addPaymentWay('debit');
    $moip->addPaymentWay('debitCard');
    $moip->setBilletConf("2011-04-06", true, array("Primeira linha", "Segunda linha", "Terceira linha"), "http://seusite.com.br/logo.gif");

    print_r($moip->getXML());
}

?>
