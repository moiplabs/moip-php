SDK Moip-PHP - API
====================================================

O Moip-PHP � uma biblioteca que implementa uma camada de abstra��o para gera��o do XML de instru��es do Moip, permitindo que voc� integre aos servi�os de API sem poluir seu c�digo com v�rias linhas de XML. Um exemplo r�pido:

    include __DIR__ . '/vendor/autoload.php';
 
    use Moip\Moip;
    
    $moip = new Moip();
    $moip->setEnvironment('test');
    $moip->setCredential(array(
        'key' => 'ABABABABABABABABABABABABABABABABABABABAB',
        'token' => '01010101010101010101010101010101'
        ));
 
    $moip->setUniqueID(false);
    $moip->setValue('100.00');
    $moip->setReason('Teste do Moip-PHP');
 
    $moip->validate('Basic');
 
    print_r($moip->send());
	

O Moip-PHP utiliza o padr�o Fluent Interfaces, portanto, voc� pode fazer o exemplo acima da seguinte forma:

    include __DIR__ . '/vendor/autoload.php';
     
    use Moip\Moip;
 
    $moip = new Moip(); 
    print_r($moip->setEnvironment('test')
            ->setCredential(array(
        'key' => 'ABABABABABABABABABABABABABABABABABABABAB',
        'token' => '01010101010101010101010101010101'
        ))->setUniqueID(false)
            ->setValue('100.00')
            ->setReason('Teste do Moip-PHP')
            ->validate('Basic')
            ->send());
-------------------------------------

M�todos dispon�veis
----------
Veja baixo rela��o e detalhes dos m�todos dispon�veis que voc� poder� utilizar com o Moip-PHP.


-------------------------------------

Moip()
----------
M�todo construtor.

Moip()

    $moip = new Moip();
-------------------------------------

setEnvironment()
----------
M�todo que define o ambiente em qual o requisi��o ser� processada, 'test' para definir que ser� em ambiente de testes Moip o Sandbox, a omiss�o desse m�todo define que a requisi��o dever� ser processada em ambiente real, de produ��o Moip.


Importante: ao definir o ambiente certifique-se de que est� utilizando a autentica��o correspondente ao ambiente, no Moip cada ambiente possui suas pr�pria chaves de autentica��o API.

setEnvironment($environment)
$environment : String ('test')

	$moip->setEnvironment('test');
-------------------------------------

setCredential()
----------
O Moip requer que voc� se autentique para que seja possivel processar requisi��es em sua API, para isso antes de realizar qualquer requisi��o voc� dever� informar ao Moip suas credenciais da API formados por um TOKEN e uma KEY.

O par�metro $credencials � um array associativo contendo as chaves key e token (ex: array('key'=>'sua_key','token'=>'seu_token')). Se voc� ainda n�o possui estes dados, veja como obtelas tarv�s em sua conta Sandbox.

 setCredential($credential)

 $credential : Array('key','token')

	$moip->setCredential(array(
	        'key' => 'SUA_KEY',
        	'token' => 'SEU_TOKEN'
        	));

-------------------------------------

validate()
----------
O m�todo validate() ir� realizar a valida��o dos dados obrigat�rios para o tipo de instru��o que voc� deseja processar, voc� pode optar por um dos dois n�veis de valida��o dispon�veis o 'Basic' e 'Identification'.

1. Basic : Ir� realizar a valida��o dos dados m�nimos de para uma requisi��o XML ao Moip.
2. Identification : Ir� validar os dados necess�rios para se processar um XML com identifica��o Moip, usados geralmente para redirecionar o cliente j� no segundo step da pagina de pagamento no checkout Moip ou usar o Moip Transparente.

 validate($validateType)

 $validateType : String ('Basic' ou 'Identification')

	$moip->validate('Identification');

-------------------------------------

setUniqueID()
----------
O m�todo setUniqueID() atribui valor a tag "&lt;IdProprio&gt;" no XML Moip.

