<?php

/**
 * Abstração da API do MoIP para PHP
 * @author Herberth Amaral
 * @version 0.4.3
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

    private $instituicoes = array('moip'=>'MoIP',
        'visa'=>'Visa',
        'american_express'=>'AmericanExpress',
        'mastercard'=>'Mastercard',
        'diners'=>'Diners',
        'banco_brasil'=>'BancoDoBrasil',
        'bradesco'=>'Bradesco',
        'itau'=>'Itau',
        'real'=>'BancoReal',
        'unibanco'=>'Unibanco',
        'aura'=>'Aura',
        'hipercard'=>'Hipercard',
        'paggo'=>'Paggo', //oi paggo
        'banrisul'=>'Banrisul'
    ); 

    private $tipo_frete = array('proprio'=>'Proprio','correios'=>'Correios');

    private $tipo_prazo = array('corridos'=>'Corridos','uteis'=>'Uteis');

    private $forma_pagamento = array();
    private $forma_pagamento_args;
    private $tipo_pagamento = 'Unico';
    private $pagador;
    var $resposta;
    private $valor;

    //simplexml object
    private $xml;

    function __construct()
    {
        $this->initXMLObject();
    }

    private function initXMLObject()
    {
        $this->xml = new SimpleXmlElement('<EnviarInstrucao></EnviarInstrucao>');
        $this->xml->addChild('InstrucaoUnica');
    }
    
    public function setTipoPagamento($tipo)
    {
        if ($tipo=='Unico' || $tipo=='Direto') {
            $this->tipo_pagamento = $tipo;
        }
        return $this;
    }

    public function setPagamentoDireto($params)
    {
        if (!isset($params['forma']))
            throw new InvalidArgumentException("Você deve especificar a forma de pagamento em setPagamentoDireto.");
        

        if ( 
            ($params['forma']=='debito' or $params['forma']=='cartao_credito') 
            and 
            (!isset($params['instituicao']) or !isset($this->instituicoes[$params['instituicao']]))

        ) 
        {
            throw new InvalidArgumentException("Você deve especificar uma instituição de pagamento válida quando".
                " a forma de forma de pagamento é via débito ou cartao");
        }

        if ($params['forma'] == 'cartao_credito' and
            (!isset($params['cartao']) or
            !isset($params['cartao']['numero']) or
            !isset($params['cartao']['expiracao']) or
            !isset($params['cartao']['codigo_seguranca']) or
            !isset($params['cartao']['portador']) or
            !isset($params['cartao']['portador']['nome']) or
            !isset($params['cartao']['portador']['identidade_numero']) or
            !isset($params['cartao']['portador']['identidade_tipo']) or
            !isset($params['cartao']['portador']['telefone']) or
            !isset($params['cartao']['portador']['data_nascimento']) or
            !isset($params['cartao']['parcelamento']) or
            !isset($params['cartao']['parcelamento']['parcelas']) or
            !isset($params['cartao']['parcelamento']['recebimento']) 
           )
          )
        {
            throw new InvalidArgumentException("Os dados do cartão foram passados de forma incorreta.");
        }

        $pd = $this->xml->InstrucaoUnica->addChild('PagamentoDireto');
        
        $pd->addChild('Forma',$this->formas_pagamento[$params['forma']]);

        if ($params['forma']=='debito' or $params['forma']=='cartao_credito')
        {
            $pd->addChild('Instituicao',$this->instituicoes[$params['instituicao']]);
        }

        if ($params['forma']=='cartao_credito')
        {
            $cartao = $pd->addChild('CartaoCredito');
            $cartao->addChild('Numero',$params['cartao']['numero']);
            $cartao->addChild('Expiracao',$params['cartao']['expiracao']);
            $cartao->addChild('CodigoSeguranca',$params['cartao']['codigo_seguranca']);

            $portador = $cartao->addChild('Portador');
            $portador->addChild('Nome',$params['cartao']['portador']['nome']);
            $portador->addChild('Identidade',$params['cartao']['portador']['identidade_numero'])
                     ->addAttribute('tipo',$params['cartao']['portador']['identidade_tipo']);

            $parcelamento = $cartao->addChild('Parcelamento');
            $parcelamento->addChild('Parcelas',$params['cartao']['parcelamento']['parcelas']);
            $parcelamento->addChild('Recebimento',$params['cartao']['parcelamento']['recebimento']);
        }

        $this->tipo_pagamento = 'Direto';
        return $this;
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
        if (!isset($this->credenciais)  or
            !isset($this->razao) or
            !isset($this->id_proprio))
            throw new InvalidArgumentException("Dados requeridos não preenchidos. Você deve especificar as credenciais, a razão do pagamento e seu ID próprio");

        $pagador = $this->pagador;
        
        if ($this->tipo_pagamento=='Direto') {

            if(  empty($pagador) or
                !isset($pagador['nome']) or
                !isset($pagador['email']) or 
                !isset($pagador['celular']) or
                !isset($pagador['apelido']) or
                !isset($pagador['identidade']) or
                !isset($pagador['endereco']) or
                !isset($pagador['endereco']['logradouro']) or
                !isset($pagador['endereco']['numero']) or
                !isset($pagador['endereco']['complemento']) or
                !isset($pagador['endereco']['bairro']) or
                !isset($pagador['endereco']['cidade']) or
                !isset($pagador['endereco']['estado']) or
                !isset($pagador['endereco']['pais']) or
                !isset($pagador['endereco']['cep']) or
                !isset($pagador['endereco']['telefone'])
            )
            {
                throw new InvalidArgumentException("Dados do pagador especificados de forma incorreta");
            }
        }

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

    public function addFormaPagamento($forma,$args=null)
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
        $this->forma_pagamento[] = $forma;
        return $this; 
    }

    public function setPagador($pagador)
    {
        $this->pagador = $pagador;
        return $this;
    }

    public function setValor($valor)
    {
        $this->valor = $valor;
        return $this;
    }

    public function setAcrescimo($valor)
    {
        $this->acrescimo = $valor;
        return $this;
    }

    public function setDeducao($valor)
    {
        $this->deducao = $valor;
        return $this;
    }

    public function addMensagem($msg)
    {
        if(!isset($this->xml->InstrucaoUnica->Mensagens))
        {
            $this->xml->InstrucaoUnica->addChild('Mensagens');
        }

        $this->xml->InstrucaoUnica->Mensagens->addChild('Mensagem',$msg);
        return $this;
    }

    public function setUrlRetorno($url)
    {
        if (!isset($this->xml->InstrucaoUnica->URLRetorno))
        {
            $this->xml->InstrucaoUnica->addChild('URLRetorno',$url);
        }
    }

    public function setUrlNotificacao($url)
    {
        if (!isset($this->xml->InstrucaoUnica->URLNotificacao))
        {
            $this->xml->InstrucaoUnica->addChild('URLNotificacao',$url);
        }
    }

    public function addComissao($param)
    {
        if (!isset($param['login_moip']))
            throw new InvalidArgumentException('Você deve especificar um usuário para comissionar.');

        if (!isset($param['valor_fixo']) or !isset($param['valor_percentual']))
            throw new InvalidArgumentException('Você deve especificar um tipo de valor para comissionar.');

        if (isset($param['valor_fixo']) and isset($param['valor_percentual']))  
            throw new InvalidArgumentException('Você deve especificar somente um tipo de valor de comissão');

        if (!isset($this->xml->InstrucaoUnica->Comissoes))
            $this->xml->InstrucaoUnica->addChild('Comissoes');

        if (isset($param['valor_fixo']))
        {
            $node = $this->xml->InstrucaoUnica->Comissoes->addChild('Comissao');
            $node->addChild('Comissionado')->addChild('LoginMoIP',$param['login_moip']);
            $node->addChild('ValorFixo',$param['valor_fixo']);
        }
        else
        {
            $node = $this->xml->InstrucaoUnica->Comissoes->addChild('Comissao');
            $node->addChild('Comissionado')->addChild('LoginMoIP',$param['login_moip']);
            $node->addChild('ValorPercentual',$param['valor_percentual']);
        }
    }

    public function addParcela($min,$max,$juros='')
    {
        if (!isset($this->xml->InstrucaoUnica->Parcelamentos))
        {
            $this->xml->InstrucaoUnica->addChild('Parcelamentos');
        }

        $parcela = $this->xml->InstrucaoUnica->Parcelamentos->addChild('Parcelamento');
        $parcela->addChild('MinimoParcelas',$min);
        $parcela->addChild('MaximoParcelas',$max);
        $parcela->addChild('Recebimento','AVista');

        if (!empty($juros))
        {
            $parcela->addChild('Juros',$min);
        }

        return $this;
    }

    public function addEntrega($params)
    {
        //validações dos parâmetros de entrega

        if (empty($params) or !isset($params['tipo']) or !isset($params['prazo'])) 
        {
            throw new InvalidArgumentException('Você deve especificar o tipo de frete (proprio ou correios) e o prazo de entrega');
        }

        if (!isset($this->tipo_frete[$params['tipo']]))
        {
            throw new InvalidArgumentException('Tipo de frete inválido. Opções válidas: "proprio" ou "correios"');
        }

        if (is_array($params['prazo']))
        { 
            if (is_array($params['prazo']) and !isset($this->tipo_prazo[$params['prazo']['tipo']]))
            {
                throw new InvalidArgumentException('Tipo de prazo de entrega inválido. Opções válidas: "uteis" ou "corridos".');
            }

            if (!isset($params['prazo']['dias']))
            {
                throw new InvalidArgumentException('Você deve especificar os dias do prazo de entrega');
            }
        }

        if ($params['tipo']=='correios')   
        {
            if ((!isset($params['correios']) or empty($params['correios'])) )
            {
                throw new InvalidArgumentException('É necessário especificar os '.
                    'parâmetros dos correios quando o '.
                    'tipo de frete é Correios');

            }

            if (!isset($params['correios']['peso']) or !isset($params['correios']['forma_entrega']))
            {
                throw new InvalidArgumentException('É necessário passar os parâmetros'.
                    ' dos correios quando a forma de envio são os Correios');
            }

        }
        else
        {
            if (!isset($params['valor_fixo']) and !isset($params['valor_percentual']))
                throw new InvalidArgumentException('Você deve especificar valor_fixo ou valor_percentual quando o tipo de frete é próprio');
        }

        //fim das validações
        if (!isset($this->xml->InstrucaoUnica->Entrega))
        {
            $this->xml->InstrucaoUnica->addChild('Entrega')->addChild('Destino','MesmoCobranca');
        }

        $entrega = $this->xml->InstrucaoUnica->Entrega;
        $calculo_frete = $entrega->addChild('CalculoFrete');
        $calculo_frete->addChild('Tipo',$this->tipo_frete[$params['tipo']]);

        $calculo_frete->addChild('Prazo',$params['prazo']['dias'])
            ->addAttribute('Tipo',$this->tipo_prazo[$params['prazo']['tipo']]);

        if ($params['tipo']=='proprio')
        {
            if (isset($params['valor_fixo']))
                $calculo_frete->addChild('ValorFixo',$params['valor_fixo']);
            else 
                $calculo_frete->addChild('ValorPercentual',$params['valor_percentual']);
        }
        else
        {
            $correios = $calculo_frete->addChild('Correios');
            $correios->addChild('PesoTotal',$params['correios']['peso']);
            $correios->addChild('FormaEntrega',$params['correios']['forma_entrega']);
        }

        return $this;
    }

    public function getXML()
    {
        $this->xml->InstrucaoUnica->addChild('IdProprio' , $this->id_proprio);
        $this->xml->InstrucaoUnica->addChild('Razao' , $this->razao);

        if (empty($this->valor))
            throw new InvalidArgumentException('Erro: o valor da transação deve ser especificado');

        $this->xml->InstrucaoUnica->addChild('Valores')
            ->addChild('Valor',$this->valor)
            ->addAttribute('moeda','BRL'); 

        if (isset($this->deducao))
        {
            $this->xml->InstrucaoUnica->Valores->addChild('Deducao',$this->deducao)
                ->addAttribute('moeda','BRL');
        }

        if (isset($this->acrescimo))
        {
            $this->xml->InstrucaoUnica->Valores->addChild('Acrescimo',$this->acrescimo)
                ->addAttribute('moeda','BRL');
        }

        if (!empty($this->forma_pagamento))
        {
            $instrucao = $this->xml->InstrucaoUnica;
            $formas = $instrucao->addChild('FormasPagamento');

            foreach ($this->forma_pagamento as $forma)
            {

                $formas->addChild('FormaPagamento',$this->formas_pagamento[$forma]);

                if($forma == 'boleto' and !empty($this->forma_pagamento_args))
                {
                    $instrucao->addChild('Boleto')
                        ->addChild('DiasExpiracao',$this->forma_pagamento_args['dias_expiracao']['dias'])
                        ->addAttribute('Tipo',$this->forma_pagamento_args['dias_expiracao']['tipo']);

                    if(isset($this->forma_pagamento_args['instrucoes']))
                    {
                        $numeroInstrucoes = 1;
                        foreach($this->forma_pagamento_args['instrucoes'] as $instrucaostr)
                        {
                            $instrucao->Boleto->addChild('Instrucao'.$numeroInstrucoes,$instrucaostr);
                            $numeroInstrucoes++;
                        }
                    }
                }

            }
        }

        if(!empty($this->pagador))
        {
            $p = $this->pagador;
            $this->xml->InstrucaoUnica->addChild('Pagador');
            (isset($p['nome']))?$this->xml->InstrucaoUnica->Pagador->addChild( 'Nome' , $this->pagador[ 'nome' ] ):null;
            (isset($p['login_moip']))?$this->xml->InstrucaoUnica->Pagador->addChild( 'LoginMoIP' , $this->pagador[ 'login_moip' ] ):null;
            (isset($p['email']))?$this->xml->InstrucaoUnica->Pagador->addChild( 'Email' , $this->pagador['email']):null;
            (isset($p['celular']))?$this->xml->InstrucaoUnica->Pagador->addChild( 'TelefoneCelular' , $this->pagador['celular']):null;
            (isset($p['apelido']))?$this->xml->InstrucaoUnica->Pagador->addChild( 'Apelido' , $this->pagador['apelido']):null;
            (isset($p['identidade']))?$this->xml->InstrucaoUnica->Pagador->addChild( 'Identidade' , $this->pagador['identidade']):null;

            $p = $this->pagador['endereco'];
            $this->xml->InstrucaoUnica->Pagador->addChild( 'EnderecoCobranca' );
            (isset($p['endereco']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'Logradouro' , $this->pagador['endereco']['logradouro']):null;
            
            (isset($p['endereco']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'Numero' , $this->pagador['endereco']['numero']):null;

            (isset($p['endereco']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'Complemento' , $this->pagador['endereco']['complemento']):null;

            (isset($p['endereco']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'Bairro' , $this->pagador['endereco']['bairro']):null;

            (isset($p['endereco']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'Cidade' , $this->pagador['endereco']['cidade']):null;

            (isset($p['endereco']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'Estado' , $this->pagador['endereco']['estado']):null;

            (isset($p['endereco']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'Pais' , $this->pagador['endereco']['pais']):null;

            (isset($p['endereco']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'CEP' , $this->pagador['endereco']['cep']):null;

            (isset($p['endereco']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'TelefoneFixo' , $this->pagador['endereco']['telefone']):null;

        }

        $return = $this->xml->asXML();
        $this->initXMLObject();
        return str_ireplace("\n","",$return);
    }

    public function envia($client=null)
    {
        $this->valida();

        if($client==null)
            $client = new MoIPClient();

        if ($this->ambiente=='sandbox')
            $url = 'https://desenvolvedor.moip.com.br/sandbox/ws/alpha/EnviarInstrucao/Unica';
        else
            $url = 'https://www.moip.com.br/ws/alpha/EnviarInstrucao/Unica';

        $this->resposta = $client->send($this->credenciais['token'].':'.$this->credenciais['key'],
            $this->getXML(),
            $url);
        return $this;
    }

    public function getResposta()
    {
        if (!empty($this->resposta->erro))
            return (object) array('sucesso'=>false,'mensagem'=>$this->resposta->erro);

        $xml = new SimpleXmlElement($this->resposta->resposta);
        $return = (object) array();
        $return->sucesso = (bool)$xml->Resposta->Status=='Sucesso';
        $return->id      = (string)$xml->Resposta->ID;
        $return->token = (string)$xml->Resposta->Token;

        if ($this->ambiente == 'sandbox')
            $return->url_pagamento = "https://desenvolvedor.moip.com.br/sandbox/Instrucao.do?token=".$return->token;
        else
            $return->url_pagamento = "https://www.moip.com.br/sandbox/Instrucao.do?token=".$return;

        return $return;
    }

    public function checarPagamentoDireto($login_moip,$client=null)
    {
        if (!isset($this->credenciais))
            throw new Exception("Você deve especificar as credenciais (token/key) da API antes de chamar este método");

        if ($client==null) {
            $client = new MoIPClient();
        }

        $url = "https://www.moip.com.br/ws/alpha/ChecarPagamentoDireto/$login_moip";
        $resposta = $client->send($this->credenciais['token'].':'.$this->credenciais['key'],'',$url,'GET');
        $xml = new SimpleXmlElement($resposta->resposta);

        return (object)array(
            'erro'=>$resposta->erro,
            'id'=>(string)$xml->Resposta->ID,
            'sucesso'=>$xml->Resposta->Status=='Sucesso',
            'carteira_moip'=>$xml->Resposta->CarteiraMoIP=='true',
            'cartao_credito'=>$xml->Resposta->CartaoCredito=='true',
            'cartao_debito'=>$xml->Resposta->CartaoDebito=='true',
            'debito_bancario'=>$xml->Resposta->DebitoBancario=='true',
            'financiamento_bancario'=>$xml->Resposta->FinanciamentoBancario=='true',
            'boleto_bancario'=>$xml->Resposta->BoletoBancario=='true',
            'debito_automatico'=>$xml->Resposta->DebitoAutomatico=='true');
    }

    public function checarValoresParcelamento($login_moip,$total_parcelas,$juros,$valor_simulado,$client=null)
    {
        if (!isset($this->credenciais)) {
            throw new Exception("Você deve especificar as credenciais (token/key) da API antes de chamar este método");
        }

        if ($client==null) {
            $client = new MoIPClient();
        }

        $url = "https://www.moip.com.br/ws/alpha/ChecarValoresParcelamento/$login_moip/$total_parcelas/$juros/$valor_simulado";
        $resposta = $client->send($this->credenciais['token'].':'.$this->credenciais['key'],'',$url,'GET');
        $xml = new SimpleXmlElement($resposta->resposta);

        $return = array('sucesso'=>(bool)$xml->Resposta->Status=='sucesso',
            'id'=>(string)$xml->Resposta->ID,
            'parcelas'=>array());

        $i = 1;

        foreach($xml->Resposta->ValorDaParcela as $parcela)
        {
            $attrib = $parcela->attributes();
            $return['parcelas']["$i"] = array('total'=>(string)$attrib['Total'],'juros'=>(string)$attrib['Juros'],'valor'=>(string)$attrib['Valor']);
            $i++;
        }    

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
    function send_without_curl($credentials, $xml, $url='http://desenvolvedor.moip.com.br/sandbox/ws/alpha/EnviarInstrucao/Unica',$method='POST')
    {  
        $auth = base64_encode($credentials);
        $url = str_replace('https','http',$url); 

        $header[] = "Authorization: Basic " . $auth;


        $params = array('http' => array(
            'method' => $method,
            'content' => $xml,
            'header'=>$header
        ));
        $ctx = stream_context_create($params);
        $fp = fopen($url, 'r', false, $ctx);

        $response = stream_get_contents($fp);
        if ($response === false) {
            throw new Exception("Problemas ao ler dados de $url, $php_errormsg");
        } 
        return (object)array('resposta'=>$response,'erro'=>null);
    }

    function send($credentials,$xml,$url='https://desenvolvedor.moip.com.br/sandbox/ws/alpha/EnviarInstrucao/Unica',$method='POST')
    {  
        $header[] = "Authorization: Basic " . base64_encode($credentials);
        if (!function_exists('curl_init'))
            return $this->send_without_curl($credentials, $xml, $url);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_USERPWD, $credentials);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0");

        $method=='POST'?curl_setopt($curl, CURLOPT_POST, true):null;

        $xml!=''?curl_setopt($curl, CURLOPT_POSTFIELDS, $xml):null;
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($curl);
        $err = curl_error($curl); 
        curl_close($curl); 
        return (object) array('resposta'=>$ret,'erro'=>$err);
    }

}
?>
