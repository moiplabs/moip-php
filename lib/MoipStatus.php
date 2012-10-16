<?php
require 'phpQuery/phpQuery.php';

/**
 * Verificação de status da conta do MoIP. Atualmente somente com suporte à verificação de saldo e ultimas transações.
 * @author Herberth Amaral
 * @version 0.0.2
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $page = curl_exec($ch);
        $error = curl_error($ch);

        if (!empty($error))
        {
            $errno = curl_errno($ch);
            throw new Exception("Ooops, ocorreu um erro ao tentar recuperar os status #$errno: $error");
        }

        if (stristr($page,"Login e senha incorretos"))
            throw new Exception('Login incorreto');

        $doc = phpQuery::newDocumentHTML($page);

        $this->saldo = pq('div.textoCinza11 b.textoAzul15')->text();
        $this->saldo_a_receber = pq('div.textoCinza11 b.textoAzul11')->text();

        $this->ultimas_transacoes = $this->getLastTransactions($page);

        return $this;
    }

    private function getLastTransactions($page)
    {
        $doc = phpQuery::newDocumentHTML($page);

        $selector = 'div.conteudo>div:eq(1)>div:eq(1)>div:eq(1)>div:eq(0) div.box table[cellpadding=5]>tbody tr';

        if (substr(utf8_encode(pq($selector)->find('td:eq(0)')->html()),0,7)=="Nenhuma")
            return null;

        $ultimas_transacoes = array();
        foreach(pq($selector) as $tr)
        {
            $tds = pq($tr);

            $transacao = array('data'=>$tds->find('td:eq(0)')->html(),
                'nome'=>$tds->find('td:eq(1)')->html(),
                'pagamento'=>$tds->find('td:eq(2)')->html(),
                'adicional'=>$tds->find('td:eq(3)')->html(),
                'valor'=>$tds->find('td:eq(4)')->html()
            );
            $ultimas_transacoes[] = $transacao;
        }

        return $ultimas_transacoes;
    }
}