1. &lt;IdProprio&gt;: Seu identificador �nico de pedido, essa mesma informa��es ser� enviada para voc� em nossas notifica��es de altera��es de status para que voc� possa identificar o pedido e tratar seu status.

setUniqueID($id)

$id : String

	$moip->setUniqueID('ABCD123456789');
-------------------------------------

setValue()
----------

O m�todo setValue() atribui valor a tag "&lt;Valor&gt;" no XML Moip.

1. &lt;Valor&gt;:  Respons�vel por definir o valor que dever� ser pago.

setValue($value)

$value : Numeric

	$moip->setValue('100.00');	
-------------------------------------

setAdds()
---------------
O m�todo setAdds() atribui valor a tag "&lt;Acrescimo&gt;" no XML Moip.

1. &lt;Acrescimo&gt;:  Respons�vel por definir o valor adicional que dever� ser pago.

setAdds($value)

$value : Numeric

	$moip->setAdds('15.00');	
-------------------------------------

setDeduct()
---------------

O m�todo setDeduct() atribui valor a tag "&lt;Deducao&gt;" no XML Moip.

1. &lt;Deducao&gt;:  Respons�vel por definir o valor de desconto que ser� subtra�do do total a ser pago.

setDeduct($value)

$value : Numeric

	$moip->setDeduct('15.00');
-------------------------------------

setReason()
---------------
O m�todo setReason() atribui valor a tag "&lt;Razao&gt;" no XML Moip.

1. &lt;Razao&gt;:  Respons�vel por definir o motivo do pagamento.
1. Este campo � sempre obrigat�rio em um instru��o de pagamento.

setReason($value)

$value : String

	$moip->setReason('Pagamento de teste do Moip-PHP');
-------------------------------------

setPayer()
---------------
O m�todo setPayer() atribui valores ao nodo "&lt;Pagador&gt;" no XML Moip.


1. &lt;Pagador&gt;:  Nodo de informa��es de quem est� realizando o pagamento.
1. name : &lt;Nome&gt; : Nome completo do pagador
2. email : &lt;Email&gt; : E-mail do pagador
3. payerId : &lt;IdPagador&gt; : Identificados unico do pagador
4. identity : &lt;Identidade&gt; : Identidade do pagador (CPF)
5. phone : &lt;TelefoneCelular&gt; : Telefone de contato secund�rio do pagador
6. billingAddress : &lt;EnderecoCobranca&gt; : Endere�o do pagador
1. address : &lt;Logradouro&gt; : Logradouro do pagador, rua, av, estrada, etc.
2. number : &lt;Numero&gt; : Numero residencial do pagador
3. complement : &lt;Complemento&gt; : Complemento do endere�o do pagador
4. city : &lt;Cidade&gt; : Cidade do endere�o do pagador
5. neighborhood : &lt;Bairro&gt; : Bairro do endere�o do pagador
6. state : &lt;Estado&gt; : Estado do endere�o do pagador em formato ISO-CODE (UF)
7. country : &lt;Pais&gt; : Pais do pagador em formato ISO-CODE
8. zipCode  : &lt;CEP&gt; : CEP de endere�o
9. phone  : &lt;TelefoneFixo&gt; : Telefone de contato do pagador

setPayer($value)

$value : Array ('name','email','payerId','identity', 'phone','billingAddress' => Array('address','number','complement','city','neighborhood','state','country','zipCode','phone'))

	$moip->setPayer(array('name' => 'Nome Sobrenome',
        	'email' => 'email@cliente.com.br',
        	'payerId' => 'id_usuario',
	        'billingAddress' => array('address' => 'Rua do Z�zinho Cora��o',
            		'number' => '45',
            		'complement' => 'z',
            		'city' => 'S�o Paulo',
            		'neighborhood' => 'Palha�o J�o',
            		'state' => 'SP',
            		'country' => 'BRA',
            		'zipCode' => '01230-000',
            		'phone' => '(11)8888-8888')));
-------------------------------------

