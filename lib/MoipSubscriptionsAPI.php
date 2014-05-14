<?php 
/**
 * Library to interact with the Moip Subscriptions API
 *
 * @author Gui Meira
 * @version 1.0
 * @license <a href="http://www.opensource.org/licenses/bsd-license.php">BSD License</a>
 */

/**
 * This class interacts with the Moip's Subscriptions API
 */
class MoipSubscriptionsAPI {
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
   * Errors
   *
   * @var string
   */
  public $errors;
  
  /**
   * Server's answer
   *
   * @var MoipResponse
   */
  public $answer;
  
  public function __construct() {
    $this->setEnvironment();
  }
  
  /**
   * Method setError()
   *
   * Set Error alert
   *
   * @param String $error Error alert
   * @return MoipSubscriptionsAPI
   * @access public
   */
  public function setError($error) {
    $this->errors = $error;
  
    return $this;
  }
  
  /**
   * Method setEnvironment()
   *
   * Define the environment for the API utilization.
   *
   * @param bool $testing If true, will use the sandbox environment
   * @return MoipSubscriptionsAPI
   */
  public function setEnvironment($testing = false) {
    if (empty($this->environment))
    {
      $this->environment = new MoipEnvironment();
    }
  
    if ($testing) {
      $this->environment->name = "Sandbox";
      $this->environment->base_url = "https://sandbox.moip.com.br/assinaturas/v1";
    } else {
      $this->environment->name = "Produção";
      $this->environment->base_url = "https://moip.com.br/assinaturas/v1";
    }
  
    return $this;
  }
  
  /**
   * Method setCredential()
   *
   * Set the credentials(key,token) required for the API authentication.
   *
   * @param array $credential Array with the credentials token and key
   * @return MoipSubscriptionsAPI
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
   * Method createPlan()
   * 
   * Tells Moip to create a plan.
   * 
   * @param MoipPlan $plan
   * @return MoipResponse the server response
   */
  public function createPlan($plan) {
    $plan->validate();
    
    if($plan->errors != null) {
      $this->setError($plan->errors);
    }
    
    return $this->curlPost($plan->toJSON(),'/plans');
  }
  
  public function getPlans() {
    $response = $this->curlGet('/plans', $this->errors);
    
    if($response->response) {
      $plans = array();
      
      foreach($response->data['plans'] as $plan) {
        $plans[$plan['code']] = new MoipPlan($plan);
      }
      
      return new MoipResponse(array('response' => true, 'error' => null, 'data' => $plans));
    } else {
      return $response;
    }
  }
  
  public function getPlan($code) {
    $response = $this->curlGet('/plans/' . $code);
    
    if($response->response) {
      $plan = new MoipPlan($response->data);
      return new MoipResponse(array('response' => true, 'error' => null, 'data' => $plan));
    } else {
      return $response;
    }
  }
  
  public function activatePlan($code) {
    return $this->curlPut(null,'/plans/'.$code.'/activate');
  }
  
  public function inactivatePlan($code) {
    return $this->curlPut(null,'/plans/'.$code.'/inactivate');
  }
  
  public function updatePlan($plan) {
    $plan->validate();
    
    if($plan->errors != null) {
      $this->setError($plan->errors);
    }
    
    return $this->curlPut($plan->toJSON(),'/plans/'.$plan->code);
  }
  
  /**
   * Method createCustomer()
   *
   * Tells Moip to create a customer.
   *
   * @param MoipCustomer $customer
   * @return MoipResponse the server response
   */
  public function createCustomer($customer, $new_vault = false) {
    $customer->validate();
  
    if($customer->errors != null) {
      $this->setError($customer->errors);
    }
  
    if($new_vault && (!$customer->hasCreditCard() || isset($customer->billing_info['credit_card']['vault']))) {
      $this->setError('Cannot create vault without credit card information.');
    }
    
    return $this->curlPost($customer->toJSON(),'/customers?newvault=' . ($new_vault ? 'true' : 'false'));
  }
  
  public function getCustomers() {
    $response = $this->curlGet('/customers', $this->errors);
  
    if($response->response) {
      $customers = array();
  
      foreach($response->data['customers'] as $customer) {
        $customers[$customer['code']] = new MoipCustomer($customer);
      }
  
      return new MoipResponse(array('response' => true, 'error' => null, 'data' => $customers));
    } else {
      return $response;
    }
  }
  
  public function getCustomer($code) {
    $response = $this->curlGet('/customers/' . $code);
  
    if($response->response) {
      $customer = new MoipCustomer($response->data);
      return new MoipResponse(array('response' => true, 'error' => null, 'data' => $customer));
    } else {
      return $response;
    }
  }
  
