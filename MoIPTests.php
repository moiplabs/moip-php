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
  //method called before each test methodi
  private $validCredentials = array('token'=>'TLCSNDHJ2K3RT2SSIADPMZFBSSCUJC17',
                                    'key'=>'62TEXRGAELROYXRWJCAWKYZJWD1WWD8WBGDVH9R0'); 
  public function setUp()
  {
    $this->MoIP = new MoIP();
  }

  public function testVerificaSeExceptionEhLancadaSeCredenciaisInvalidasForemPassadas()
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

  public function testVerificaSeNaoOcorreExceptionsQuandoCredenciaisValidasForemPassadas()
  {
    $this->MoIP->setCredenciais($this->validCredentials);
  }

  public function testVerificaSeExceptionEhLancadaQuandoOAmbienteDeExecucaoInvalidoForPassado()
  {
    try
    {
      $this->MoIP->setAmbiente('ambiente.invalido');
      $this->fail('Erro: Não obtive uma exception ao informar um abiente inválido');
    }catch(InvalidArgumentException $e){}
  }

  public function testVerificaSeExceptionNaoEhLancadaQuandoOAmbienteDeExecucaoValidoForPassado()
  {
    $this->MoIP->setAmbiente('producao');
    $this->MoIP->setAmbiente('sandbox');
  }

  public function testVerificaSeQuandoDadosInsuficientesForemPassadosUmaExceptionEhLancada()
  {
    try
    {
      $this->MoIP->valida();
      $this->fail("Erro: não obtive uma exception ao deixar de informar campos inválidos");
    } catch (InvalidArgumentException $e){}

   try
   {
     $this->MoIP->setRazao('Pagamento de testes')->valida();
     $this->fail("Erro: não obtive exception ao especificar somente a razão");
   }catch(InvalidArgumentException $e){}
  }

  public function testVerificaSeQuandoDadosCorretosForemPassadosNenhumaExceptionEhLancada()
  {
    $this->MoIP->setRazao('Pagamento de testes')
               ->setCredenciais($this->validCredentials)
               ->setIDProprio(123456)
               ->valida();
  }

  public function testVerificaSeXMLGeradoEhValidoQuandoParametrosBasicosForemPassados()
  {

    $current = $this->MoIP->setRazao('Pagamento de testes')
                ->setCredenciais($this->validCredentials)
                ->setIDProprio(123456)
                ->valida();
    $expected = "<EnviarInstrucao><InstrucaoUnica><Razao>Pagamento de testes</Razao><IdProprio>123456</IdProprio></InstrucaoUnica></EnviarInstrucao>";
    $this->assertEquals($expected,$current->getXML(),"Instruções básicas");
    
    //com valor
    $current = $this->MoIP->setValor(123.45);

    $expected = '<EnviarInstrucao><InstrucaoUnica><Razao>Pagamento de testes</Razao><IdProprio>123456</IdProprio>'.
                 '<Valores><Valor moeda="BRL">123.45</Valor></Valores></InstrucaoUnica></EnviarInstrucao>';
    $this->assertEquals($expected,$current->getXML(),"Instruções básicas + valor");

    //com forma de pagamento em boleto
    $current->setFormaPagamento('boleto');
    $expected = '<EnviarInstrucao><InstrucaoUnica><Razao>Pagamento de testes</Razao><IdProprio>123456</IdProprio><Valores>'.
      '<Valor moeda="BRL">123.45</Valor></Valores><PagamentoDireto><Forma>BoletoBancario</Forma></PagamentoDireto>'.
      '</InstrucaoUnica></EnviarInstrucao>';
    $this->assertEquals($expected,$current->getXML(),"Instruções básicas com valor e forma de pagamento");
    
    //com forma de pagamento em boleto com instruções extra
    $current->setFormaPagamento('boleto',array('dias_expiracao'=>array('tipo'=>'Corridos','dias'=>5),
      'instrucoes'=>array('Nao receber apos o vencimento','Outra instrucao')));
    $expected = '<EnviarInstrucao><InstrucaoUnica><Razao>Pagamento de testes</Razao><IdProprio>123456</IdProprio><Valores>'.
      '<Valor moeda="BRL">123.45</Valor></Valores><PagamentoDireto><Forma>BoletoBancario</Forma></PagamentoDireto>'.
      '<Boleto><DiasExpiracao Tipo="Corridos">5</DiasExpiracao><Instrucao1>Nao receber apos o vencimento</Instrucao1>'.
      '<Instrucao2>Outra instrucao</Instrucao2></Boleto></InstrucaoUnica></EnviarInstrucao>';
    
    $this->assertEquals($expected,$current->getXML(),"Instruções básicas com valor e forma de pagamento com parâmetros");
  }

  public function testVerificaSeUmaExceptionEhLancadaQuandoAFormaDePagamentoNaoEstiverDisponivel()
  {
    try
    {
      $this->MoIP->setFormaPagamento('invalid');
      $this->fail('Erro: não houve exception ao setar uma forma de pagamento inválida');
    }
    catch(InvalidArgumentException $e){}
  }
  public function testVerificaSeOpcoesDePagamentoPadraoEstaoDisponiveis()
  {
    $this->MoIP->setFormaPagamento('boleto');
  }

  public function testVerificaSeARespostaDoServidorFoiRecebidaCorretamente()
  {
    $respostaFromMoIPClient = (object) array('erro'=>false,
                                       'resposta'=>'<ns1:EnviarInstrucaoUnicaResponse xmlns:ns1="http://www.moip.com.br/ws/alpha/"><Resposta><ID>201008080605083750000000012345</ID><Status>Sucesso</Status><Token>1230S1C7P0Z8L8M8C0Z6Q9F5B0V8Y3L7S5N0U0O0D0F0N060Z053Y2Y3Z2Q7</Token></Resposta></ns1:EnviarInstrucaoUnicaResponse>');
    
    
    $client = $this->getMock('MoIPClient',array('send'));

    $client->expects($this->any())
           ->method('send')
           ->will($this->returnValue($respostaFromMoIPClient));

    $resposta = $this->MoIP->setRazao('Pagamento de testes')
               ->setCredenciais($this->validCredentials)
               ->setIDProprio(123456)
               ->valida()
               ->envia($client)
               ->getResposta();
    
    $this->assertTrue($resposta->sucesso);
    $this->assertEquals(strlen($resposta->token),60);
  }

  public function testVerificaSeExceptionEhLancadaQuandoDadosDoBoletoSaoPassadosIncorretamente()
  {
    try
    {
      $this->MoIP->setFormaPagamento('boleto',array('nada'));
      $this->fail('Erro: deve haver uma exception quando os dados do boleto são especificados de forma incorreta');
    }
    catch(InvalidArgumentException $e){}

  }

  public function testVerificaSeNaoHaExceptionQuandoOsDadosDoBoletoSaoPassadosCorretamente()
  {
    $this->MoIP->setFormaPagamento('boleto',array('dias_expiracao'=>array('tipo'=>'corridos','dias'=>'5')));
  }

  //method called after each test method
  public function tearDown()
  {
    unset($this->MoIP);
  }
}
?>