addPaymentWay()
---------------
O m�todo addPaymentWay() atribui valor a tag "&lt;FormaPagamento&gt;" do nodo "&lt;FormasPagamento&gt;" no XML Moip.

&lt;FormaPagamento&gt;: Define quais as formas de pagamento que ser�o exibidas ao pagador no Checkout Moip.
1. billet : Para disponibilizar a op��o "Boleto Banc�rio" como forma de pagamento no checkout Moip.
2. financing :  Para disponibilizar a op��o "Financiamento" como forma de pagamento no checkout Moip.
3. debit :  Para disponibilizar a op��o "Debito em conta" como forma de pagamento no checkout Moip.
4. creditCard :  Para disponibilizar a op��o "Cart�o de Cr�dito" como forma de pagamento no checkout Moip.
5. debitCard :  Para disponibilizar a op��o "Cart�o de d�bito" como forma de pagamento no checkout Moip.

addPaymentWay($way)

$way : String ('billet','financing','debit','creditCard','debitCard')

	$moip->addPaymentWay('creditCard');
	$moip->addPaymentWay('billet');
	$moip->addPaymentWay('financing');
	$moip->addPaymentWay('debit');
	$moip->addPaymentWay('debitCard');
-------------------------------------

setBilletConf()
---------------
O m�todo setBilletConf() atribui valores ao node "&lt;Boleto&gt;" no XML Moip que � respons�vel por definir as configura��es adicionais e personaliza��o do Boleto banc�rio.

1. $expiration :  Data em formato "AAAA-MM-DD" ou quantidade de dias.
2. $workingDays : Caso "$expiration" seja quantidade de dias voc� pode definir com "true" para que seja contado em dias úteis, o padr�o ser� dias corridos.
3. $instructions : Mensagem adicionais a ser impresso no boleto, at� tr�s mensagens.
4. $uriLogo : URL de sua logomarca, dimensões m�ximas 75px largura por 40px altura.

setBilletConf($expiration, $workingDays, $instructions, $uriLogo)

$expiration : Int ou Date

$workingDays : Boolean

$instructions : Array()

$uriLogo : String

	$moip->setBilletConf("2011-04-06",
            	false,
            	array("Primeira linha",
                	"Segunda linha",
                	"Terceira linha"),
            	"http://seusite.com.br/logo.gif");
-------------------------------------

addMessage()
---------------
O m�todo addMessage() atribui valor a tag "&lt;Mensagem&gt;" do node "&lt;Mensagens&gt;" no XML Moip.

1. &lt;Mensagens&gt;:  Node com "&lt;Mensagens&gt;".
1. &lt;Mensagem&gt;: TAG que define mensagem adicional a ser exibida no checkout Moip.

addMessage($msg)

$msg : String

	$moip->addMessage('Seu pedido contem os produtos X,Y e Z.');
-------------------------------------

setReturnURL()
---------------
O m�todo setReturnURL() atribui valor a tag "&lt;URLRetorno&gt;" no XML Moip, respons�vel por definir a URL que o comprador ser� redirecionado ao finalizar um pagamento atrav�s do checkout Moip.

setReturnURL($url)

$url : String

	$moip->setReturnURL('https://meusite.com.br/cliente/pedido/bemvindodevolta');
-------------------------------------

setNotificationURL()
---------------
O m�todo setNotificationURL() atribui valor a tag "&lt;URLNotificacao&gt;" no XML Moip, respons�vel por definir a URL ao qual o Moip dever� notificar com o NASP (Notifica��o de Altera��o de Status de Pagamento) as mudan�a de status.

setNotificationURL($url)

$url : String

	$moip->setNotificationURL('https://meusite.com.br/nasp/');
-------------------------------------

addComission()
---------------
O m�todo addComission() atribui valores as tags "&lt;Comissoes&gt;" no XML Moip, respons�vel por atribuir recebedores secund�rios a transa��o.


