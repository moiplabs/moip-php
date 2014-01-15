<?php 

/**
 * Library to interact with the Moip Subscriptions API
 * 
 * @author Gui Meira
 * @version 1.0
 * @license <a href="http://www.opensource.org/licenses/bsd-license.php">BSD License</a>
 */

/**
 * This class represents a Moip Plan
 */
 class MoipPlan {
   /**
    * Code of the plan (65 characters)
    * 
    * @var string
    */
   protected $code;
   
   /**
    * Name of the plan (65 characters)
    * 
    * @var string
    */
   protected $name;
   
   /**
    * Description of the plan (255 characters)
    * 
    * @var string
    */
   protected $description;
   
   /**
    * Price of the plan
    * 
    * @var int
    */
   protected $amount;
   /**
    * Setup fee
    * 
    * @var int
    */
   protected $setup_fee;
   
   /**
    * Maximum number of subscriptions. Default is unlimited
    * 
    * @var int
    */
   protected $max_qty;
   
   /**
    * Plan status (ACTIVE or INACTIVE)
    * 
    * @var string
    */
   protected $status;
   
   /**
    * Associative array with the keys:
    * 'length' => integer
    * 'unit' => string ('DAY', 'MONTH' or 'YEAR')
    * 
    * @var array
    */
   protected $interval;
   
   /**
    * Number of billing cycles before the subscription expires. Default is unlimited
    * 
    * @var int
    */
   protected $billing_cycles;
   
   /**
    * Associative array with the keys:
    * 'days' => integer, the number of trial days
    * 'enabled' => 'TRUE' or 'FALSE', indicating if the trial is enabled. Default is 'FALSE'
    * @var unknown
    */
   protected $trial;
   
   /**
    * Errors
    *
    * @var string
    */
   public $errors;
   
   protected $fields = array(
       'code' => 'setCode',
       'name' => 'setName',
       'description' => 'setDescription',
       'amount' => 'setAmount',
       'setup_fee' => 'setSetupFee',
       'max_qty' => 'setMaxQty',
       'status' => 'setStatus',
       'interval' => 'setInterval',
       'billing_cycles' => 'setBillingCycles',
       'trial' => 'setTrial'
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
    * Set the plan code.
    * 
    * @param string $code
    * @return MoipPlan
    */
   public function setCode($code) {
     if(strlen($code) > 64) {
       $this->setError('Code cannot be greater than 65 characters.');
     }
     else {
       $this->code = $code;
     }
     
     return $this;
   }
   
   /**
    * Method setName()
    *
    * Set the plan name.
    *
    * @param string $name
    * @return MoipPlan
    */
   public function setName($name) {
     if(strlen($name) > 64) {
       $this->setError('Name cannot be greater than 65 characters.');
     }
     else {
       $this->name = $name;
     }
      
     return $this;
   }
   
   /**
    * Method setDescription()
    *
    * Set the plan description.
    *
    * @param string $description
    * @return MoipPlan
    */
   public function setDescription($description) {
     if(strlen($description) > 255) {
       $this->setError('Description cannot be greater than 255 characters.');
     }
     else {
       $this->description = $description;
     }
      
     return $this;
   }
   
   /**
    * Method setAmount()
    *
    * Set the plan price.
    *
    * @param int $amount
    * @return MoipPlan
    */
   public function setAmount($amount) {
     if(!is_numeric($amount)) {
       $this->setError('Amount is an integer number.');
     }
     else {
       $this->amount = $amount;
     }
      
     return $this;
   }
   
   /**
    * Method setSetupFee()
    *
    * Set the plan setup fee.
    *
    * @param int $setup_fee
    * @return MoipPlan
    */
   public function setSetupFee($setup_fee) {
     if(!is_numeric($setup_fee)) {
       $this->setError('Setup fee is an integer number.');
     }
     else {
       $this->setup_fee = $setup_fee;
     }
      
     return $this;
   }
   
   /**
    * Method setMaxQty()
    *
    * Set the plan maximum quantity.
    *
    * @param int $max_quantity
    * @return MoipPlan
    */
   public function setMaxQty($max_qty) {
     if(!is_numeric($max_qty)) {
       $this->setError('Max quantity is an integer number.');
     }
     else {
       $this->max_qty = $max_qty;
     }
      
     return $this;
   }
   
   /**
    * Method setStatus()
    *
    * Set the plan status.
    *
    * @param string $status
    * @return MoipPlan
    */
   public function setStatus($status) {
     if($status != 'ACTIVE' && $status != 'INACTIVE') {
       $this->setError('Status must be ACTIVE or INACTIVE.');
     }
     else {
       $this->status = $status;
     }
   
     return $this;
   }
   
   /**
    * Method setInterval()
    *
    * Set the plan interval.
    *
    * @param array $interval
    * @return MoipPlan
    */
   public function setInterval($interval) {
     if(!isset($interval['unit']) || !isset($interval['length']) || !in_array($interval['unit'], array('DAY', 'MONTH', 'YEAR')) || !is_numeric($interval['length'])) {
       $this->setError('Interval must be an array containing the keys \'length\' and \'unit\'.');
     }
     else {
       $this->interval = $interval;
     }
      
     return $this;
   }
   
   /**
    * Method setBillingCycles()
    *
    * Set the plan billing cycles.
    *
    * @param int $billing_cycles
    * @return MoipPlan
    */
   public function setBillingCycles($billing_cycles) {
     if(!is_numeric($billing_cycles)) {
       $this->setError('Billing cycles is an integer number.');
     }
     else {
       $this->billing_cycles = $billing_cycles;
     }
      
     return $this;
   }
   
   /**
    * Method setTrial()
    *
    * Set the plan trial.
    *
    * @param array $trial
    * @return MoipPlan
    */
   public function setTrial($trial) {
     if(!isset($trial['days']) || !isset($trial['enabled']) || !in_array($trial['enabled'], array('TRUE', 'FALSE')) || !is_numeric($trial['days'])) {
       $this->setError('Trial must be an array containing the keys \'days\' and \'enabled\'.');
     }
     else {
       $this->trial = $trial;
     }
   
     return $this;
   }
   
   public function validate() {
     if($this->code == null || $this->name == null || $this->amount == null) {
       $this->setError('Code, name and amount are mandatory.');
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