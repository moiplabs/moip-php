<?php
require 'phpQuery.php';

/**
 * Verificação de status da conta do MoIP. Atualmente somente com suporte à verificação de saldo.
 * @author Herberth Amaral
 * @version 0.0.1
 * @package MoIP
 */

class MoIPStatus
{
    private $url_login = "https://www.moip.com.br/j_acegi_security_check";

    function setCredenciais($username,$password)
    {

        $this->username = $username;
        $this->password = $password;
        return $this;
    }

    function getStatus()
    {
        if (!isset($this->username) or !isset($this->password))
            throw new Exception("Usuário ou senha não especificados.");
        

        $ch = curl_init($this->url_login);
        curl_setopt($ch, CURLOPT_POST      ,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS    ,"j_username=$this->username&j_password=$this->password");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
        curl_setopt($ch, CURLOPT_HEADER      ,0);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  
        $page = curl_exec($ch);
        
        if (stristr($page,"Login e senha incorretos"))
            throw new Exception('Login incorreto');
        
        $doc = phpQuery::newDocumentHTML($page);
        
        $saldo = pq('div.textoCinza11 b.textoAzul15');
        
        return (object)array('saldo'=>$saldo->text());
    }
}
?>
