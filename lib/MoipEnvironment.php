<?php 

class MoipEnvironment {
  public $base_url;
  public $name;

  function __construct($base_url = '', $name = '')
  {
    $this->base_url = $base_url;
    $this->name = $name;
  }
}