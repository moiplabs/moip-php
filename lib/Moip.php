<?php

/**
 * Library to help PHP users of Moip's API
 *
 * @author  Herberth Amaral
 * @author  Wesley Willians
 * @author  Alê Borba
 * @author  Vagner Fiuza Vieira
 * @author  Paulo Cesar
 * @version 1.7
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
	protected $encoding = 'UTF-8';
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
	protected $payment_ways = array(
		'billet' => 'BoletoBancario',
		'financing' => 'FinanciamentoBancario',
		'debit' => 'DebitoBancario',
		'creditCard' => 'CartaoCredito',
		'debitCard' => 'CartaoDebito'
	);
	/**
	 * Associative array of payment's institutions
	 *
	 * @var array
	 */
	protected $institution = array(
		'moip' => 'MoIP',
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
	protected $delivery_type = array(
		'proprio' => 'Proprio',
		'correios' => 'Correios'
	);
	/**
	 * Associative array with type of delivery's time
	 *
	 * @var array
	 */
	protected $delivery_type_time = array(
		'corridos' => 'Corridos',
		'uteis' => 'Uteis'
	);
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
	 * @param bool $testing Set true if in testing environment
	 *

	 */
	public function __construct($testing = false)
	{
		$this->setEnvironment($testing);
		$this->payment_type = 'Basic';
		$this->initXMLObject();
	}

	/**
	 * Moip uses UTF-8 responses and posts. Needed to convert to UTF-8
	 *
	 * @param string $text
	 * @param bool   $post
	 *
	 * @return string
	 */
	protected function convertEncoding($text, $post = false)
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
	 * Set the current page encoding. ISO needs to be converted to UTF-8
	 *
	 * @param $encoding
	 *
	 * @throws Exception
	 * @return Moip
	 */
	public function setEncoding($encoding)
	{
		$encoding = strtoupper($encoding);

		if (in_array($encoding, mb_list_encodings()) !== false)
		{
			$this->encoding = $encoding;
		}
		else
		{
			throw new Exception('Invalid encoding specified: ' . $encoding);
		}

		return $this;
	}

	/**
	 * Method initXMLObject()
	 *
	 * Start a new XML structure for the requests
	 *
	 * @return void
	 */
	protected function initXMLObject()
	{
		$this->xml = new SimpleXmlElement('<?xml version="1.0" encoding="utf-8" ?><EnviarInstrucao></EnviarInstrucao>');
		$this->xml->addChild('InstrucaoUnica');
	}

	/**
	 * Method setPaymentType()
	 *
	 * Define the payment's type between 'Basic' or 'Identification'
	 *
	 * @param string $tipo Can be 'Basic' or 'Identification'
	 *
	 * @return Moip
	 */
	public function setPaymentType($tipo)
	{
		if (in_array($tipo, array('Basic', 'Identification')) !== false)
		{
			$this->payment_type = $tipo;
		}
		else
		{
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
	 *
	 * @return Moip
	 */
	public function setCredential($credential)
	{
		if (
			!isset($credential['token']) or
			!isset($credential['key']) or
			strlen($credential['token']) != 32 or
			strlen($credential['key']) != 40
		)
		{
			$this->setError("Error: credential invalid");
		}

		$this->credential = $credential;

		return $this;
	}

	/**
	 * Method setEnvironment()
	 *
	 * Define the environment for the API utilization.
	 *
	 * @param bool $testing If true, will use the sandbox environment
	 *
	 * @return Moip
	 */
	public function setEnvironment($testing = false)
	{
		if ($this->environment instanceof MoipEnvironment)
		{
			unset($this->environment);
		}

		if ($testing)
		{
			$this->environment = new MoipEnvironment('https://desenvolvedor.moip.com.br/sandbox', 'Sandbox');
		}
		else
		{
			$this->environment = new MoipEnvironment('https://www.moip.com.br', 'Produção');
		}

		return $this;
	}

	/**
	 * Method validate()
	 *
	 * Make the data validation
	 *
	 * @param string $validateType Identification or Basic, defaults to Basic
	 *
	 * @return Moip
	 */
	public function validate($validateType = "Basic")
	{

		$this->setPaymentType($validateType);

		if (empty($this->credential) or empty($this->reason) or empty($this->uniqueID))
		{
			$this->setError("[setCredential], [setReason] and [setUniqueID] are required");
		}

		$payer = $this->payer;

		if ($this->payment_type === 'Identification')
		{
			$varNotSeted = '';

			$dataValidate = array(
				'name',
				'email',
				'payerId',
				'billingAddress'
			);

			$dataValidateAddress = array(
				'address',
				'number',
				'complement',
				'neighborhood',
				'city',
				'state',
				'country',
				'zipCode',
				'phone'
			);

			foreach ($dataValidate as $key)
			{
				if (!isset($payer[$key]))
				{
					$varNotSeted .= ' [' . $key . '] ';
				}
			}

			foreach ($dataValidateAddress as $key)
			{
				if (!isset($payer['billingAddress'][$key]))
				{
					$varNotSeted .= ' [' . $key . '] ';
				}
			}

			if ($varNotSeted !== '')
			{
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
	 * @param mixed $id Unique ID for each transaction
	 *
	 * @return Moip
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
	 *
	 * @return Moip
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
	 * @param string $way The payment method. Options: 'billet','financing','debit','creditCard','debitCard'.
	 *
	 * @return Moip
	 */
	public function addPaymentWay($way)
	{
		if (!isset($this->payment_ways[$way]))
		{
			$this->setError("Error: Payment method unavailable");
		}
		else
		{
			$this->payment_way[] = $way;
		}

		return $this;
	}

	/**
	 * Method billetConf()
	 *
	 * Add a payment's method
	 *
	 * @param int     $expiration   expiration in days or dateTime.
	 * @param boolean $workingDays  expiration should be counted in working days?
	 * @param array   $instructions Additional payment instructions can be array of message or a message in string
	 * @param string  $uriLogo      URL of the image to be displayed on docket (75x40)
	 *
	 * @return void
	 */
	public function setBilletConf($expiration, $workingDays = false, $instructions = null, $uriLogo = null)
	{

		if (!isset($this->xml->InstrucaoUnica->Boleto))
		{
			$this->xml->InstrucaoUnica->addChild('Boleto');

			if (is_numeric($expiration))
			{
				$this->xml->InstrucaoUnica->Boleto->addChild('DiasExpiracao', $expiration);

				if ($workingDays)
				{
					$this->xml->InstrucaoUnica->Boleto->DiasExpiracao->addAttribute('Tipo', 'Uteis');
				}
				else
				{
					$this->xml->InstrucaoUnica->Boleto->DiasExpiracao->addAttribute('Tipo', 'Corridos');
				}
			}
			else
			{
				$this->xml->InstrucaoUnica->Boleto->addChild('DataVencimento', $expiration);
			}

			if (isset($instructions))
			{
				if (is_array($instructions))
				{
					$numeroInstrucoes = 1;
					foreach ($instructions as $instrucaostr)
					{
						$this->xml->InstrucaoUnica->Boleto->addChild('Instrucao' . $numeroInstrucoes, $instrucaostr);
						$numeroInstrucoes++;
					}
				}
				else
				{
					$this->xml->InstrucaoUnica->Boleto->addChild('Instrucao1', $instructions);
				}
			}

			if (isset($uriLogo))
			{
				$this->xml->InstrucaoUnica->Boleto->addChild('URLLogo', $uriLogo);
			}
		}
	}

	/**
	 * Clean the value, and parse correctly as currency (x.xx)
	 *
	 * @param $value
	 *
	 * @return float
	 */
	protected function makeCurrency($value)
	{
		if (is_string($value))
		{
			if (strpos($value, ',') !== false && strpos($value, '.') === false)
			{
				$value = floatval(str_replace(',', '.', $value));
			}
			elseif (strpos($value, ',') === false && strpos($value, '.') !== false)
			{
				$value = floatval($value);
			}
			elseif (strpos($value, ',') !== false && strpos($value, '.') !== false)
			{
				$pos = strrchr($value, '.');
				if (strpos($pos, ',') !== false)
				{
					$value = str_replace('.', '', $value);
				}
				$value = floatval(str_replace(',', '.', $value));
			}
			else
			{
				$value = floatval($value);
			}
		}
		elseif (is_int($value))
		{
			$value = floatval($value);
		}
		elseif (!$value)
		{
			$value = 0.0;
		}

		return sprintf('%.02f', $value);
	}

	/**
	 * Method setPayer()
	 *
	 * Set contacts informations for the payer.
	 *
	 * @param array $payer Contact information for the payer.
	 *
	 * @return Moip
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
	 * @param float $value The transaction's value
	 *
	 * @return Moip
	 */
	public function setValue($value)
	{
		$this->value = $this->makeCurrency($value);

		return $this;
	}

	/**
	 * Method setAdds()
	 *
	 * Adds a value on payment. Can be used for collecting fines, shipping and other
	 *
	 * @param float $value The value to add.
	 *
	 * @return Moip
	 */
	public function setAdds($value)
	{
		$this->adds = $this->makeCurrency($value);

		return $this;
	}

	/**
	 * Method setDeduct()
	 *
	 * Deducts a payment amount. It is mainly used for discounts.
	 *
	 * @param float $value The value to deduct
	 *
	 * @return Moip
	 */
	public function setDeduct($value)
	{
		$this->deduction = $this->makeCurrency($value);

		return $this;
	}

	/**
	 * Method addMessage()
	 *
	 * Add a message in the instruction to be displayed to the payer.
	 *
	 * @param string $msg Message to be displayed.
	 *
	 * @return Moip
	 */
	public function addMessage($msg)
	{
		if (!isset($this->xml->InstrucaoUnica->Mensagens))
		{
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
	 *
	 * @return Moip
	 */
	public function setReturnURL($url)
	{
		if (!isset($this->xml->InstrucaoUnica->URLRetorno))
		{
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
	 *
	 * @return Moip
	 */
	public function setNotificationURL($url)
	{
		if (!isset($this->xml->InstrucaoUnica->URLNotificacao))
		{
			$this->xml->InstrucaoUnica->addChild('URLNotificacao', $url);
		}

		return $this;
	}

	/**
	 * Method setError()
	 *
	 * Set Erroe alert
	 *
	 * @param String $error Error alert
	 *
	 * @return Moip
	 */
	public function setError($error)
	{
		$this->errors = $error;

		return $this;
	}

	/**
	 * Method addComission()
	 *
	 * Allows to specify commissions on the payment, like fixed values or percent.
	 *
	 * @param string  $reason          reason for commissioning
	 * @param string  $receiver        login Moip the secondary receiver
	 * @param number  $value           value of the division of payment
	 * @param boolean $percentageValue percentage value should be
	 * @param boolean $ratePayer       this secondary recipient will pay the fee Moip
	 *
	 * @return Moip
	 */
	public function addComission($reason, $receiver, $value, $percentageValue = false, $ratePayer = false)
	{

		if (!isset($this->xml->InstrucaoUnica->Comissoes))
		{
			$this->xml->InstrucaoUnica->addChild('Comissoes');
		}

		if (is_numeric($value))
		{

			$split = $this->xml->InstrucaoUnica->Comissoes->addChild('Comissionamento');
			$split->addChild('Comissionado')->addChild('LoginMoIP', $receiver);
			$split->addChild('Razao', $reason);

			if ($percentageValue == false)
			{
				$split->addChild('ValorFixo', $value);
			}
			if ($percentageValue == true)
			{
				$split->addChild('ValorPercentual', $value);
			}
			if ($ratePayer == true)
			{
				$this->xml->InstrucaoUnica->Comissoes->addChild('PagadorTaxa')->addChild('LoginMoIP', $receiver);
			}
		}
		else
		{
			$this->setError('Error: Value must be numeric.');
		}

		return $this;
	}

	/**
	 * Method addParcel()
	 *
	 * Allows to add a order to parceling.
	 *
	 * @param int     $min      The minimum number of parcels.
	 * @param int     $max      The maximum number of parcels.
	 * @param float   $rate     The percentual value of rates
	 * @param boolean $transfer "true" defines the amount of interest charged by MoIP installment to be paid by the payer
	 *
	 * @return Moip
	 */
	public function addParcel($min, $max, $rate = null, $transfer = false)
	{
		if (!isset($this->xml->InstrucaoUnica->Parcelamentos))
		{
			$this->xml->InstrucaoUnica->addChild('Parcelamentos');
		}

		$parcela = $this->xml->InstrucaoUnica->Parcelamentos->addChild('Parcelamento');
		if (is_numeric($min) && $min <= 12)
		{
			$parcela->addChild('MinimoParcelas', $min);
		}
		else
		{
			$this->setError('Error: Minimum parcel can not be greater than 12.');
		}

		if (is_numeric($max) && $max <= 12)
		{
			$parcela->addChild('MaximoParcelas', $max);
		}
		else
		{
			$this->setError('Error: Maximum amount can not be greater than 12.');
		}

		$parcela->addChild('Recebimento', 'AVista');

		if ($transfer === false)
		{
			if (isset($rate))
			{
				if (is_numeric($rate))
				{
					$parcela->addChild('Juros', $rate);
				}
				else
				{
					$this->setError('Error: Rate must be numeric');
				}
			}
		}
		else
		{
			if (is_bool($transfer))
			{
				$parcela->addChild('Repassar', $transfer);
			}
			else
			{
				$this->setError('Error: Transfer must be boolean');
			}
		}

		return $this;
	}

	/**
	 * Method setReceiving()
	 *
	 * Allows to add a order to parceling.
	 *
	 * @param string $receiver login Moip the secondary receiver
	 *
	 * @return Moip
	 */
	public function setReceiver($receiver)
	{
		if (!isset($this->xml->InstrucaoUnica->Recebedor))
		{
			$this->xml->InstrucaoUnica->addChild('Recebedor')
			->addChild('LoginMoIP', $receiver);
		}

		return $this;
	}

	public function removeInstruction($paymentToken)
	{
		$answer = $this->doRequest('RemoverInstrucaoPost', "<RemoverInstrucaoPost><Token>{$paymentToken}</Token></RemoverInstrucaoPost>", 'POST');

		if ($answer->response !== false)
		{
			$xml_answer = simplexml_load_string($answer->xml);

			if ($xml_answer && !isset($xml_answer->Resposta->Erro))
			{
				return new MoipResponse(self::xml2array($xml_answer));
			}
			else
			{
				return new MoipResponse(self::xml2array($xml_answer->Resposta));
			}
		}

		return new MoipResponse(array());
	}

	/**
	 * GET request for common instructions
	 *
	 * @param string $name  Name of the instruction, like VisualizarInstrucaoUnica, RemoverInstrucaoPost, etc
	 * @param string $content Token
	 * @param string $method GET or POST
	 *
	 * @throws InvalidArgumentException
	 * @return MoipResponse
	 */
	public function doRequest($name, $content = null, $method = 'GET')
	{
		$client = new MoipClient();

		$url = $this->environment->base_url . '/ws/alpha/' . $name;
		$credentials = $this->credential['token'] . ':' . $this->credential['key'];

		switch (strtoupper($method))
		{
			case 'GET':
				$url .= '/' . ($content ? $content : $this->getAnswer()->token);
				$answer = $client->curlGet($credentials, $url);
				break;
			case 'POST':
				if (empty($content))
				{
					throw new InvalidArgumentException('doRequest for POST needs content');
				}
				$answer = $client->curlPost($credentials, $content, $url);
				break;
			default:
				throw new InvalidArgumentException('$method must be GET or POST');
		}

		unset($client);

		return $answer;
	}

	protected static function xml2array($xml)
	{
		return json_decode(json_encode((array)$xml), true);
	}

	/**
	 * Method getUniqueInstruction()
	 *
	 * @param $paymentToken
	 *
	 * @return MoipResponse
	 */
	public function getUniqueInstruction($paymentToken = null)
	{
		$answer = $this->doRequest('VisualizarInstrucaoUnica', $paymentToken);

		if ($answer->response !== false)
		{
			$xml_answer = simplexml_load_string($answer->xml);

			if ($xml_answer && isset($xml_answer->RespostaVisualizarInstrucaoUnica))
			{
				return new MoipResponse(self::xml2array($xml_answer->RespostaVisualizarInstrucaoUnica->InstrucaoUnica));
			}
			else
			{
				return new MoipResponse(self::xml2array($xml_answer));
			}
		}

		return new MoipResponse(array());
	}

	/**
	 * Method getStatus()
	 *
	 * Get the status of the current token or the param token
	 *
	 * @param string $paymentToken
	 *
	 * @return MoipResponse
	 */
	public function getPaymentStatus($paymentToken = null)
	{
		$answer = $this->doRequest('ConsultarInstrucao', $paymentToken);

		if ($answer->response !== false)
		{
			$xml_answer = simplexml_load_string($answer->xml);

			if ($xml_answer && isset($xml_answer->RespostaConsultar->Autorizacao))
			{
				unset($xml_answer->RespostaConsultar->Autorizacao->Recebedor);

				return new MoipResponse(self::xml2array($xml_answer->RespostaConsultar->Autorizacao));
			}
			else
			{
				return new MoipResponse(self::xml2array($xml_answer));
			}
		}

		return new MoipResponse(array());
	}

	/**
	 * Method getXML()
	 *
	 * Returns the XML that is generated. Useful for debugging.
	 *
	 * @return string
	 */
	public function getXML()
	{

		if ($this->payment_type == "Identification")
		{
			$this->xml->InstrucaoUnica->addAttribute('TipoValidacao', 'Transparente');
		}

		$this->xml->InstrucaoUnica->addChild('IdProprio', $this->uniqueID);
		$this->xml->InstrucaoUnica->addChild('Razao', $this->reason);

		if (empty($this->value))
		{
			$this->setError('Error: The transaction amount must be specified.');
		}

		$this->xml->InstrucaoUnica->addChild('Valores')
		->addChild('Valor', $this->value)
		->addAttribute('moeda', 'BRL');

		if (isset($this->deduction))
		{
			$this->xml->InstrucaoUnica->Valores->addChild('Deducao', $this->deduction)
			->addAttribute('moeda', 'BRL');
		}

		if (isset($this->adds))
		{
			$this->xml->InstrucaoUnica->Valores->addChild('Acrescimo', $this->adds)
			->addAttribute('moeda', 'BRL');
		}

		if (!empty($this->payment_way))
		{
			$instrucao = $this->xml->InstrucaoUnica;
			$formas = $instrucao->addChild('FormasPagamento');

			foreach ($this->payment_way as $way)
			{
				$formas->addChild('FormaPagamento', $this->payment_ways[$way]);
			}
		}

		if (!empty($this->payer))
		{
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

		$return = $this->convertEncoding($this->xml->asXML(), true);
		$this->initXMLObject();

		return str_replace("\n", "", $return);
	}

	/**
	 * Method send()
	 *
	 * Send the request to the server
	 *
	 * @param object $client The server's connection
	 *
	 * @return MoipResponse
	 */
	public function send($client = null)
	{
		$this->validate();

		if ($client == null)
		{
			$client = new MoipClient();
		}

		$url = $this->environment->base_url . '/ws/alpha/EnviarInstrucao/Unica';

		return
			$this->answer =
				$client->curlPost($this->credential['token'] . ':' . $this->credential['key'],
					$this->getXML(),
					$url,
					$this->errors
				);
	}

	/**
	 * Get the Checkout Transparent javascript URL according to the environment
	 *
	 * @param bool $include_https In IE, including https:// inside an http:// site raises an error
	 *
	 * @return string
	 */
	public function getJavascript($include_https = true)
	{
		return ($include_https ? $this->environment->base_url : str_replace('https://', '//', $this->environment->base_url)) . '/transparente/MoipWidget-v2.js';
	}

	/**
	 * Return the HTML widget with the token and callbacks attached
	 *
	 * @param string $success Name of success function callback in Javascript
	 * @param string $error   Name of error function callback in Javascript
	 *
	 * @return string
	 */
	public function getWidget($success, $error)
	{
		$token = $this->getAnswer()->token;
		$html = <<<"HTML"
<div id="MoipWidget" data-token="{$token}" callback-method-success="{$success}" callback-method-error="{$error}"></div>
HTML;

		return $html;
	}

	/**
	 * Method getAnswer()
	 *
	 * Gets the server's answer
	 *
	 * @param boolean $return_xml_as_string Return the answer XMl string
	 *
	 * @return MoipResponse|string String will be returned in case of an error
	 */
	public function getAnswer($return_xml_as_string = false)
	{
		if ($this->answer->response === true)
		{
			if ($return_xml_as_string)
			{
				return $this->answer->xml;
			}

			$xml = new SimpleXmlElement($this->answer->xml);

			return new MoipResponse(array(
				'response' => $xml->Resposta->Status == 'Sucesso' ? true : false,
				'error' => $xml->Resposta->Status == 'Falha' ? $this->convertEncoding((string)$xml->Resposta->Erro) : false,
				'token' => (string)$xml->Resposta->Token,
				'payment_url' => $xml->Resposta->Status == 'Sucesso' ? (string)$this->environment->base_url . "/Instrucao.do?token=" . (string)$xml->Resposta->Token : false,
			));
		}
		else
		{
			return $this->answer->error;
		}
	}

	/**
	 * Method verifyParcelValues()
	 *
	 * Get all informations about the parcelling of user defined by $login_moip
	 *
	 * @param string $login          The client's login for Moip services
	 * @param int    $maxParcel      The total parcels
	 * @param float  $rate           The rate's percents of the parcelling.
	 * @param float  $simulatedValue The value for simulation
	 *
	 * @return array
	 */
	public function queryParcel($login, $maxParcel, $rate, $simulatedValue)
	{
		if (!isset($this->credential))
		{
			$this->setError("You must specify the credentials (token / key) and enriroment");
		}

		$client = new MoipClient();

		$url = $this->environment->base_url . "/ws/alpha/ChecarValoresParcelamento/$login/$maxParcel/$rate/$simulatedValue";
		$credential = $this->credential['token'] . ':' . $this->credential['key'];
		$answer = $client->curlGet($credential, $url, $this->errors);

		if ($answer->response)
		{
			$xml = new SimpleXmlElement($answer->xml);

			if ($xml->Resposta->Status === "Sucesso")
			{
				$response = true;
			}
			else
			{
				$response = false;
			}

			$return = array(
				'response' => $response,
				'installment' => array()
			);

			$i = 1;
			foreach ($xml->Resposta->ValorDaParcela as $parcela)
			{
				$attrib = $parcela->attributes();
				$return['installment']["$i"] = array('total' => (string)$attrib['Total'], 'rate' => (string)$attrib['Juros'], 'value' => (string)$attrib['Valor']);
				$i++;
			}

			return $return;
		}

		return $answer;
	}
}