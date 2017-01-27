<?php

/**
 * Library to help PHP users of Moip's API
 *
 * @author Herberth Amaral
 * @author Wesley Willians
 * @author Alê Borba
 * @author Vagner Fiuza Vieira
 * @author Paulo Cesar
 * @version 1.6.2
 * @license <a href="http://www.opensource.org/licenses/bsd-license.php">BSD License</a>
 */

/**
 * Moip's API abstraction class
 *
 * Class to use for all abstraction of Moip's API
 * @package Moip
 */
class Moip {

	/**
	 * Encoding of the page
	 *
	 * @var string
	 */
	public $encoding = 'UTF-8';
    /**
     * Associative array with two keys. 'key'=>'your_key','token'=>'your_token'
     *
     * @var array
     */
    protected $credential;
    /**
     * Define the payment's reason
     *
     * @var string
     */
    protected $reason;
    /**
     * The application's environment
     *
     * @var MoipEnvironment
     */
    protected $environment = null;
    /**
     * Transaction's unique ID
     *
     * @var string
     */
    protected $uniqueID;
    /**
     * Associative array of payment's way
     *
     * @var array
     */
    protected $payment_ways = array('billet' => 'BoletoBancario',
        'financing' => 'FinanciamentoBancario',
        'debit' => 'DebitoBancario',
        'creditCard' => 'CartaoCredito',
        'debitCard' => 'CartaoDebito');
    /**
     * Associative array of payment's institutions
     *
     * @var array
     */
    protected $institution = array('moip' => 'MoIP',
        'visa' => 'Visa',
        'american_express' => 'AmericanExpress',
        'mastercard' => 'Mastercard',
        'diners' => 'Diners',
        'banco_brasil' => 'BancoDoBrasil',
        'bradesco' => 'Bradesco',
        'itau' => 'Itau',
        'real' => 'BancoReal',
        'unibanco' => 'Unibanco',
        'aura' => 'Aura',
        'hipercard' => 'Hipercard',
        'paggo' => 'Paggo', //oi paggo
        'banrisul' => 'Banrisul'
    );
    /**
     * Associative array of delivery's type
     *
     * @var array
     */
    protected $delivery_type = array('proprio' => 'Proprio', 'correios' => 'Correios');
    /**
     * Associative array with type of delivery's time
     *
     * @var array
     */
    protected $delivery_type_time = array('corridos' => 'Corridos', 'uteis' => 'Uteis');
    /**
     * Payment method
     *
     * @var array
     */
    protected $payment_method;
    /**
     * Arguments of payment method
     *
     * @var array
     */
    protected $payment_method_args;
    /**
     * Payment's type
     *
     * @var string
     */
    protected $payment_type;
    /**
     * Associative array with payer's information
     *
     * @var array
     */
    protected $payer;
    /**
     * Server's answer
     *
     * @var MoipResponse
     */
    public $answer;
    /**
     * The transaction's value
     *
     * @var float
     */
    protected $value;
    /**
     * Simple XML object
     *
     * @var SimpleXMLElement
     */
    protected $xml;
    /**
     * Simple XML object
     *
     * @var object
     */
    public $errors;
	/**
	 * @var array
	 */
	protected $payment_way = array();
	/**
	 * @var float
	 */
	protected $adds;
	/**
	 * @var float
	 */
	protected $deduction;
    /**
     * Method construct
     *
     * @access public
     */
    public function __construct() {
        $this->setEnvironment();

        if (!$this->payment_type) {
            $this->payment_type = 'Basic';
        }

        $this->initXMLObject();
    }

	private function convert_encoding($text, $post = false)
	{
		if ($post)
		{
			return mb_convert_encoding($text, 'UTF-8');
		}
		else
		{
			/* No need to convert if its already in utf-8 */
			if ($this->encoding === 'UTF-8')
			{
				return $text;
			}
			return mb_convert_encoding($text, $this->encoding, 'UTF-8');
		}
	}

    /**
     * Method initXMLObject()
     *
     * Start a new XML structure for the requests
     *
     * @return void
     * @access private
     */
    private function initXMLObject() {
        $this->xml = new SimpleXmlElement('<?xml version="1.0" encoding="utf-8" ?><EnviarInstrucao></EnviarInstrucao>');
        $this->xml->addChild('InstrucaoUnica');
    }

