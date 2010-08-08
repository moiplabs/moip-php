<?php

/**
 * Abstração da API do MoIP para PHP
 * @author Herberth Amaral
 * @version 0.0.1
 * @package MoIP
 */
class MoIP
{

  private $credenciais;
  private $razao;
  private $ambiente;
  private $id_proprio;
  private $formas_pagamento = array('boleto','financiamento','debito','cartao','carteira_moip');
  private $forma_pagamento;

  function __construct()
  {
    
  }

  public function setCredenciais($credenciais)
  {
    if (!isset($credenciais['token']) or 
       !isset($credenciais['key']) or
       strlen($credenciais['token'])!=32 or
       strlen($credenciais['key'])!=40)
       throw new InvalidArgumentException("Credenciais inválidas");
    
    $this->credenciais = $credenciais;
    return $this;
  }

  public function setAmbiente($ambiente)
  {
    if ($ambiente!='sandbox' and $ambiente!='producao')
      throw new InvalidArgumentException("Ambiente inválido");
    $this->ambiente = $ambiente;
    return $this;
  }

  public function valida()
  {
    if ($this->credenciais == null or
        $this->razao == null or
        $this->id_proprio == null)
        throw new InvalidArgumentException("Dados requeridos não preenchidos. Você deve especificar as credenciais, a razão do pagamento e seu ID próprio");

    return $this;
  }

  public function setIDProprio($id)
  {
    $this->id_proprio = $id;
    return $this;
  }

  public function setRazao($razao)
  {
    $this->razao = $razao;
    return $this;
  }

  public function setFormaPagamento($forma)
  {
    if(!in_array($forma,$this->formas_pagamento))
      throw new InvalidArgumentException("Forma de pagamento indisponivel");
    $this->forma_pagamento = $forma;
    return $this; 
  }

  public function getXML()
  {
    return "<EnviarInstrucao><InstrucaoUnica><Razao>Pagamento de testes</Razao><IdProprio>123456</IdProprio></InstrucaoUnica></EnviarInstrucao>";
  }

  public function envia()
  {
    $this->valida();
    return $this;
  }

  public function getResposta()
  {
    return (object) array('sucesso'=>true,'token'=>'A2J031F0F06810E7E1L9P4R7B5O4F003V3W0Z090H0J080I0Z0J372I352I4');
  }
}

class MoIPClient
{
  function send()
  {

  }
}
?>