1. $reason : Raz�o/Motivo ao qual o recebedor secund�rio receber� o valor definido.
2. $receiver: Login Moip do usuario que receber� o valor.
3. $value : Valor ao qual ser� destinado ao recebedor secund�rio.
4. $percentageValue: Caso "true" define que valor ser� calculado em rela��o ao percentual sobre o valor total da transa��o.
5. $ratePayer: Caso "true" define que esse recebedor secund�rio ir� pagar a Taxa Moip com o valor recebido.

addComission($reason, $receiver, $value, $percentageValue, $ratePayer)

$reason : String

$receiver : String

$value : Number

$percentageValue: Boolean

$ratePayer : Boolean

	$moip->addComission('Raz�o do Split',
			'recebedor_secundario',
			'5.00');
	$moip->addComission('Raz�o do Split',
			'recebedor_secundario_2',
			'12.00',
			true,
			true);
-------------------------------------

addParcel()
---------------
O m�todo addParcel() atribui valores as tags de "&lt;Parcelamentos&gt;" no XML Moip, respons�vel configuras as op��es de parcelamento que ser�o disponíveis ao pagador.


1. $min : Quantidade mínima de parcelas disponível ao pagador.
2. $max : Quantidade m�xima de parcelas disponíveis ao pagador.
3. $rate : Valor de juros a.m por parcela.
4. $transfer : Caso "true" define que o valor de juros padr�o do Moip ser� pago pelo pagador.


addParcel($min, $max, $rate, $transfer)

$min : Number

$max : Number

$rate : Number

$transfer : Boolean

	$moip->addParcel('2', '4');
	$moip->addParcel('5', '7', '1.00');
	$moip->addParcel('8', '12', null, true);
-------------------------------------

setReceiver()
---------------
O m�todo setReceiver() atribui valor a tag "&lt;LoginMoIP&gt;" do node "&lt;Recebedor&gt;" que identifica o usu�rio Moip que ir� receber o pagamento no Moip.


1. $receiver : Login Moip do recebedor primario.


setReceiver($receiver)

$receiver : String

	$moip->setReceiver('integracao@labs.moip.com.br');
-------------------------------------

getXML()
---------------
O m�todo getXML() ir� retornar o XML gerado com todos os atributos que voc� configurou, esse m�todo pode ajudar a saber exatamente o XML que voc� ir� enviar ao Moip.


getXML()

	$moip = new Moip();
	$moip->setEnvironment('test');
	$moip->setCredential(array(
	    'key' => 'ABABABABABABABABABABABABABABABABABABABAB',
	    'token' => '01010101010101010101010101010101'
	    ));
	$moip->setUniqueID(false);
	$moip->setValue('100.00');
	$moip->setReason('Teste do Moip-PHP');
	$moip->validate('Basic');

	print_r($moip->getXML());

        //IR�? IMPRIMIR
        <?xml version="1.0" encoding="utf-8"?>
        <EnviarInstrucao>
            <InstrucaoUnica>
                <IdProprio></IdProprio>
                <Razao>Teste do Moip-PHP</Razao>
                <Valores>
                    <Valor moeda="BRL">100.00</Valor>
                </Valores>
            </InstrucaoUnica>
        </EnviarInstrucao>
-------------------------------------

send()
---------------
O m�todo send() executa o envio da instru��o ao Moip, e retorna os dados de resposta obtidos do Moip.


1. response : "true" para o caso de sucesso e "false" para quando ocorre algum erro.
2. error : Retorna sempre uma mensagem quando "response" � "false".
3. xml:  Retorna sempre o XML de resposta Moip quando "response" � "true".

send()

	$moip = new Moip();
	$moip->setEnvironment('test');
	$moip->setCredential(array(
	    'key' => 'ABABABABABABABABABABABABABABABABABABABAB',
	    'token' => '01010101010101010101010101010101'
	    ));
	$moip->setUniqueID(false);
	$moip->setValue('100.00');
	$moip->setReason('Teste do Moip-PHP');
	$moip->validate('Basic');

	print_r($moip->send());

        //IR�? IMPRIMIR
        stdClass Object
        (
            [response] => 1
            [error] =>
            [xml] => <ns1:EnviarInstrucaoUnicaResponse xmlns:ns1="http://www.moip.com.br/ws/alpha/"><Resposta><ID>201209042007216380000000989104</ID><Status>Sucesso</Status><Token>M2C031R2Q0Z9W0Y4Q2S0H0W7E2G1Z6P3E8C0C0W050T01070Y9Y8V9G1F0F4</Token></Resposta></ns1:EnviarInstrucaoUnicaResponse>
        )