    /**
     * Method setPaymentType()
     *
     * Define the payment's type between 'Basic' or 'Identification'
     *
     * @param string $tipo Can be 'Basic' or 'Identification'
     * @return Moip
     * @access public
     */
    public function setPaymentType($tipo) {
        if ($tipo == 'Basic' || $tipo == 'Identification') {
            $this->payment_type = $tipo;
        } else {
            $this->setError("Error: The variable type must contain values 'Basic' or 'Identification'");
        }

        return $this;
    }

    /**
     * Method setCredential()
     *
     * Set the credentials(key,token) required for the API authentication.
     *
     * @param array $credential Array with the credentials token and key
     * @return Moip
     * @access public
     */
    public function setCredential($credential) {
        if (!isset($credential['token']) or
                !isset($credential['key']) or
                strlen($credential['token']) != 32 or
                strlen($credential['key']) != 40)
            $this->setError("Error: credential invalid");

        $this->credential = $credential;
        return $this;
    }

    /**
     * Method setEnvironment()
     *
     * Define the environment for the API utilization.
     *
     * @param bool $testing If true, will use the sandbox environment
	 * @return Moip
     */
    public function setEnvironment($testing = false) {
		if (empty($this->environment))
		{
			$this->environment = new MoipEnvironment();
		}

        if ($testing) {
            $this->environment->name = "Sandbox";
            $this->environment->base_url = "https://desenvolvedor.moip.com.br/sandbox";
        } else {
            $this->environment->name = "Produção";
            $this->environment->base_url = "https://www.moip.com.br";
        }

        return $this;
    }

    /**
     * Method validate()
     *
     * Make the data validation
	 *
     * @param string $validateType Identification or Basic, defaults to Basic
     * @return Moip
     * @access public
     */
    public function validate($validateType = "Basic") {

        $this->setPaymentType($validateType);

        if (!isset($this->credential) or
                !isset($this->reason) or
                !isset($this->uniqueID))
            $this->setError("[setCredential], [setReason] and [setUniqueID] are required");

        $payer = $this->payer;

        if ($this->payment_type == 'Identification') {
			$varNotSeted = '';

            $dataValidate = array('name',
                'email',
                'payerId',
                'billingAddress');

            $dataValidateAddress = array('address',
                'number',
                'complement',
                'neighborhood',
                'city',
                'state',
                'country',
                'zipCode',
                'phone');

            foreach ($dataValidate as $key) {
                if (!isset($payer[$key])) {
                    $varNotSeted .= ' [' . $key . '] ';
                }
            }

            foreach ($dataValidateAddress as $key) {
                if (!isset($payer['billingAddress'][$key])) {
                    $varNotSeted .= ' [' . $key . '] ';
                }
            }

            if ($varNotSeted !== '') {
                $this->setError('Error: The following data required were not informed: ' . $varNotSeted . '.');
			}
        }
        return $this;
    }

    /**
     * Method setUniqueID()
     *
     * Set the unique ID for the transaction
     *
     * @param int $id Unique ID for each transaction
     * @return Moip
     * @access public
     */
    public function setUniqueID($id) {
        $this->uniqueID = $id;
        $this->xml->InstrucaoUnica->addChild('IdProprio', $this->uniqueID);

        return $this;
    }

    /**
     * Method setReason()
     *
     * Set the short description of transaction. eg. Order Number.
     *
     * @param string $reason The reason fo transaction
     * @return Moip
     * @access public
     */
    public function setReason($reason) {
        $this->reason = $reason;
        $this->xml->InstrucaoUnica->addChild('Razao', $this->reason);
        
        return $this;
    }

    /**
     * Method addPaymentWay()
     *
     * Add a payment's method
     *
     * @param string $way The payment method. Options: 'billet','financing','debit','creditCard','debitCard'.
     * @return Moip
     * @access public
     */
    public function addPaymentWay($way) {
        if (!isset($this->payment_ways[$way]))
            $this->setError("Error: Payment method unavailable");
        else
            $this->payment_way[] = $way;


        $instrucao = $this->xml->InstrucaoUnica;


        $formas = (!isset($instrucao->FormasPagamento)) ? $instrucao->addChild('FormasPagamento') : $instrucao->FormasPagamento;

        if (!empty($this->payment_way)) 
            $formas->addChild('FormaPagamento', $this->payment_ways[$way]);

        return $this;
    }

