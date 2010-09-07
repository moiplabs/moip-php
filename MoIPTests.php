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

  private $pagadorValido = array('nome'=>'Luiz Inácio Lula da Silva',
                                 'login_moip'=>'lula',
                                 'email'=>'presidente@planalto.com.br',
                                 'celular'=>'(61)9999-9999',
                                 'apelido'=>'Lula',
                                 'identidade'=>'111.111.111-11',
                                 'endereco'=>array('logradouro'=>'Praça dos Três Poderes',
                                                   'numero'=>'0',
                                                   'complemento'=>'Palácio do Planalto',
                                                   'bairro'=>'Zona Cívico-Admnistrativa',
                                                   'cidade'=>'Brasília',
                                                   'estado'=>'DF',
                                                   'pais'=>'BRA',
                                                   'cep'=>'70100-000',
                                                   'telefone'=>'(61)3211-1221'));
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
    //com forma de pagamento em boleto com instruções extra
    $current = new MoIP(); 
    $current->setIDProprio(123456)
            ->setRazao('Pagamento de testes')
            ->setFormaPagamento('boleto',array('dias_expiracao'=>array('tipo'=>'Corridos','dias'=>5),
                                'instrucoes'=>array('Nao receber apos o vencimento','Outra instrucao'))); 
    $xml = new SimpleXmlElement($current->getXML()); 

    
    $this->assertEquals((int)$xml->InstrucaoUnica->Boleto->DiasExpiracao,5);
    $this->assertEquals((string)$xml->InstrucaoUnica->Boleto->DiasExpiracao["Tipo"],"Corridos");
    $this->assertEquals((string)$xml->InstrucaoUnica->Boleto->Instrucao1,"Nao receber apos o vencimento");
    $this->assertEquals((string)$xml->InstrucaoUnica->Boleto->Instrucao2,"Outra instrucao");
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

  public function testVerificaSeExceptionEhLancadaQuandoDadosDoPagadorSaoPassadosIncorretamente()
  {
    $pagador = array();
    try
    {
      $this->MoIP->setPagador($pagador);
      $this->fail('Erro: deve haver uma exception quando dos dados do pagador são especificados de forma incorreta');
    }catch (InvalidArgumentException $e){}
  }

  public function testVerificaSeExceptionNaoEhLancadaQuandoDadosDoPagadorSaoPassadosCorretamente()
  {

    $this->MoIP->setPagador($this->pagadorValido);
  }

  //method called after each test method
  public function tearDown()
  {
    unset($this->MoIP);
  }
}
?>
