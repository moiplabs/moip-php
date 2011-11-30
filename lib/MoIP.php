<?php
/**
 * Library to help PHP users of MoIP's API
 *
 * @author Herberth Amaral
 * @author Wesley Willians
 * @author Alê Borba
 * @version 1.0
 * @license <a href="http://www.opensource.org/licenses/bsd-license.php">BSD License</a>
 */
/**
 * MoIP's API abstraction class
 *
 * Class to use for all abstraction of MoIP's API
 * @package MoIP
 */
class MoIP
{
    /**
     * Associative array with two keys. 'key'=>'your_key','token'=>'your_token'
     *
     * @var array
     * @access private
     */
    private $credential;
    /**
     * Define the payment's reason
     *
     * @var string
     * @access private
     */
    private $reason;
    /**
     * The application's environment
     *
     * @var string
     * @access private
     */
    private $environment;
    /**
     * Transaction's unique ID
     *
     * @var string
     * @access private
     */
    private $uniqueID;
    /**
     * Associative array of payment's way
     *
     * @var array
     * @access private
     */
    private $payment_ways = array('boleto'=>'BoletoBancario',
        'financiamento'=>'FinanciamentoBancario',
        'debito'=>'DebitoBancario',
        'cartao_credito'=>'CartaoCredito',
        'cartao_debito'=>'CartaoDebito',
        'carteira_moip'=>'CarteiraMoIP');
    /**
     * Associative array of payment's institutions
     *
     * @var array
     * @access private
     */
    private $institution = array('moip'=>'MoIP',
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
    /**
     * Associative array of delivery's type
     *
     * @var array
     * @access private
     */
    private $delivery_type = array('proprio'=>'Proprio','correios'=>'Correios');
    /**
     * Associative array with type of delivery's time
     *
     * @var array
     * @access private
     */
    private $delivery_type_time = array('corridos'=>'Corridos','uteis'=>'Uteis');
    /**
     * Payment method
     *
     * @var array
     * @access private
     */
    private $payment_method;
    /**
     * Arguments of payment method
     *
     * @var array
     * @access private
     */
    private $payment_method_args;
    /**
     * Payment's type
     *
     * @var string
     * @access private
     */
    private $payment_type;
    /**
     * Associative array with payer's information
     *
     * @var array
     * @access private
     */
    private $payer;
    /**
     * Server's answer
     *
     * @var object
     * @access public
     */
    public $answer;
    /**
     * The transaction's value
     *
     * @var numeric
     * @access private
     */
    private $value;
    /**
     * Simple XML object
     *
     * @var object
     * @access private
     */
    private $xml;

    /**
     * Method construct
     *
     * @return void
     * @access public
     */
    public function __construct()
    {
	//Verify the environment variable, if not 'producao' set 'sandbox'
	if($this->environment != 'producao')
	{
		$this->environment = 'sandbox';
	}

	//Verify the payment's type, if null set 'Unico'
	if(!$this->payment_type)
	{
		$this->payment_type = 'Unico';
	}

        $this->initXMLObject();
    }

    /**
     * Method initXMLObject()
     *
     * Start a new XML structure for the requests
     *
     * @return void
     * @access private
     */
    private function initXMLObject()
    {
        $this->xml = new SimpleXmlElement('<EnviarInstrucao></EnviarInstrucao>');
        $this->xml->addChild('InstrucaoUnica');
    }

    /**
     * Method setPaymentType()
     *
     * Define the payment's type between 'Unico' or 'Direto'
     *
     * @param string $tipo Can be 'Unico' or 'Direto'
     * @return void
     * @access public
     */
    public function setPaymentType($tipo)
    {
 	//Verify if the value of variable $tipo is between 'Unico' or 'Direto'. If not, throw new exception error
        if ($tipo=='Unico' || $tipo=='Direto') {
            $this->payment_type = $tipo;
        }
	else
	{
		throw new Exception("A variável tipo deve conter os valores 'Unico' ou 'Direto'");
	}

        return $this;
    }

    /**
     * Method setPagamentoDireto()
     *
     * Especify the transaction will be done using the MoIP's PagamentoDireto
     *
     * @param array $params The PagamentoDireto data information
     * @return void
     * @access public
     */
    public function setPagamentoDireto($params)
    {
        if (!isset($params['forma']))
            throw new InvalidArgumentException("Você deve especificar a forma de pagamento em setPagamentoDireto.");


        if (
            ($params['forma']=='debito' or $params['forma']=='cartao_credito')
            and
            (!isset($params['instituicao']) or !isset($this->institution[$params['instituicao']]))

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

        $pd->addChild('Forma',$this->payment_ways[$params['forma']]);

        if ($params['forma']=='debito' or $params['forma']=='cartao_credito')
        {
            $pd->addChild('Instituicao',$this->institution[$params['instituicao']]);
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

        $this->payment_type = 'Direto';
        return $this;
    }

    /**
     * Method setCredential()
     *
     * Set the credentials(key,token) required for the API authentication.
     *
     * @param array $credential Array with the credentials token and key
     * @return void
     * @access public
     */
    public function setCredential($credential)
    {
        if (!isset($credential['token']) or
            !isset($credential['key']) or
            strlen($credential['token'])!=32 or
            strlen($credential['key'])!=40)
            throw new InvalidArgumentException("credential inválidas");

        $this->credential = $credential;
        return $this;
    }

    /**
     * Method setEnvironment()
     *
     * Define the environment for the API utilization.
     *
     * @param string $environment Only two values supported, 'sandbox' or 'producao'
     */
    public function setEnvironment($environment)
    {
        if ($environment!='sandbox' and $environment!='producao')
            throw new InvalidArgumentException("Ambiente inválido");

        $this->environment = $environment;
        return $this;
    }

    /**
     * Method validate()
     *
     * Make the data validation
     *
     * @return void
     * @access public
     */
    public function validate()
    {
        if (!isset($this->credential)  or
            !isset($this->reason) or
            !isset($this->uniqueID))
            throw new InvalidArgumentException("Dados requeridos não preenchidos. Você deve especificar as credenciais, a razão do pagamento e seu ID próprio");

        $payer = $this->payer;

        if ($this->payment_type=='Direto') {

            if(  empty($payer) or
                !isset($payer['nome']) or
                !isset($payer['email']) or
                !isset($payer['celular']) or
                !isset($payer['apelido']) or
                !isset($payer['identidade']) or
                !isset($payer['endereco']) or
                !isset($payer['endereco']['logradouro']) or
                !isset($payer['endereco']['numero']) or
                !isset($payer['endereco']['complemento']) or
                !isset($payer['endereco']['bairro']) or
                !isset($payer['endereco']['cidade']) or
                !isset($payer['endereco']['estado']) or
                !isset($payer['endereco']['pais']) or
                !isset($payer['endereco']['cep']) or
                !isset($payer['endereco']['telefone'])
            )
            {
                throw new InvalidArgumentException("Dados do payer especificados de forma incorreta");
            }
        }

        return $this;
    }

    /**
     * Method setUniqueID()
     *
     * Set the unique ID for the transaction
     *
     * @param numeric $id Unique ID for each transaction
     * @return void
     * @access public
     */
    public function setUniqueID($id)
    {
        $this->uniqueID = $id;
        return $this;
    }

    /**
     * Method setReason()
     *
     * Set the short description of transaction. eg. Order Number.
     *
     * @param string $reason The reason fo transaction
     * @return void
     * @access public
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * Method addPaymentWay()
     *
     * Add a payment's method
     *
     * @param string $way The payment method. Options: 'boleto','financiamento','debito','cartao_credito','cartao_debito','carteira_moip'
     * @param array $args Use for optional informations with the payment's method 'boleto'.
     * @return void
     * @access public
     */
    public function addPaymentWay($way,$args=null)
    {
        if(!isset($this->payment_ways[$way]))
            throw new InvalidArgumentException("Forma de pagamento indisponivel");

        if($args!=null)
        {
            if (!is_array($args))
                throw new InvalidArgumentException("Os parâmetros extra devem ser passados em um array");

            if($way=='boleto')
            {
                //argumentos possíveis: dias de expiração, instruções e logo da URL
                if (isset($args['dias_expiracao']) and isset($args['dias_expiracao']['tipo']) and isset($args['dias_expiracao']['dias']))
                {
                    $this->payment_way_args = $args;
                }
                else
                {
                    throw new InvalidArgumentException("Parâmetros passados de forma incorreta");
                }
            }
        }
        $this->payment_way[] = $way;
        return $this;
    }

    /**
     * Method setPayer()
     *
     * Set contacts informations for the payer.
     *
     * @param array $payer Contact information for the payer.
     * @return voi
     * @access public
     */
    public function setPayer($payer)
    {
        $this->payer = $payer;
        return $this;
    }

    /**
     * Method setValue()
     *
     * Set the transaction's value
     *
     * @param numeric $value The transaction's value
     * @return void
     * @access public
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Method setAdds()
     *
     * Adds a value on payment. Can be used for collecting fines, shipping and other
     *
     * @param numeric $value The value to add.
     * @return void
     * @access public
     */
    public function setAdds($value)
    {
        $this->adds = $value;
        return $this;
    }

    /**
     * Method setDeduct()
     *
     * Deducts a payment amount. It is mainly used for discounts.
     *
     * @param numeric $value The value to deduct
     * @return void
     * @access public
     */
    public function setDeduct($value)
    {
        $this->deduction = $value;
        return $this;
    }

    /**
     * Method addMessage()
     *
     * Add a message in the instruction to be displayed to the payer.
     *
     * @param string $msg Message to be displayed.
     * @return void
     * @access public
     */
    public function addMessage($msg)
    {
        if(!isset($this->xml->InstrucaoUnica->Mensagens))
        {
            $this->xml->InstrucaoUnica->addChild('Mensagens');
        }

        $this->xml->InstrucaoUnica->Mensagens->addChild('Mensagem',$msg);
        return $this;
    }

    /**
     * Method setReturnURL()
     *
     * Set the return URL, which redirects the client after payment.
     *
     * @param string $url Return URL
     * @access public
     */
    public function setReturnURL($url)
    {
        if (!isset($this->xml->InstrucaoUnica->URLRetorno))
        {
            $this->xml->InstrucaoUnica->addChild('URLRetorno',$url);
        }
    }

    /**
     * Method setNotificationURL()
     *
     * Set the notification URL, which sends information about changes in payment status
     *
     * @param string $url Notification URL
     * @access public
     */
    public function setNotificationURL($url)
    {
        if (!isset($this->xml->InstrucaoUnica->URLNotificacao))
        {
            $this->xml->InstrucaoUnica->addChild('URLNotificacao',$url);
        }
    }

    /**
     * Method addComission()
     *
     * Allows to specify commissions on the payment, like fixed values or percent.
     *
     * @param array $param Array of informations about the commissioner
     * @access public
     */
    public function addComission($param)
    {
        if (!isset($param['login_moip']))
            throw new InvalidArgumentException('Você deve especificar um usuário para comissionar.');

        if (!isset($param['valor_fixo']) and !isset($param['valor_percentual']))
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

    /**
     * Method addParcel()
     *
     * Allows to add a order to parceling.
     *
     * @param numeric $min The minimum number of parcels.
     * @param numeric $max The maximum number of parcels.
     * @param numeric $rate The percentual value of rates
     * @return void
     * @access public
     */
    public function addParcel($min,$max,$rate='')
    {
        if (!isset($this->xml->InstrucaoUnica->Parcelamentos))
        {
            $this->xml->InstrucaoUnica->addChild('Parcelamentos');
        }

        $parcela = $this->xml->InstrucaoUnica->Parcelamentos->addChild('Parcelamento');
        $parcela->addChild('MinimoParcelas',$min);
        $parcela->addChild('MaximoParcelas',$max);
        $parcela->addChild('Recebimento','AVista');

        if (!empty($rate))
        {
            $parcela->addChild('rate',$min);
        }

        return $this;
    }

    /**
     * Method addDelivery()
     *
     * Adds a parameter for delivery, allowing you to specify the shipping value calculation
     *
     * @param array $params The parameters for delivery
     * @return void
     * @access public
     */
    public function addDelivery($params)
    {
        //Validating the delivery's parameters

        if (empty($params) or !isset($params['tipo']) or !isset($params['prazo']))
        {
            throw new InvalidArgumentException('Você deve especificar o tipo de frete (proprio ou correios) e o prazo de entrega');
        }

        if (!isset($this->delivery_type[$params['tipo']]))
        {
            throw new InvalidArgumentException('Tipo de frete inválido. Opções válidas: "proprio" ou "correios"');
        }

        if (is_array($params['prazo']))
        {
            if (is_array($params['prazo']) and !isset($this->delivery_type_time[$params['prazo']['tipo']]))
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

        //End of validate

        if (!isset($this->xml->InstrucaoUnica->Entrega))
        {
            $this->xml->InstrucaoUnica->addChild('Entrega')->addChild('Destino','MesmoCobranca');
        }

        $entrega = $this->xml->InstrucaoUnica->Entrega;
        $calculo_frete = $entrega->addChild('CalculoFrete');
        $calculo_frete->addChild('Tipo',$this->delivery_type[$params['tipo']]);

        $calculo_frete->addChild('Prazo',$params['prazo']['dias'])
            ->addAttribute('Tipo',$this->delivery_type_time[$params['prazo']['tipo']]);

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

    /**
     * Method getXML()
     *
     * Returns the XML that is generated. Useful for debugging.
     *
     * @return string
     * @access public
     */
    public function getXML()
    {
        $this->xml->InstrucaoUnica->addChild('IdProprio' , $this->uniqueID);
        $this->xml->InstrucaoUnica->addChild('Razao' , $this->reason);

        if (empty($this->value))
            throw new InvalidArgumentException('Erro: o valor da transação deve ser especificado');

        $this->xml->InstrucaoUnica->addChild('Valores')
            ->addChild('Valor',$this->value)
            ->addAttribute('moeda','BRL');

        if (isset($this->deduction))
        {
            $this->xml->InstrucaoUnica->Valores->addChild('Desconto',$this->deduction)
                ->addAttribute('moeda','BRL');
        }

        if (isset($this->adds))
        {
            $this->xml->InstrucaoUnica->Valores->addChild('Acrescimo',$this->adds)
                ->addAttribute('moeda','BRL');
        }

        if (!empty($this->payment_way))
        {
            $instrucao = $this->xml->InstrucaoUnica;
            $formas = $instrucao->addChild('FormasPagamento');

            foreach ($this->payment_way as $way)
            {

                $formas->addChild('FormaPagamento',$this->payment_ways[$way]);

                if($way == 'boleto' and !empty($this->payment_way_args))
                {
                    $instrucao->addChild('Boleto')
                        ->addChild('DiasExpiracao',$this->payment_way_args['dias_expiracao']['dias'])
                        ->addAttribute('Tipo',$this->payment_way_args['dias_expiracao']['tipo']);

                    if(isset($this->payment_way_args['instrucoes']))
                    {
                        $numeroInstrucoes = 1;
                        foreach($this->payment_way_args['instrucoes'] as $instrucaostr)
                        {
                            $instrucao->Boleto->addChild('Instrucao'.$numeroInstrucoes,$instrucaostr);
                            $numeroInstrucoes++;
                        }
                    }
                }

            }
        }


        if(!empty($this->payer))
        {
            $p = $this->payer;
            $this->xml->InstrucaoUnica->addChild('Pagador');
            (isset($p['nome']))?$this->xml->InstrucaoUnica->Pagador->addChild( 'Nome' , $this->payer[ 'nome' ] ):null;
            (isset($p['login_moip']))?$this->xml->InstrucaoUnica->Pagador->addChild( 'LoginMoIP' , $this->payer[ 'login_moip' ] ):null;
            (isset($p['email']))?$this->xml->InstrucaoUnica->Pagador->addChild( 'Email' , $this->payer['email']):null;
            (isset($p['celular']))?$this->xml->InstrucaoUnica->Pagador->addChild( 'TelefoneCelular' , $this->payer['celular']):null;
            (isset($p['apelido']))?$this->xml->InstrucaoUnica->Pagador->addChild( 'Apelido' , $this->payer['apelido']):null;
            (isset($p['identidade']))?$this->xml->InstrucaoUnica->Pagador->addChild( 'Identidade' , $this->payer['identidade']):null;

            $p = $this->payer['endereco'];
            $this->xml->InstrucaoUnica->Pagador->addChild( 'EnderecoCobranca' );
            (isset($p['logradouro']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'Logradouro' , $this->payer['endereco']['logradouro']):null;

            (isset($p['numero']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'Numero' , $this->payer['endereco']['numero']):null;

            (isset($p['complemento']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'Complemento' , $this->payer['endereco']['complemento']):null;

            (isset($p['bairro']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'Bairro' , $this->payer['endereco']['bairro']):null;

            (isset($p['cidade']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'Cidade' , $this->payer['endereco']['cidade']):null;

            (isset($p['estado']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'Estado' , $this->payer['endereco']['estado']):null;

            (isset($p['pais']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'Pais' , $this->payer['endereco']['pais']):null;

            (isset($p['cep']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'CEP' , $this->payer['endereco']['cep']):null;

            (isset($p['telefone']))?$this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild( 'TelefoneFixo' , $this->payer['endereco']['telefone']):null;

        }

        $return = $this->xml->asXML();
        $this->initXMLObject();
        return str_ireplace("\n","",$return);
    }

    /**
     * Method send()
     *
     * Send the request to the server
     *
     * @param object $client The server's connection
     * @return void
     * @access public
     */
    public function send($client=null)
    {
        $this->validate();

        if($client==null)
            $client = new MoIPClient();

        if ($this->environment=='sandbox')
            $url = 'https://desenvolvedor.moip.com.br/sandbox/ws/alpha/EnviarInstrucao/Unica';
        else
            $url = 'https://www.moip.com.br/ws/alpha/EnviarInstrucao/Unica';

        $this->answer = $client->send($this->credential['token'].':'.$this->credential['key'],
            $this->getXML(),
            $url);

        return $this;
    }

    /**
     * Method getAnswer()
     *
     * Gets the server's answer
     *
     * @return object
     * @access public
     */
    public function getAnswer()
    {
        $xml = new SimpleXmlElement($this->answer->resposta);
        if (isset($xml->Resposta->Erro)) {
            return (object) array('sucesso'=>false,'mensagem'=>$xml->Resposta->Erro);
        }

        $return = (object) array();
        $return->success = (bool)$xml->Resposta->Status=='Sucesso';
        $return->id      = (string)$xml->Resposta->ID;
        $return->token = (string)$xml->Resposta->Token;

        if($this->payment_type=='Direto'){
            $return->pd_valor_total = (string)$xml->Resposta->RespostaPagamentoDireto->TotalPago;
            $return->pd_taxa_moip = (string)$xml->Resposta->RespostaPagamentoDireto->TaxaMoIP;
            $return->pd_status = (string)$xml->Resposta->RespostaPagamentoDireto->Status;
            $return->pd_codigo_moip = (string)$xml->Resposta->RespostaPagamentoDireto->CodigoMoIP;
            $return->pd_mensagem = (string)$xml->Resposta->RespostaPagamentoDireto->Mensagem;
        }

        if ($this->environment == 'sandbox')
            $return->payment_url = "https://desenvolvedor.moip.com.br/sandbox/Instrucao.do?token=".$return->token;
        else
            $return->payment_url = "https://www.moip.com.br/Instrucao.do?token=".$return->token;

        return $return;
    }

    /**
     * Method verifyPagamentoDireto()
     *
     * Does a verification of payment types available for the MoIP's client defined in $login_moip
     *
     * @param string $login_moip The client's login for MoIP services.
     * @param object $client The server's connection
     * @return object
     * @access public
     */
    public function verifyPagamentoDireto($login_moip,$client=null)
    {
        if (!isset($this->credential))
            throw new Exception("Você deve especificar as credenciais (token/key) da API antes de chamar este método");

        if ($client==null) {
            $client = new MoIPClient();
        }

        $url = "https://www.moip.com.br/ws/alpha/ChecarPagamentoDireto/$login_moip";
        $answer = $client->send($this->credential['token'].':'.$this->credential['key'],'',$url,'GET');
        $xml = new SimpleXmlElement($answer->resposta);

        return (object)array(
            'erro'=>$answer->erro,
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

    /**
     * Method verifyParcelValues()
     *
     * Get all informations about the parcelling of user defined by $login_moip
     *
     * @param string $login_moip The client's login for MoIP services
     * @param numeric $total_parcels The total parcels
     * @param numeric $rate The rate's percents of the parcelling.
     * @param numeric $simulated_value The value for simulation
     * @param object $client The server's connection
     * @return array
     * @access public
     */
    public function verifyParcelValues($login_moip,$total_parcels,$rate,$simulated_value,$client=null)
    {
        if (!isset($this->credential)) {
            throw new Exception("Você deve especificar as credenciais (token/key) da API antes de chamar este método");
        }

        if ($client==null) {
            $client = new MoIPClient();
        }

        $url = "https://www.moip.com.br/ws/alpha/ChecarValoresParcelamento/$login_moip/$total_parcels/$rate/$simulated_value";
        $answer = $client->send($this->credential['token'].':'.$this->credential['key'],'',$url,'GET');
        $xml = new SimpleXmlElement($answer->resposta);

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

    public function queryInstruction($token,$client=null)
    {
        if (!isset($this->credential))
            throw new Exception("Você deve especificar as credenciais (token/key) da API antes de chamar este método");

        $url = $this->environment == "producao"?"https://www.moip.com.br/ws/alpha/ConsultarInstrucao/":"https://desenvolvedor.moip.com.br/sandbox/ws/alpha/ConsultarInstrucao/";

        $url .= $token;
        if ($client == null)
            $client = new MoIPClient();


        $response = $client->send($this->credential['token'].':'.$this->credential['key'],'',$url,'GET');
        $xml = new SimpleXmlElement($response->resposta);
        return $xml;
    }

}
?>