  public function updateCustomer($customer) {
    $customer->validate();
    
    if($customer->errors != null) {
      $this->setError($customer->errors);
    }
    
    return $this->curlPut($customer->toJSON(),'/customers/'.$customer->code);
  }
  
  public function updateCustomerBillingInfo($code, $billing_info) {
    return $this->curlPut(json_encode($billing_info), '/customers/'.$code.'/billing_infos');
  }
  
  public function suspendSubscription($code) {
	return $this->curlPut(null,'/subscriptions/'.$code.'/suspend');
  }
  
  public function activateSubscription($code) {
	return $this->curlPut(null,'/subscriptions/'.$code.'/activate');
  }
  
  public function cancelSubscription($code) {
	return $this->curlPut(null,'/subscriptions/'.$code.'/cancel');
  }
  
  /**
   * @param string $json json data
   * @param string $url url request
   * @param string $error errors
   * @return MoipResponse
   */
  private function curlPost($json, $url) {
  
    if (!$this->errors) {
      $header[] = 'Expect:';
      $header[] = 'Authorization: Basic ' . base64_encode($this->credential['token'] . ':' . $this->credential['key']);
      $header[] = 'Content-Type: application/json';
  
      $ch = curl_init();
      $options = array(CURLOPT_URL => $this->environment->base_url . $url,
          CURLOPT_HTTPHEADER => $header,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_POST => true,
          CURLOPT_POSTFIELDS => $json,
          CURLOPT_RETURNTRANSFER => true,
          CURLINFO_HEADER_OUT => true
      );
  
      curl_setopt_array($ch, $options);
      $ret = curl_exec($ch);
      $err = curl_error($ch);
      $info = curl_getinfo($ch);
      curl_close($ch);
      
      $obj = json_decode($ret, true);
      
      return new MoipResponse(array(
          'response' => (substr($info['http_code'],0,1) == '2'),
          'error' => isset($obj['errors']) ? $obj['message'] : null,
          'data' => $obj)
      );
    } else {
      return new MoipResponse(array('response' => false, 'error' => $this->errors, 'data' => null));
    }
  }
  
  
  /**
   * @param string $url url request
   * @param string $error errors
   * @return MoipResponse
   */
  private function curlGet($url) {
  
    if (!$this->errors) {
      $header[] = 'Expect:';
      $header[] = 'Authorization: Basic ' . base64_encode($this->credential['token'] . ':' . $this->credential['key']);
      $header[] = 'Content-Type: application/json';
  
      $ch = curl_init();
      $options = array(CURLOPT_URL => $this->environment->base_url . $url,
          CURLOPT_HTTPHEADER => $header,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_RETURNTRANSFER => true,
          CURLINFO_HEADER_OUT => true
      );
  
      curl_setopt_array($ch, $options);
      $ret = curl_exec($ch);
      $err = curl_error($ch);
      $info = curl_getinfo($ch);
      curl_close($ch);
      
      $obj = json_decode($ret, true);
      
      return new MoipResponse(array(
          'response' => (substr($info['http_code'],0,1) == '2'),
          'error' => isset($obj['errors']) ? $obj['message'] : null,
          'data' => $obj)
      );
    } else {
      return new MoipResponse(array('response' => false, 'error' => $this->errors, 'data' => null));
    }
  }
  
  /**
   * @param string $url url request
   * @param string $error errors
   * @return MoipResponse
   */
  private function curlPut($json, $url) {
  
    if (!$this->errors) {
      $header[] = 'Expect:';
      $header[] = 'Authorization: Basic ' . base64_encode($this->credential['token'] . ':' . $this->credential['key']);
      $header[] = 'Content-Type: application/json';
      
      if($json != null)
        $header[] = 'Content-Length: '.strlen($json);
  
      $ch = curl_init();
      $options = array(CURLOPT_URL => $this->environment->base_url . $url,
          CURLOPT_HTTPHEADER => $header,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_CUSTOMREQUEST => 'PUT',
          CURLOPT_RETURNTRANSFER => true,
          CURLINFO_HEADER_OUT => true
      );
      curl_setopt_array($ch, $options);
      
      if($json != null)
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
      
      $ret = curl_exec($ch);
      $err = curl_error($ch);
      $info = curl_getinfo($ch);
      curl_close($ch);
  
      $obj = json_decode($ret, true);
  
      return new MoipResponse(array(
          'response' => (substr($info['http_code'],0,1) == '2'),
          'error' => isset($obj['errors']) ? $obj['message'] : null,
          'data' => $obj)
      );
    } else {
      return new MoipResponse(array('response' => false, 'error' => $this->errors, 'data' => null));
    }
  }
}