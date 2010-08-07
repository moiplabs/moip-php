<?php
require_once 'PHPUnit/Framework.php';
require_once 'MoIP.php';

/**
 * Testes unitários da lib MoIP
 * 
 * @author Herberth Amaral
 * @version 0.0.1
 * @package MoIP
 */
class MoIPTests extends PHPUnit_Framework_TestCase
{
  //method called before each test method
  public function setUp()
  {
    $this->MoIP = new MoIP();
  }

  public function testVerificaSeTokenEKeyForamPassadosCorretamente()
  {
    try
    {
      $this->MoIP->setCredenciais(array('token'=>'token','key'=>'key'));
      $this->fail('Erro: Não obtive uma exception ao informar um token e uma key inválida ');
    }
    catch(InvalidArgumentException $e)
    {
      
    }
   
  }

  //method called after each test method
  public function tearDown()
  {
    unset($this->MoIP);
  }
}

?>
