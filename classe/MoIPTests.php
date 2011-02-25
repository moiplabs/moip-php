<?php
require_once 'PHPUnit/Framework.php';
require_once 'MoIP.php';

/**
 * Testes unitários da lib MoIP
 * 
 * @author Herberth Amaral
 * @version 0.4.3 
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
            ->setValor('12345')
            ->addFormaPagamento('boleto',array('dias_expiracao'=>array('tipo'=>'Corridos','dias'=>5),
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
            $this->MoIP->addFormaPagamento('invalid');
            $this->fail('Erro: não houve exception ao setar uma forma de pagamento inválida');
        }
        catch(InvalidArgumentException $e){}
    }
    public function testVerificaSeOpcoesDePagamentoPadraoEstaoDisponiveis()
    {
        $this->MoIP->addFormaPagamento('boleto');
        $this->MoIP->addFormaPagamento('financiamento');
        $this->MoIP->addFormaPagamento('debito');
        $this->MoIP->addFormaPagamento('cartao_credito');
        $this->MoIP->addFormaPagamento('cartao_debito');
        $this->MoIP->addFormaPagamento('carteira_moip');
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
            ->setValor('123456')
            ->valida()
            ->envia($client)
            ->getResposta();

        $this->assertTrue($resposta->sucesso);
        $this->assertEquals(strlen($resposta->token),60);
    }

    public function testMetodoChecarPagamentoDiretoDeveLancarUmaExceptionQuandoOsDadosDeAuthNaoForemPassados()
    {
        try
        {
            $this->MoIP->checarPagamentoDireto('login');
            $this->fail('checarPagamentoDireto deveria lancar uma exception quando os dados de auth não forem passados');
        }
        catch(Exception $e)
        {

        }
    }
    public function testChecarPagamentoDiretoDeveRetornarUmObjetoMoIPCheckQuandoARespostaDoServerForValida()
    {
        $respostaFromMoIPClient =(object) array('erro'=>false,'resposta'=>'<ns1:ChecarPagamentoDiretoResponse xmlns:ns1="http://www.moip.com.br/ws/alpha/"><Resposta><ID>201008241612518190000002974464</ID><Status>Sucesso</Status><CarteiraMoIP>false</CarteiraMoIP><CartaoCredito>true</CartaoCredito><CartaoDebito>false</CartaoDebito><DebitoBancario>true</DebitoBancario><FinanciamentoBancario>false</FinanciamentoBancario><BoletoBancario>true</BoletoBancario><DebitoAutomatico>false</DebitoAutomatico></Resposta></ns1:ChecarPagamentoDiretoResponse>');
        $client = $this->getMock('MoIPClient',array('send'));

        $client->expects($this->any())
            ->method('send')
            ->will($this->returnValue($respostaFromMoIPClient));

        $resposta = $this->MoIP->setCredenciais($this->validCredentials)->checarPagamentoDireto('login',$client);
        $this->assertFalse($resposta->erro);
        $this->assertEquals($resposta->id,'201008241612518190000002974464');
        $this->assertTrue($resposta->sucesso);
        $this->assertFalse($resposta->carteira_moip);
        $this->assertTrue($resposta->cartao_credito);
        $this->assertFalse($resposta->cartao_debito);
        $this->assertTrue($resposta->debito_bancario);
        $this->assertFalse($resposta->financiamento_bancario);
        $this->assertTrue($resposta->boleto_bancario);
        $this->assertFalse($resposta->debito_automatico);
    }
    
    public function testChecarValoresParcelamentoDeveLancarUmaExceptionQuandoCredenciaisNaoForemInformadas()
    {
        try
        {
            $this->MoIP->checarValoresParcelamento('login_moip',12,1.99,100);
            $this->fail('O Método checarValoresParcelamento deve lançar uma exception quando as credenciais não forem informadas');
        }
        catch(Exception $e)
        {
        }
    }

    public function testVerificaSeOMetodoDeParcelamentoEstaRetornandoOsValoresCorretosDeAcordoComARespostaDoServer()
    {
        $respostaFromMoIPClient = (object)array('erro'=>false,'resposta'=>'
            <ns1:ChecarValoresParcelamentoResponse
            xmlns:ns1="http://www.moip.com.br/ws/alpha/">
            <Resposta>
            <ID>201008241556253120000000034351</ID>
            <Status>Sucesso</Status>
            <ValorDaParcela Total="150.00" Juros="2.99" Valor="150.00">1</ValorDaParcela>
            <ValorDaParcela Total="156.76" Juros="2.99" Valor="78.38">2</ValorDaParcela>
            <ValorDaParcela Total="159.06" Juros="2.99" Valor="53.02">3</ValorDaParcela>
            <ValorDaParcela Total="161.36" Juros="2.99" Valor="40.34">4</ValorDaParcela>
            <ValorDaParcela Total="163.70" Juros="2.99" Valor="32.74">5</ValorDaParcela>
            <ValorDaParcela Total="166.08" Juros="2.99" Valor="27.68">6</ValorDaParcela>
            <ValorDaParcela Total="168.49" Juros="2.99" Valor="24.07">7</ValorDaParcela>
            <ValorDaParcela Total="170.88" Juros="2.99" Valor="21.36">8</ValorDaParcela>
            </Resposta>
            </ns1:ChecarValoresParcelamentoResponse>');

        $client = $this->getMock('MoIPClient',array('send'));

        $client->expects($this->any())
            ->method('send')
            ->will($this->returnValue($respostaFromMoIPClient));

        $expected = array('sucesso'=>true,'id'=>'201008241556253120000000034351',
            'parcelas'=>array('1'=>array('total'=>'150.00','juros'=>'2.99','valor'=>'150.00'),
                              '2'=>array('total'=>'156.76','juros'=>'2.99','valor'=>'78.38'),
                              '3'=>array('total'=>'159.06','juros'=>'2.99','valor'=>'53.02'),
                              '4'=>array('total'=>'161.36','juros'=>'2.99','valor'=>'40.34'),
                              '5'=>array('total'=>'163.70','juros'=>'2.99','valor'=>'32.74'),
                              '6'=>array('total'=>'166.08','juros'=>'2.99','valor'=>'27.68'),
                              '7'=>array('total'=>'168.49','juros'=>'2.99','valor'=>'24.07'),
                              '8'=>array('total'=>'170.88','juros'=>'2.99','valor'=>'21.36'),
        ));

        $this->MoIP->setCredenciais($this->validCredentials);
        $current = $this->MoIP->checarValoresParcelamento('blah',12,1.99,100,$client);

        $this->assertEquals($expected,$current);
    }

    public function testVerificaSeExceptionEhLancadaQuandoDadosDoBoletoSaoPassadosIncorretamente()
    {
        try
        {
            $this->MoIP->addFormaPagamento('boleto',array('nada'));
            $this->fail('Erro: deve haver uma exception quando os dados do boleto são especificados de forma incorreta');
        }
        catch(InvalidArgumentException $e){}

    }

    public function testVerificaSeNaoHaExceptionQuandoOsDadosDoBoletoSaoPassadosCorretamente()
    {
        $this->MoIP->addFormaPagamento('boleto',array('dias_expiracao'=>array('tipo'=>'corridos','dias'=>'5')));
    }

    public function testVerificaSeExceptionEhLancadaQuandoDadosDoPagadorSaoPassadosIncorretamente()
    {
        $pagador = array();
        try
        {
            $this->MoIP->setPagador($pagador);
            $this->MoIP->setTipoPagamento('Direto');
            $this->MoIP->valida();
            $this->fail('Erro: deve haver uma exception quando dos dados do pagador são especificados de forma incorreta');
        }catch (InvalidArgumentException $e){}
    }

    public function testVerificaSeExceptionEhLancadaQuandoOValorNaoEhEspecificado()
    {
        try
        {
            $this->MoIP->setIDProprio('123456')->setRazao('Razao do pagamento')->getXML();
            $this->fail('Erro: uma exception deve ser lançada quando o valor não for especificado');
        }catch(InvalidArgumentException $e){}
    }

    public function testVerificaSeExceptionEhLancadaQuandoOsParametrosDeComissaoSaoPassadosIncorretamente()
    {
        try
        {
            $this->MoIP->addComissao(array());
            $this->fail('Erro: uma exception deveria ser lançada se os parametros de comissão fossem passados incorretamente');
        }catch(InvalidArgumentException $e){}

            try
            {
                $this->MoIP->addComissao(array('login_moip'=>'blah'));
                $this->fail('Erro: uma exception deveria ser lançada se o valor não for informado');
            }catch(InvalidArgumentException $e){}

                try
                {
                    $this->MoIP->addComissao(array('login_moip'=>'blah','valor_fixo'=>'10','valor_percentual'=>'15'));
                    $this->fail('Erro: uma exception deveria ser lançada se mais de um valor for informado');
                }
        catch(InvalidArgumentException $e){}
    }

    public function testVerificaSeExceptionNaoEhLancadaQuandoDadosDoPagadorSaoPassadosCorretamente()
    {
        $this->MoIP->setPagador($this->pagadorValido);
    }

    public function testCertificaQueUmArrayVazioNaoPodeSerPassadoComoArgumentoDeEntrega()
    {
        try
        {
            $this->MoIP->addEntrega(array());
            $this->fail('Erro: uma exception deveria ser lançada caso os parametros necessários da entrega não fossem informados');
        }
        catch(InvalidArgumentException $e){}
    }

    public function testVerificaSeExceptionEhLancandaQuandoUmDosParametrosNecessariosNaoSaoPassados()
    {
        try
        {
            $this->MoIP->addEntrega(array('tipo'=>'proprio')); 
            $this->fail('Erro: uma exception deveria ser lançada caso os parametros necessários da entrega não fossem informados');
        }catch(InvalidArgumentException $e){}

            try
            {
                $this->MoIP->addEntrega(array('prazo'=>'5')); 
                $this->fail('Erro: uma exception deveria ser lançada caso os parametros necessários da entrega não fossem informados');

            }catch(InvalidArgumentException $e){}
    }

    public function testVerificaSeExceptionEhLancadaQuandoUmTipoDeFreteInvalidoEhPassado()
    {
        try
        {
            $this->MoIP->addEntrega(array('prazo'=>'5','tipo'=>'blah'));
            $this->fail('Erro: uma exception deveria ser lançada quando um tipo inválido for passado');
        }
        catch(InvalidArgumentException $e){}
    }

    public function testVerificaSeExceptionEhLancadaQuandoUmPrazoInvalidoEhEspecificado()
    {
        try
        {
            $this->MoIP->addEntrega(array('prazo'=>array('tipo'=>'blah','dias'=>'5'),'tipo'=>'proprio'));
            $this->fail('Erro: uma exception deveria ser lançada quando um tipo de prazo inválido for passado');
        }
        catch(InvalidArgumentException $e){}
    }

    public function testVerificaSeExceptionEhLancadaQuandoParametrosDosCorreiosEstaoVazios()
    {
        try
        {
            $this->MoIP->addEntrega(array('prazo'=>'5','tipo'=>'correios'));
            $this->fail('Erro: uma exception deveria ser lançada quando os correios não forem passados corretamente');
        }
        catch(InvalidArgumentException $e){}
    }

    public function testVerificaSeExceptionEhLancadaQuandoParametrosDosCorreiosNaoSaoEspecificadosCorretamente()
    {
        try
        {
            $this->MoIP->addEntrega(array('prazo'=>'5','tipo'=>'correios','correios'=>array()));
            $this->fail('Erro: Uma exception deveria ser lançada quando os parâmetros dos correios não forem passados corretamente');
        }
        catch (InvalidArgumentException $e){}

            try
            {
                $this->MoIP->addEntrega(array('prazo'=>'5','tipo'=>'correios','correios'=>array('peso_total'=>'1.1')));
                $this->fail('Erro: Uma exception deveria ser lançada quando os parâmetros dos correios não forem passados corretamente');
            }
        catch (InvalidArgumentException $e){}

            try
            {
                $this->MoIP->addEntrega(array('prazo'=>'5','tipo'=>'correios','correios'=>array('forma_entrega'=>'EncomendaNormal')));
                $this->fail('Erro: Uma exception deveria ser lançada quando os parâmetros dos correios não forem passados corretamente');
            }
        catch (InvalidArgumentException $e){}

    }

    public function testVerificaSeExceptionEhLancadaQuandoTipoDoPrazoEhEspecificadoIncorretamente()
    {
        try
        {
            $this->MoIP->addEntrega(array('tipo'=>'proprio','valor'=>'2.30','prazo'=>array('tipo'=>'uteis','dia'=>'3')));
            $this->fail('Erro: quando o numero de dias não eh passado, deve ocorrer uma exception.');
        }catch (InvalidArgumentException $e){}
    }

    public function testVerificaSeExceptionEhLancadaQuandoNenhumValorEhPassadoEOTipoEhProprio()
    {
        try
        {
            $this->MoIP->addEntrega(array('tipo'=>'proprio','prazo'=>array('tipo'=>'corridos','dias'=>'3')));
            $this->fail('Erro: uma exception deveria ser lançada quando o tipo de frete é próprio mas não há nenhum valor, fixo ou percentual');
        }catch(InvalidArgumentException $e){}
    }

    public function testVerificaSeNaoHaNenhumaExceptionQuandoOsParametrosDoAddEntregaSaoPassadosCorretamente()
    {
        $this->MoIP->addEntrega(array('tipo'=>'proprio','valor_fixo'=>'2.30','prazo'=>array('tipo'=>'corridos','dias'=>'3')));

        $this->MoIP->addEntrega(array('tipo'=>'correios',

            'prazo'=>array('tipo'=>'corridos','dias'=>'3'),
            'correios'=>array('peso'=>'10','forma_entrega'=>'EncomendaNormal')));

        $this->MoIP->addEntrega(array('tipo'=>'correios',                              
            'prazo'=>array('tipo'=>'corridos','dias'=>'3'),
            'correios'=>array('peso'=>'10','forma_entrega'=>'Sedex10')));

    }
    
    public function testVerificaSeUmaExceptionEhLancadaQuandoSetPagamentoDiretoForChamadoSemArgumentos()
    {
       
        try
        {
            
            $this->MoIP->setPagamentoDireto(array());
            $this->fail("A chamada do método setPagamentoDireto deveria lançar uma exception se o ".
                "mesmo for chamado com argumentos insuficientes.");

        }
        catch(InvalidArgumentException $e)
        {
        }
    }

    public function testVerificaSeUmaExceptionEhLancadaQuandoUmaFormaInvalidaEhSelecionada()
    {
        // to be implemented
    }

    public function testVerificaSeUmaExceptionEhLancadaQuandoOPagamentoDiretoEhSetadoComoDebitoBancarioSemInstituicao()
    {
        try
        {
            $this->MoIP->setPagamentoDireto(array('forma'=>'debito'));
            $this->fail("A chamada do método setPagamentoDireto deveria lançar uma exception se a ".
                "forma de pagamento for via débito bancário");
        }
        catch(InvalidArgumentException $e)
        {
        }
    }

    public function testVerificaSeUmaExceptionEhLancadaQuandoOPagamentoDiretoEhSetadoComoDebitoBancarioComInstituicaoInvalida()
    {
        try
        {
            $this->MoIP->setPagamentoDireto(array('forma'=>'debito','instituicao'=>'blah'));
            $this->fail("O método setPagamentoDireto deveria lançar uma exception quando a instituição for inválida");
        }
        catch(InvalidArgumentException $e)
        {
        }
        
        //this should work
        $this->MoIP->setPagamentoDireto(array('forma'=>'debito','instituicao'=>'real'));

    }

    public function testVerificaSeNaoHaErroQuandoPagamentoDiretoEhPorBoletoBancario()
    {
        $this->MoIP->setPagamentoDireto(array('forma'=>'boleto'));
    }

    public function testVerificaSeUmaExceptionEhLancadaQuandoUmaInstituicaoEhInvalidaEOPagamentoEhViaCartao()
    {
        try
        {
            $this->MoIP->setPagamentoDireto(array('forma'=>'debito','instituicao'=>'blah'));
            $this->fail("O método setPagamentoDireto deveria lançar uma exception quando a instituição é inválida");
        }
        catch(InvalidArgumentException $e)
        {
        }

    }

    public function testVerificaSeExceptionNaoEhLancadaQuandoDadosDoCartaoSaoPassadosCorretamente()
    {
        try
        {
            $this->MoIP->setPagamentoDireto(
                array('forma'=>'cartao_credito',
                      'instituicao'=>'american_express',
                      'cartao'=>array('numero'=>345678901234564,
                                      'expiracao'=>'08/11',
                                      'codigo_seguranca'=>'1234',
                                      'portador'=>array('nome'=>'Nome do Portador',
                                                  'identidade_tipo' => 'cpf',
                                                  'identidade_numero' => '111.111.111-11',
                                                  'telefone' => '(11) 1111-1111',
                                                  'data_nascimento' => '30/11/1980'
                                              ),
                                      'parcelamento' => array('parcelas'=>2,'recebimento'=>'avista')
                                     )
                     ));

        } 
        catch(InvalidArgumentException $e)
        {
            $this->fail('Exception não deveria ser lançada quando os dados do cartão estão corretos');
        }
    }


    //method called after each test method
    public function tearDown()
    {
        unset($this->MoIP);
    }
}
?>
