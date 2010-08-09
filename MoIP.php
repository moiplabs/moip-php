<?php
require 'xml2array.php';
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
  private $ambiente = 'sandbox';
  private $id_proprio;
  private $formas_pagamento = array('boleto'=>'BoletoBancario',
                                    'financiamento'=>'FinanciamentoBancario',
                                    'debito'=>'DebitoBancario',
                                    'cartao_credito'=>'CartaoCredito',
                                    'cartao_debito'=>'CartaoDebito',
                                    'carteira_moip'=>'CarteiraMoIP');
  private $forma_pagamento;
  private $forma_pagamento_args;
  private $resposta;
  private $valor;

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

  public function setFormaPagamento($forma,$args=null)
  {
    if(!isset($this->formas_pagamento[$forma]))
      throw new InvalidArgumentException("Forma de pagamento indisponivel");

    if($args!=null)
    {
      if (!is_array($args))
        throw InvalidArgumentException("Os parâmetros extra devem ser passados em um array");

      if($forma=='boleto')
      { 
        //argumentos possíveis: dias de expiração, instruções e logo da URL
        if (isset($args['dias_expiracao']) and isset($args['dias_expiracao']['tipo']) and isset($args['dias_expiracao']['dias']))
        {
            $this->forma_pagamento_args = $args;
        }
        else
        {
          throw new InvalidArgumentException("Parâmetros passados de forma incorreta");
        }
      }
    }
    $this->forma_pagamento = $forma;
    return $this; 
  }

  public function setValor($valor)
  {
    $this->valor = $valor;
    return $this;
  }

  public function getXML()
  {
    $xml = "<EnviarInstrucao><InstrucaoUnica><Razao>".$this->razao."</Razao><IdProprio>".$this->id_proprio."</IdProprio>";
    if (!empty($this->valor))
    {
      $xml .='<Valores><Valor moeda="BRL">'.$this->valor.'</Valor></Valores>';
    }

    if (!empty($this->forma_pagamento))
    {
      $xml .= '<PagamentoDireto><Forma>'.$this->formas_pagamento[$this->forma_pagamento].'</Forma></PagamentoDireto>';

      if($this->forma_pagamento=='boleto' and !empty($this->forma_pagamento_args))
      {
        $xml .= '<Boleto><DiasExpiracao Tipo="'.$this->forma_pagamento_args['dias_expiracao']['tipo'].'">'.
                $this->forma_pagamento_args['dias_expiracao']['dias'].'</DiasExpiracao>';

        if(isset($this->forma_pagamento_args['instrucoes']))
        {
          $numeroInstrucoes = 1;
          foreach($this->forma_pagamento_args['instrucoes'] as $instrucao)
          {
            $xml .= '<Instrucao'.$numeroInstrucoes.'>'.$instrucao.'</Instrucao'.$numeroInstrucoes.'>';
            $numeroInstrucoes++;
          }
        }

        $xml .= '</Boleto>';
      }
    }

    $xml .= "</InstrucaoUnica></EnviarInstrucao>";
    return $xml; 
  }

  public function envia($client=null)
  {
    $this->valida();
    
    if($client==null)
      $client = new MoIPClient();

    if ($this->ambiente=='sandbox')
      $url = 'https://desenvolvedor.moip.com.br/sandbox/ws/alpha/EnviarInstrucao/Unica';
    else
      $url = 'pegar url depois';

    $this->resposta = $client->send($this->credenciais['token'].':'.$this->credenciais['key'],
                                    $this->getXML(),
                                    $url);
    return $this;
  }

  public function getResposta()
  {
    if (!empty($this->resposta->erro))
      return (object) array('sucesso'=>false,'mensagem'=>$this->resposta->erro);

    $xml = xml2array($this->resposta->resposta);
    $struct = $xml['ns1:EnviarInstrucaoUnicaResponse'];
    $return = (object) array();
    $return->sucesso = $struct['Resposta']['Status']=='Sucesso';
    $return->token = $struct['Resposta']['Token'];
    return $return;
  }
}

/**
 * Cliente HTTP "burro"
 *
 * @author Herberth Amaral
 * @version 0.0.1
 */ 
class MoIPClient
{
  function send($auth,$xml,$url='https://desenvolvedor.moip.com.br/sandbox/ws/alpha/EnviarInstrucao/Unica')
  {
    $header[] = "Authorization: Basic " . base64_encode($auth);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL,$url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_USERPWD, $auth);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0");
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $ret = curl_exec($curl);
    $err = curl_error($curl); 
    curl_close($curl);
    return (object) array('resposta'=>$ret,'erro'=>$err);
  }
}
?>