    /**
     * Method billetConf()
     *
     * Add a payment's method
     *
     * @param int $expiration expiration in days or dateTime.
     * @param boolean $workingDays expiration should be counted in working days?
     * @param array $instructions Additional payment instructions can be array of message or a message in string
     * @param string $uriLogo URL of the image to be displayed on docket (75x40)
     * @return void
     * @access public
     */
    public function setBilletConf($expiration, $workingDays=false, $instructions = null, $uriLogo = null) {

        if (!isset($this->xml->InstrucaoUnica->Boleto)) {
            $this->xml->InstrucaoUnica->addChild('Boleto');

            if (is_numeric($expiration)) {
                $this->xml->InstrucaoUnica->Boleto->addChild('DiasExpiracao', $expiration);

                if ($workingDays)
                    $this->xml->InstrucaoUnica->Boleto->DiasExpiracao->addAttribute('Tipo', 'Uteis');
                else
                    $this->xml->InstrucaoUnica->Boleto->DiasExpiracao->addAttribute('Tipo', 'Corridos');
            }else {
                $this->xml->InstrucaoUnica->Boleto->addChild('DataVencimento', $expiration);
            }

            if (isset($instructions)) {
                if (is_array($instructions)) {
                    $numeroInstrucoes = 1;
                    foreach ($instructions as $instrucaostr) {
                        $this->xml->InstrucaoUnica->Boleto->addChild('Instrucao' . $numeroInstrucoes, $instrucaostr);
                        $numeroInstrucoes++;
                    }
                } else {
                    $this->xml->InstrucaoUnica->Boleto->addChild('Instrucao1', $instructions);
                }
            }

            if (isset($uriLogo))
                $this->xml->InstrucaoUnica->Boleto->addChild('URLLogo', $uriLogo);
        }

        return $this;
    }

    /**
     * Method setPayer()
     *
     * Set contacts informations for the payer.
     *
     * @param array $payer Contact information for the payer.
     * @return Moip
     * @access public
     */
    public function setPayer($payer) {
        $this->payer = $payer;

        if (!empty($this->payer)) {
            $p = $this->payer;
            $this->xml->InstrucaoUnica->addChild('Pagador');
            (isset($p['name'])) ? $this->xml->InstrucaoUnica->Pagador->addChild('Nome', $this->payer['name']) : null;
            (isset($p['email'])) ? $this->xml->InstrucaoUnica->Pagador->addChild('Email', $this->payer['email']) : null;
            (isset($p['payerId'])) ? $this->xml->InstrucaoUnica->Pagador->addChild('IdPagador', $this->payer['payerId']) : null;
            (isset($p['identity'])) ? $this->xml->InstrucaoUnica->Pagador->addChild('Identidade', $this->payer['identity']) : null;
            (isset($p['phone'])) ? $this->xml->InstrucaoUnica->Pagador->addChild('TelefoneCelular', $this->payer['phone']) : null;

            $p = $this->payer['billingAddress'];
            $this->xml->InstrucaoUnica->Pagador->addChild('EnderecoCobranca');
            (isset($p['address'])) ? $this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild('Logradouro', $this->payer['billingAddress']['address']) : null;

            (isset($p['number'])) ? $this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild('Numero', $this->payer['billingAddress']['number']) : null;

            (isset($p['complement'])) ? $this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild('Complemento', $this->payer['billingAddress']['complement']) : null;

            (isset($p['neighborhood'])) ? $this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild('Bairro', $this->payer['billingAddress']['neighborhood']) : null;

            (isset($p['city'])) ? $this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild('Cidade', $this->payer['billingAddress']['city']) : null;

            (isset($p['state'])) ? $this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild('Estado', $this->payer['billingAddress']['state']) : null;

            (isset($p['country'])) ? $this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild('Pais', $this->payer['billingAddress']['country']) : null;

            (isset($p['zipCode'])) ? $this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild('CEP', $this->payer['billingAddress']['zipCode']) : null;

            (isset($p['phone'])) ? $this->xml->InstrucaoUnica->Pagador->EnderecoCobranca->addChild('TelefoneFixo', $this->payer['billingAddress']['phone']) : null;
        }

        return $this;
    }

    /**
     * Method setValue()
     *
     * Set the transaction's value
     *
     * @param float $value The transaction's value
     * @return Moip
     * @access public
     */
    public function setValue($value) {
        $this->value = $value;

        if (empty($this->value))
            $this->setError('Error: The transaction amount must be specified.');

        $this->xml->InstrucaoUnica->addChild('Valores')
                ->addChild('Valor', $this->value)
                ->addAttribute('moeda', 'BRL');

        return $this;
    }

