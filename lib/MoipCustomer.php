<?php 

/**
 * Library to interact with the Moip Subscriptions API
 * 
 * @author Gui Meira
 * @version 1.0
 * @license <a href="http://www.opensource.org/licenses/bsd-license.php">BSD License</a>
 */

/**
 * This class represents a Customer.
 */
 class MoipCustomer {
   /**
    * Customer's code
    * 
    * @var string
    */
   protected $code;
   
   /**
    * Customer's e-mail
    * 
    * @var string
    */
   protected $email;
   
   /**
    * Customer's full name
    * 
    * @var string
    */
   protected $fullname;
   
   /**
    * Customer's CPF
    * 
    * @var string
    */
   protected $cpf;
   /**
    * Customer's phone area code
    * 
    * @var string
    */
   protected $phone_area_code;
   
   /**
    * Customer's phone number
    * 
    * @var string
    */
   protected $phone_number;
   
   /**
    * Customer's birthdate day
    * 
    * @var int
    */
   protected $birthdate_day;
   
   /**
    * Customer's birthdate month
    * 
    * @var int
    */
   protected $birthdate_month;
   
   /**
    * Customer's birthdate year
    * 
    * @var int
    */
   protected $birthdate_year;
   
   /**
    * Customer's address. An associative array with the keys:
    * 'street' => street name
    * 'number' => the number
    * 'complement' => the complement
    * 'district' => the district
    * 'city' => the city name
    * 'state' => the state, with two letters
    * 'country' => the country, with three letters (BRA for Brazil)
    * 'zipcode' => the zipcode, only numbers, no dashes
    * 
    * @var array
    */
   protected $address;
   
   /**
    * Customer's billing info. An associative array with the key:
    * 'credit_card' => the credit card, represented by an associative array, containing the keys:
    *   'holder_name' => the full holder name
    *   'number' => the credit card number
    *   'expiration_month' => the month of expiration
    *   'expiration_year' => the year of expiration
    *   If the credit card is already stored on a vault, it's enough to provide only the following key:
    *   'vault' => the vault number
    * 
    * @var array
    */
   protected $billing_info;
   
   /**
    * Errors
    *
    * @var string
    */
   public $errors;
   
   protected $fields = array(
       'code' => 'setCode',
       'email' => 'setEmail',
       'fullname' => 'setFullName',
       'cpf' => 'setCPF',
       'phone_area_code' => 'setPhoneAreaCode',
       'phone_number' => 'setPhoneNumber',
       'birthdate_day' => 'setBirthdateDay',
       'birthdate_month' => 'setBirthdateMonth',
       'birthdate_year' => 'setBirthdateYear',
       'address' => 'setAddress',
       'billing_info' => 'setBillingInfo',
   );
   
   public function __construct($initial_values = null) {
     if($initial_values != null) {
       foreach($this->fields as $field => $setter) {
         if(isset($initial_values[$field])) {
           $this->$setter($initial_values[$field]); //invoke the setter to make sure the data is valid
         }
       }
     }
   }
   
   /**
    * Method setError()
    *
    * Set Error alert
    *
    * @param String $error Error alert
    * @return MoipPlan
    */
   public function setError($error) {
     $this->errors = $error;
   
     return $this;
   }
   
   /**
    * Method setCode()
    * 
    * Set the customer code.
    * 
    * @param string $code
    * @return MoipCustomer
    */
   public function setCode($code) {
     if(strlen($code) > 65) {
       $this->setError('Code cannot be greater than 65 characters.');
     }
     else {
       $this->code = $code;
     }
     
     return $this;
   }
   
   /**
    * Method setEmail()
    *
    * Set the customer's email.
    *
    * @param string $email
    * @return MoipCustomer
    */
   public function setEmail($email) {
     if(strpos($email,'@') === false || strpos($email,'.') === false) { //very simple validation, just to make sure it's not completely invalid.
       $this->setError('E-mail is not valid.');
     }
     else {
       $this->email = $email;
     }
      
     return $this;
   }
   
   /**
    * Method setFullName()
    *
    * Set the customer's full name.
    *
    * @param string $fullname
    * @return MoipCustomer
    */
   public function setFullName($fullname) {
     if(strlen($fullname) > 150) {
       $this->setError('Full name cannot be greater than 150 characters.');
     }
     else {
       $this->fullname = $fullname;
     }
      
     return $this;
   }
   
   /**
    * Method setCPF()
    *
    * Set the customer's CPF.
    *
    * @param string $cpf
    * @return MoipCustomer
    */
   public function setCPF($cpf) {
     if(!preg_match('/^[0-9]{11}$/',$cpf)) {
       $this->setError('CPF is not valid.');
     }
     else {
       $this->cpf = $cpf;
     }
      
     return $this;
   }
   
   /**
    * Method setPhoneAreaCode()
    *
    * Set the customer's phone area code.
    *
    * @param string $phone_area_code
    * @return MoipCustomer
    */
   public function setPhoneAreaCode($phone_area_code) {
     if(!is_numeric($phone_area_code)) {
       $this->setError('Phone area code is an integer number.');
     }
     else {
       $this->phone_area_code = $phone_area_code;
     }
      
     return $this;
   }
   
   /**
    * Method setPhoneNumber()
    *
    * Set the customer's phone number.
    *
    * @param string $phone_number
    * @return MoipCustomer
    */
   public function setPhoneNumber($phone_number) {
     if(!is_numeric($phone_number)) {
       $this->setError('Phone number must be numeric.');
     }
     else {
       $this->phone_number = $phone_number;
     }
      
     return $this;
   }
   
   /**
    * Method setBirthdateDay()
    *
    * Set the customer's birthdate day.
    *
    * @param int $birthdate_day
    * @return MoipCustomer
    */
   public function setBirthdateDay($birthdate_day) {
     if(!is_numeric($birthdate_day) || $birthdate_day > 31 || $birthdate_day < 1) {
       $this->setError('Birthdate day must be a number between 1 and 31.');
     }
     else {
       $this->birthdate_day = $birthdate_day;
     }
   
     return $this;
   }
   
   /**
    * Method setBirthdateMonth()
    *
    * Set the customer's birthdate month.
    *
    * @param int $birthdate_month
    * @return MoipCustomer
    */
   public function setBirthdateMonth($birthdate_month) {
     if(!is_numeric($birthdate_month) || $birthdate_month > 12 || $birthdate_month < 1) {
       $this->setError('Birthdate month must be a number between 1 and 12.');
     }
     else {
       $this->birthdate_month = $birthdate_month;
     }
      
     return $this;
   }
   
   /**
    * Method setBirthdateYear()
    *
    * Set the customer's birthdate year.
    *
    * @param int $birthdate_year
    * @return MoipCustomer
    */
   public function setBirthdateYear($birthdate_year) {
     if(!is_numeric($birthdate_year)) {
       $this->setError('Birthdate year must be a number.');
     }
     else {
       $this->birthdate_year = $birthdate_year;
     }
      
     return $this;
   }
   
   /**
    * Method setAddress()
    *
    * Set the customer's address.
    *
    * @param array $address
    * @return MoipCustomer
    */
   public function setAddress($address) {
     if(!isset($address['street']) ||
        !isset($address['number']) ||
        !isset($address['complement']) ||
        !isset($address['district']) ||
        !isset($address['city']) ||
        !isset($address['state']) ||
        !isset($address['country']) ||
        !isset($address['zipcode'])) {
       $this->setError('All address fields are mandatory.');
     }
     else {
       $this->address = $address;
     }
   
     return $this;
   }
   
   /**
    * Method setBillingInfo()
    *
    * Set the customer's billing info.
    *
    * @param array $billing_info
    * @return MoipCustomer
    */
   public function setBillingInfo($billing_info) {
     if(!isset($billing_info['credit_card']) ||
        (
          (
            !isset($billing_info['credit_card']['holder_name']) ||
            !isset($billing_info['credit_card']['number']) ||
            !isset($billing_info['credit_card']['expiration_month']) ||
            !isset($billing_info['credit_card']['expiration_year'])
          ) && (
            !isset($billing_info['credit_card']['vault'])
          )
        )
     ) {
       $this->setError('Provide credit card information or a vault number.');
     }
     else {
       $this->billing_info = $billing_info;
     }
      
     return $this;
   }
   
   public function hasCreditCard() {
     return isset($this->billing_info) && isset($this->billing_info['credit_card']);
   }
   
   public function validate() {
     if($this->code == null || $this->fullname == null || $this->email == null || $this->cpf == null || $this->phone_area_code == null
    || $this->phone_number== null || $this->birthdate_day == null || $this->birthdate_month == null || $this->birthdate_year == null
    || $this->address == null ) {
       $this->setError('Code, full name, e-mail, CPF, phone area code, phone number, birthdate day, birthdate month, birthdate year and address fields are mandatory.');
     }
   }
   
   /**
    * Method toJSON()
    * 
    * Generates the JSON representation of this object.
    * 
    * @return string
    */
   public function toJSON() {
     $json = array();
     
     foreach($this->fields as $field => $setter) {
       if($this->$field != null) {
         $json[$field] = $this->$field;
       }
     }
     
     return json_encode($json);
   }
   
   function __get($name)
   {
     if (isset($this->$name))
     {
       return $this->$name;
     }
     return null;
   }
 }