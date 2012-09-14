<?php

/**
 * Library to help PHP users of Moip's API
 *
 * @author Herberth Amaral
 * @author Wesley Willians
 * @author Alê Borba
 * @author Vagner Fiuza Vieira
 * @version 1.5
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
    private $payment_ways = array('billet' => 'BoletoBancario',
        'financing' => 'FinanciamentoBancario',
        'debit' => 'DebitoBancario',
        'creditCard' => 'CartaoCredito',
        'debitCard' => 'CartaoDebito');
    /**
     * Associative array of payment's institutions
     *
     * @var array
     * @access private
     */
    private $institution = array('moip' => 'MoIP',
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
     * @access private
     */
    private $delivery_type = array('proprio' => 'Proprio', 'correios' => 'Correios');
    /**
     * Associative array with type of delivery's time
     *
     * @var array
     * @access private
     */
    private $delivery_type_time = array('corridos' => 'Corridos', 'uteis' => 'Uteis');
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
     * Simple XML object
     *
     * @var object
     * @access public
     */
    public $errors;

    /**
     * Method construct
     *
     * @return void
     * @access public
     */
    public function __construct() {
//Verify the payment's type, if null set 'Unico'
        $this->setEnvironment();

        if (!$this->payment_type) {
            $this->payment_type = 'Basic';
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
    private function initXMLObject() {
        $this->xml = new SimpleXmlElement('<?xml version="1.0" encoding="utf-8" ?><EnviarInstrucao></EnviarInstrucao>');
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
    public function setPaymentType($tipo) {
//Verify if the value of variable $tipo is between 'Basic' or 'Identification'. If not, throw new exception error
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
     * @return void
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
     * @param string $environment Only two values supported, 'sandbox' or 'producao'
     */
    public function setEnvironment($environment = null) {
        if ($environment == 'test') {
            $return = (object) array();
            $return->name = "Sandbox";
            $return->base_url = "https://desenvolvedor.moip.com.br/sandbox";
        } else {
            $return = (object) array();
            $return->name = "Produção";
            $return->base_url = "https://www.moip.com.br";
        }

        $this->environment = $return;

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
    public function validate($validateType = "Basic") {

        $this->setPaymentType($validateType);

        if (!isset($this->credential) or
                !isset($this->reason) or
                !isset($this->uniqueID))
            $this->setError("[setCredential], [setReason] and [setUniqueID] are required");

        $payer = $this->payer;

        if ($this->payment_type == 'Identification') {

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
                    $notSeted = $key;
                    $varNotSeted .= ' [' . $key . '] ';
                }
            }

            foreach ($dataValidateAddress as $key) {
                if (!isset($payer['billingAddress'][$key])) {
                    $notSeted = $key;
                    $varNotSeted .= ' [' . $key . '] ';
                }
            }

            if ($notSeted)
                $this->setError('Error: The following data required were not informed: ' . $varNotSeted . '.');
            
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
    public function setUniqueID($id) {
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
    public function setReason($reason) {
        $this->reason = $reason;
        return $this;
    }

    /**
     * Method addPaymentWay()
     *
     * Add a payment's method
     *
     * @param string $way The payment method. Options: 'billet','financing','debit','creditCard','debitCard'.
     * @return void
     * @access public
     */
    public function addPaymentWay($way) {
        if (!isset($this->payment_ways[$way]))
            $this->setError("Error: Payment method unavailable");
        else
            $this->payment_way[] = $way;

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
    public function setPayer($payer) {
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
    public function setValue($value) {
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
    public function setAdds($value) {
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
    public function setDeduct($value) {
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
     * @access public
     */
    public function setReturnURL($url) {
        if (!isset($this->xml->InstrucaoUnica->URLRetorno)) {
            $this->xml->InstrucaoUnica->addChild('URLRetorno', $url);
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
     * @return void
     * @access public
     */
    public function setError($error) {
        $this->errors = $error;

        return $this;
//throw new InvalidArgumentException($error);
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
     * @param numeric $min The minimum number of parcels.
     * @param numeric $max The maximum number of parcels.
     * @param numeric $rate The percentual value of rates
     * @param boolean $tranfer "true" defines the amount of interest charged by MoIP installment to be paid by the payer
     * @return void
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

        if ($transfer == false) {
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
     * @return void
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

        $this->xml->InstrucaoUnica->addChild('IdProprio', $this->uniqueID);
        $this->xml->InstrucaoUnica->addChild('Razao', $this->reason);

        if (empty($this->value))
            $this->setError('Error: The transaction amount must be specified.');

        $this->xml->InstrucaoUnica->addChild('Valores')
                ->addChild('Valor', $this->value)
                ->addAttribute('moeda', 'BRL');

        if (isset($this->deduction)) {
            $this->xml->InstrucaoUnica->Valores->addChild('Deducao', $this->deduction)
                    ->addAttribute('moeda', 'BRL');
        }

        if (isset($this->adds)) {
            $this->xml->InstrucaoUnica->Valores->addChild('Acrescimo', $this->adds)
                    ->addAttribute('moeda', 'BRL');
        }

        if (!empty($this->payment_way)) {
            $instrucao = $this->xml->InstrucaoUnica;
            $formas = $instrucao->addChild('FormasPagamento');

            foreach ($this->payment_way as $way) {
                $formas->addChild('FormaPagamento', $this->payment_ways[$way]);
            }
        }


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

        $return = utf8_encode($this->xml->asXML());
        $this->initXMLObject();
        return str_ireplace("\n", "", $return);
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
     *
     * @return object
     * @access public
     */
    public function getAnswer($formato=null) {
        if ($this->answer->response == true) {
            if ($formato == "xml") {

                return $this->answer->xml;
            }

            $xml = new SimpleXmlElement($this->answer->xml);

            $return = (object) array();
            $return->response = $xml->Resposta->Status == 'Sucesso' ? true : false;
            $return->error = $xml->Resposta->Status == 'Falha' ? (string) utf8_decode($xml->Resposta->Erro) : false;
            $return->token = (string) $xml->Resposta->Token;
            $return->payment_url = $xml->Resposta->Status == 'Sucesso' ? (string) $this->environment->base_url . "/Instrucao.do?token=" . $return->token : false;

            return $return;
        } else {
            return $this->answer->error;
        }
    }

    /**
     * Method verifyParcelValues()
     *
     * Get all informations about the parcelling of user defined by $login_moip
     *
     * @param string $login_moip The client's login for Moip services
     * @param numeric $total_parcels The total parcels
     * @param numeric $rate The rate's percents of the parcelling.
     * @param numeric $simulated_value The value for simulation
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
        } else {
            return $answer;
        }


        return $return;
    }

}
?>