    /**
     * Method setAdds()
     *
     * Adds a value on payment. Can be used for collecting fines, shipping and other
     *
     * @param float $value The value to add.
     * @return Moip
     * @access public
     */
    public function setAdds($value) {
        $this->adds = $value;

        if (isset($this->adds)) {
            $this->xml->InstrucaoUnica->Valores->addChild('Acrescimo', $this->adds)
                    ->addAttribute('moeda', 'BRL');
        }

        return $this;
    }

    /**
     * Method setDeduct()
     *
     * Deducts a payment amount. It is mainly used for discounts.
     *
     * @param float $value The value to deduct
     * @return Moip
     * @access public
     */
    public function setDeduct($value) {
        $this->deduction = $value;

        if (isset($this->deduction)) {
            $this->xml->InstrucaoUnica->Valores->addChild('Deducao', $this->deduction)
                    ->addAttribute('moeda', 'BRL');
        }

        return $this;
    }

    /**
     * Method addMessage()
     *
     * Add a message in the instruction to be displayed to the payer.
     *
     * @param string $msg Message to be displayed.
     * @return Moip
     * @access public
     */
    public function addMessage($msg) {
        if (!isset($this->xml->InstrucaoUnica->Mensagens)) {
            $this->xml->InstrucaoUnica->addChild('Mensagens');
        }

        $this->xml->InstrucaoUnica->Mensagens->addChild('Mensagem', $msg);
        return $this;
    }

    /**
     * Method setReturnURL()
     *
     * Set the return URL, which redirects the client after payment.
     *
     * @param string $url Return URL
	 * @return Moip
     * @access public
     */
    public function setReturnURL($url) {
        if (!isset($this->xml->InstrucaoUnica->URLRetorno)) {
            $this->xml->InstrucaoUnica->addChild('URLRetorno', $url);
        }
		return $this;
    }

    /**
     * Method setNotificationURL()
     *
     * Set the notification URL, which sends information about changes in payment status
     *
     * @param string $url Notification URL
     * @access public
     */
    public function setNotificationURL($url) {
        if (!isset($this->xml->InstrucaoUnica->URLNotificacao)) {
            $this->xml->InstrucaoUnica->addChild('URLNotificacao', $url);
        }
    }

    /**
     * Method setError()
     *
     * Set Erroe alert
     *
     * @param String $error Error alert
     * @return Moip
     * @access public
     */
    public function setError($error) {
        $this->errors = $error;

        return $this;
    }

    /**
     * Method addComission()
     *
     * Allows to specify commissions on the payment, like fixed values or percent.
     *
     * @param string $reason reason for commissioning
     * @param string $receiver login Moip the secondary receiver
     * @param number $value value of the division of payment
     * @param boolean $percentageValue percentage value should be
     * @param boolean $ratePayer this secondary recipient will pay the fee Moip
	 * @return Moip
     * @access public
     */
    public function addComission($reason, $receiver, $value, $percentageValue=false, $ratePayer=false) {

        if (!isset($this->xml->InstrucaoUnica->Comissoes))
            $this->xml->InstrucaoUnica->addChild('Comissoes');

        if (is_numeric($value)) {

            $split = $this->xml->InstrucaoUnica->Comissoes->addChild('Comissionamento');
            $split->addChild('Comissionado')->addChild('LoginMoIP', $receiver);
            $split->addChild('Razao', $reason);

            if ($percentageValue == false)
                $split->addChild('ValorFixo', $value);
            if ($percentageValue == true)
                $split->addChild('ValorPercentual', $value);
            if ($ratePayer == true)
                $this->xml->InstrucaoUnica->Comissoes->addChild('PagadorTaxa')->addChild('LoginMoIP', $receiver);
        }else {
            $this->setError('Error: Value must be numeric.');
        }

        return $this;
    }

    /**
     * Method addParcel()
     *
     * Allows to add a order to parceling.
     *
     * @param int $min The minimum number of parcels.
     * @param int $max The maximum number of parcels.
     * @param float $rate The percentual value of rates
     * @param boolean $transfer "true" defines the amount of interest charged by MoIP installment to be paid by the payer
     * @return Moip
     * @access public
     */
    public function addParcel($min, $max, $rate=null, $transfer=false) {
        if (!isset($this->xml->InstrucaoUnica->Parcelamentos)) {
            $this->xml->InstrucaoUnica->addChild('Parcelamentos');
        }

        $parcela = $this->xml->InstrucaoUnica->Parcelamentos->addChild('Parcelamento');
        if (is_numeric($min) && $min <= 12)
            $parcela->addChild('MinimoParcelas', $min);
        else
            $this->setError('Error: Minimum parcel can not be greater than 12.');

        if (is_numeric($max) && $max <= 12)
            $parcela->addChild('MaximoParcelas', $max);
        else
            $this->setError('Error: Maximum amount can not be greater than 12.');

        $parcela->addChild('Recebimento', 'AVista');

        if ($transfer === false) {
            if (isset($rate)) {
                if (is_numeric($rate))
                    $parcela->addChild('Juros', $rate);
                else
                    $this->setError('Error: Rate must be numeric');
            }
        }else {
            if (is_bool($transfer))
                $parcela->addChild('Repassar', $transfer);
            else
                $this->setError('Error: Transfer must be boolean');
        }

        return $this;
    }

