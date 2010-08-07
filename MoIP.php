<?php

/**
 * Abstração da API do MoIP para PHP
 * @author Herberth Amaral
 * @version 0.0.1
 * @package MoIP
 */
class MoIP
{
  
  function __construct()
  {
    
  }
  public function setCredenciais($credenciais)
  {
    throw new InvalidArgumentException("Credenciais inválidas");
  }
}
?>