-------------------------------------

getAnswer()
---------------
O m�todo getAnswer() retorna os dados de resposta do Moip em forma de objeto.

1. response : "true" para o caso onde o "&lt;Status&gt;" Moip retornou "Sucesso" e "false" para quando retornou "Falha".
2. error : Retorna sempre uma mensagem quando "response" � "false".
3. token:  Retorna o TOKEN de pagamento gerado para quando "response" � "true".
4. payment_url : Retorna a URL de checkout Moip preparada para redirecionar o cliente com o TOKEN de pagamento para quando "response" � "true".

getAnswer()

	$moip = new Moip();
	$moip->setEnvironment('test');
	$moip->setCredential(array(
	    'key' => 'ABABABABABABABABABABABABABABABABABABABAB',
	    'token' => '01010101010101010101010101010101'
	    ));

	$moip->setUniqueID(false);
	$moip->setValue('100.00');
	$moip->setReason('Teste do Moip-PHP');
	$moip->validate('Basic');
	$moip->send();

	print_r($moip->getAnswer());

	//IR�? IMPRIMIR
	stdClass Object
	(
	    [response] => 1
	    [error] =>
	    [token] => 92D091R2I0Y9X0E4T2K034L2H2V4H2J6L9R0S0T0K0N0L0T0Y9H879H144O8
	    [payment_url] => https://desenvolvedor.moip.com.br/sandbox/Instrucao.do?token=92D091R2I0Y9X0E4T2K034L2H2V4H2J6L9R0S0T0K0N0L0T0Y9H879H144O8
	)
-------------------------------------

queryParcel()
---------------
O m�todo queryParcel() retorna um Array() contendo as informa��es de parcelas e seus respectivos valores cobrados por parcela e o valor total a ser pago referente a taxa de juros simulada..

1. REQUEST
2. $login: Login Moip do usuario.
3. $maxParcel: M�ximo de parcelar a ser consultado.
4. $rate:  Taxa de juros para simula��o.
5. $simulatedValue: Valor pago ao qual ser� simulado.

6. RESPONSE
7. response : "true" em caso de resposta Moip com "&lt;Status&gt;" "Sucesso" e "false" em caso de "Falha"
8. installment: Numero de parcela correspondente aos valores.
9. total : Total a ser pago.
10. rate: Taxa de juros atribuido.
11. value: Valor por parcela.

queryParcel($login, $maxParcel, $rate, $simulatedValue)

$login : String

$maxParcel : Number

$rate : Number

$simulatedValue: Number

        $moip = new Moip();
        $moip->setEnvironment('test');
        $moip->setCredential(array(
            'key' => 'ABABABABABABABABABABABABABABABABABABABAB',
            'token' => '01010101010101010101010101010101'
            ));


        print_r($moip->queryParcel('integracao@labs.moip.com.br', '4', '1.99', '100.00'));


        //IR�? IMPRIMIR
        Array
        (
            [response] => 1
            [installment] => Array
                (
                    [1] => Array
                        (
                            [total] => 100.00
                            [rate] => 1.99
                            [value] => 100.00
                        )


                    [2] => Array
                        (
                            [total] => 103.00
                            [rate] => 1.99
                            [value] => 51.50
                        )


                    [3] => Array
                        (
                            [total] => 104.01
                            [rate] => 1.99
                            [value] => 34.67
                        )


                    [4] => Array
                        (
                            [total] => 105.04
                            [rate] => 1.99
                            [value] => 26.26
                        )


                )


        )
---------------