    /**
     * Method setReceiving()
     *
     * Allows to add a order to parceling.
     *
     * @param string $receiver login Moip the secondary receiver
     * @return Moip
     * @access public
     */
    public function setReceiver($receiver) {
        if (!isset($this->xml->InstrucaoUnica->Recebedor)) {
            $this->xml->InstrucaoUnica->addChild('Recebedor')
                    ->addChild('LoginMoIP', $receiver);
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
    public function getXML() {

        if ($this->payment_type == "Identification")
            $this->xml->InstrucaoUnica->addAttribute('TipoValidacao', 'Transparente');        

        $return = $this->convert_encoding($this->xml->asXML(), true);
        return str_ireplace("\n", "", $return);
    }

    /**
     * Method send()
     *
     * Send the request to the server
     *
     * @param object $client The server's connection
     * @return MoipResponse
     * @access public
     */
    public function send($client=null) {
        $this->validate();

        if ($client == null)
            $client = new MoipClient();

        $url = $this->environment->base_url . '/ws/alpha/EnviarInstrucao/Unica';

        return $this->answer = $client->curlPost($this->credential['token'] . ':' . $this->credential['key'],
                $this->getXML(),
                $url, $this->errors);
    }

    /**
     * Method getAnswer()
     *
     * Gets the server's answer
     * @param boolean $return_xml_as_string Return the answer XMl string
     * @return MoipResponse|string
     * @access public
     */
    public function getAnswer($return_xml_as_string = false) {
        if ($this->answer->response == true) {
            if ($return_xml_as_string) {
                return $this->answer->xml;
            }

            $xml = new SimpleXmlElement($this->answer->xml);

            return new MoipResponse(array(
				'response' => $xml->Resposta->Status == 'Sucesso' ? true : false,
    			'error' => $xml->Resposta->Status == 'Falha' ? $this->convert_encoding((string)$xml->Resposta->Erro) : false,
    			'token' => (string) $xml->Resposta->Token,
    			'payment_url' => $xml->Resposta->Status == 'Sucesso' ? (string) $this->environment->base_url . "/Instrucao.do?token=" . (string) $xml->Resposta->Token : false,
			));
        } else {
            return $this->answer->error;
        }
    }

    /**
     * Method verifyParcelValues()
     *
     * Get all informations about the parcelling of user defined by $login_moip
     *
     * @param string $login The client's login for Moip services
     * @param int $maxParcel The total parcels
     * @param float $rate The rate's percents of the parcelling.
     * @param float $simulatedValue The value for simulation
     * @return array
     * @access public
     */
    public function queryParcel($login, $maxParcel, $rate, $simulatedValue) {
        if (!isset($this->credential))
            $this->setError("You must specify the credentials (token / key) and enriroment");


        $client = new MoipClient();

        $url = $this->environment->base_url . "/ws/alpha/ChecarValoresParcelamento/$login/$maxParcel/$rate/$simulatedValue";
        $credential = $this->credential['token'] . ':' . $this->credential['key'];
        $answer = $client->curlGet($credential, $url, $this->errors);

        if ($answer->response) {
            $xml = new SimpleXmlElement($answer->xml);

            if ($xml->Resposta->Status == "Sucesso")
                $response = true;
            else
                $response = false;

            $return = array('response' => $response,
                'installment' => array());

            $i = 1;
            foreach ($xml->Resposta->ValorDaParcela as $parcela) {
                $attrib = $parcela->attributes();
                $return['installment']["$i"] = array('total' => (string) $attrib['Total'], 'rate' => (string) $attrib['Juros'], 'value' => (string) $attrib['Valor']);
                $i++;
            }
            return $return;
        }

		return $answer;
    }

}

class MoipEnvironment {
	public $base_url;
	public $name;

	function __construct($base_url = '', $name = '')
	{
		$this->base_url = $base_url;
		$this->name = $name;
	}
}