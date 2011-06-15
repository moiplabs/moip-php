MOIP-PHP - Biblioteca PHP para acesso à API do MoIP 
====================================================

Você já deve ter visto todos os nossos plugins prontos e provavelmente deve ter pensado consigo mesmo: "uma biblioteca pronta para PHP iria facilitar muito a minha vida de desenvolvedor, pra eu não precisar mais de ficar validando regras de negócio na mão ou trabalhando diretamente com o cURL."

Pois seus problemas acabaram-se :-)

A MoIP-PHP é uma biblioteca que implementa uma camada de abstração orientada à objetos para geração do XML de instruções da MoIP, permitindo que você gere instruções sem poluir seu código com várias linhas de XML. Um exemplo rápido:

      require 'MoIP.php';
      $moip = new MoIP();
      $moip->setCredencial(array('key'=>'sua_key','token'=>'seu_token'));
      $moip->setUniqueID(123456);
      $moip->setValue('123456');
      $moip->setReason('Teste do MoIP-PHP');
      $moip->validate();
      $moip->send();
      echo $moip->getAnswer()->token;

O MoIP-PHP utiliza o padrão [Fluent Interfaces](http://martinfowler.com/bliki/FluentInterface.html), portanto, você pode fazer o exemplo acima da seguinte forma:

      require 'MoIP.php';
      $moip = new MoIP();
      echo $moip->setCredential(array('key'=>'sua_key','token'=>'seu_token'))
                ->setUniqueID(123456)
                ->setValue('123456')
                ->setReason('Teste do MoIP-PHP')
                ->validate()
                ->send()
                ->getAnswer()
                ->token;


O método getAnswer() retorna um objeto contendo com os atributos "token" , "success" (um tipo booleano) e "url_pagamento", que contém a URL que você deverá redirecionar seu cliente de acordo com o ambiente (sandbox ou produção) que você está utilizando.

O MoIP-PHP possui testes unitários utilizando o framework [PHPUnit](http://phpunit.de). Se você quiser se certificar que o MoIP-PHP funciona no seu ambiente, é só chamar o phpunit com o arquivo de testes:


> $ phpunit test/MoIPTests.php

Métodos disponíveis
===================

setCredentials ($credencials)
------------------------------

Informa as credenciais (token,key) ao objeto MoIP. Necessárias à autenticação. Você *precisa* informar as suas credenciais antes de enviar a instrução, pois não é possível autenticar no sistema da MoIP sem estas informações.

O parâmetro $credencials é um array associativo contendo as chaves _key_ e _token_ (ex: array('key'=>'sua_key','token'=>'seu_token')). Se você ainda não possui estes dados, entre em contato com a equipe do MoiP e solicite-os.

setEnvironment($environment)
------------------------------

Configura o ambiente a ser utilizado. Suporta apenas dois valores: 'producao' e 'sandbox'

setUniqueID($uniqueID)
------------------------------

Informa seu ID para a transação.

setReason($reason)
------------------------------

Informa a razão do pagamento. Campo obrigatório.

addPaymentWay($way,$args=null)
------------------------------

Adiciona um tipo de forma de pagamento. $forma pode ser:

 - 'boleto' 
 - 'financiamento'
 - 'debito'
 - 'cartao_credito'
 - 'cartao_debito' 
 - 'carteira_moip' 

O parametro opcional $args serve para informar dados adicionais do pagamento em boleto bancário, como:

    array('dias_expiracao'=>array('dias'=>5,'tipo'=>'corridos'));

setValue($value) [obrigatório]
------------------------------

Especifica o valor da transação no formato do MoIP (sem vírgulas, sendo que os dois ultimos digitos representam os centavos)

setPagamentoDireto($params)
------------------------------

Especifica que a transação irá ser feita utilizando o Pagamento Direto do MoIP. É necessário que a conta do MoIP em questão já esteja com o Pagamento Direto habilitado. Em caso de dúvidas sobre o pagamento direto, utilize nosso [ fórum ][http://labs.moip.com.br/forum/]

Um exemplo de uso:

    $moip = new MoIP();
    //... seta token/key, informa razão de pagamento e ID próprio
    $moip->setPagamentoDireto(array('forma'=>'boleto'); //pagamento direto via boleto
    $moip->setPagamentoDireto(array('forma'=>'debito','instituicao'=>'banco_brasil'); //debito bancario pelo Banco do Brasil

    //pagamento direto via cartão de crédito
    //todos os dados são necessários
    $moip->setPagamentoDireto(array('forma'=>'cartao_credito',
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

verifyPagamentoDireto($login_moip)
------------------------------

Faz a verificação dos tipos de pagamento disponíveis para o cliente MoIP em $login_moip, utilizando o PagamentoDireto. **Atenção**: você precisa especificar as credenciais de acesso utilizando o método setCredentials antes de chamar o checarPagamentoDireto.

Esse método retorna um objeto contendo os métodos/meios de pagamento do MoIP e se o usuário em questão pode utiliza-lo. Eis um exemplo adaptado da saída do ḿetodo anterior para um usuário que não tem o PagamentoDireto:

    stdClass Object
    (
        [erro] => false
        [id] => 201101260848421410000005380579
        [sucesso] => true 
        [carteira_moip] => false
        [cartao_credito] => false
        [cartao_debito] => false
        [debito_bancario] => false 
        [financiamento_bancario] => false 
        [boleto_bancario] => false
        [debito_automatico] => false
    )

No caso anterior, o usuário consultado não tem acesso a nenhum método de pagamento via PagamentoDireto. Para maiores detalhes sobre esse método, veja [nosso post sobre o assunto](http://labs.moip.com.br/2011/01/24/novas-funcionalidades-da-api-do-moip-checarpagamentodireto-e-checarvaloresparcelamento/)


verifyParcelValues($login_moip,$total_parcels,$rate,$simuleted_value)
------------------------------

Obtém os valores das parcelas de acordo com o usuário MoIP (determinado por $login_moip), o número de parcelas ($total_parcelas, inteiro), os juros ($juros, float) e o valor total da transação a ser simulado ($valor_simulado). **Atenção** este método requer que você especifique suas credenciais **de produção** da API do MoIP para funcionar.

Exemplo:

    $moip = new MoIP();
    $moip->setCredentials(array('token'=>'meu_token_de_producao','key'=>'minha_key_de_producao');
    $parcelamento = $moip->verifyParcelValues('login_moip',12,1.99,100);
    print_r($parcelamento);

    // a instrucao acima irá imprimir algo parecido com isso:
    Array
    (
        [sucesso] => 1
        [id] => 201101261211303120000005383093
        [parcelas] => Array
            (
                [1] => Array
                    (
                        [total] => 100
                        [juros] => 1.99
                        [valor] => 100
                    )

                [2] => Array
                    (
                        [total] => 103.00
                        [juros] => 1.99
                        [valor] => 51.50
                    )

                [3] => Array
                    (
                        [total] => 104.01
                        [juros] => 1.99
                        [valor] => 34.67
                    )

                [4] => Array
                    (
                        [total] => 105.04
                        [juros] => 1.99
                        [valor] => 26.26
                    )

                [5] => Array
                    (
                        [total] => 106.05
                        [juros] => 1.99
                        [valor] => 21.21
                    )

                [6] => Array
                    (
                        [total] => 107.10
                        [juros] => 1.99
                        [valor] => 17.85
                    )

                [7] => Array
                    (
                        [total] => 108.15
                        [juros] => 1.99
                        [valor] => 15.45
                    )

                [8] => Array
                    (
                        [total] => 109.20
                        [juros] => 1.99
                        [valor] => 13.65
                    )

                [9] => Array
                    (
                        [total] => 110.25
                        [juros] => 1.99
                        [valor] => 12.25
                    )

                [10] => Array
                    (
                        [total] => 111.30
                        [juros] => 1.99
                        [valor] => 11.13
                    )

                [11] => Array
                    (
                        [total] => 112.31
                        [juros] => 1.99
                        [valor] => 10.21
                    )

                [12] => Array
                    (
                        [total] => 113.40
                        [juros] => 1.99
                        [valor] => 9.45
                    )

            )

    )



setPayer($payer)
------------------------------

Informa os dados do pagador em que ''$payer''. Um exemplo de $pagador:

    $pagador = array('nome'=>'Jose da Silva',
                     'login_moip'=>'jose_silva',
                     'email'=>'jose@silva.com',
                     'celular'=>'1199999999',
                     'apelido'=>'zeh',
                     'identidade'=>'12345678',
                     'endereco'=>array('logradouro'=>'Rua do Zé',
                                       'numero'=>'45',
                                       'complemento'=>'z',
                                       'cidade'=>'São Paulo',
                                       'estado'=>'São Paulo',
                                       'pais'=>'Brasil',
                                       'cep'=>'11111111',
                                       'telefone'=>'1188888888'));
addMessage($msg)
------------------------------

Adiciona uma mensagem na instrução para serem mostradas ao pagador. Você pode adicionar quantas mensagens quiser.

setReturnURL($url)
------------------------------

Informa a URL de retorno, que redireciona o cliente à página de seu site, por exemplo, após o pagamento. É necessário que a ferramenta URL de Retorno esteja habilitada em sua conta MoIP. Para habilitá-la, acesse sua conta MoIP em Meus Dados > Preferências > URL de Retorno

setNotificationURL($url)
------------------------------

Informa a URL de notificação, que envia as informações sobre as alterações de status do pagamento. Estas informações são enviadas ao seu sistema para controle dos recebimentos. É necessário que a ferramenta NASP esteja habilitada em sua conta MoIP. Para habilitá-la, acesse sua conta MoIP em Meus Dados > Preferências > Notificação das Transações. Neste menu, marque a opção “*Receber notificação instantânea de transação” e confirme as alterações.

setAdds($value)
------------------------------

Adiciona um valor no pagamento. Pode ser usado para cobrança de multas, fretes e outros.

setDeduct($value)
------------------------------

Deduz um valor do pagamento. É usado principalmente para descontos.

addDelivery($params)
------------------------------

Adiciona um parâmetro de entrega, permitindo especificar o cálculo do frete (sendo que o frete pode ser próprio ou dos correios).

Um exemplo mínimo:

    $moip = new MoIP();
    
    //adiciona um parâmetro de entrega de frete próprio, custando R$2,30 que será entregue em 3 dias corridos
    $moip->addDelivery(array('tipo'=>'proprio',
                            'valor_fixo'=>'2.30',
                            'prazo'=>array('tipo'=>'corridos','dias'=>'3'));
   
    //adiciona um parâmetro de entrega de frete via correios, com 10KG, via uma encomenda normal,
    //podendo ser entregue em até 3 dias uteis. 
    $moip->addDelivery(array('tipo'=>'correios',
                            'prazo'=>array('tipo'=>'uteis','dias'=>'3'),
                            'correios'=>array('peso'=>'10.00','forma_entrega'=>'EncomendaNormal')));
    
    // adiciona um parâmetro de entrega de frete via correios, com 10KG, via Sedex 10.
    // podendo ser entregue em até 1 dia corrido.
    $moIP->addDelivery(array('tipo'=>'correios',
                            'prazo'=>array('tipo'=>'corridos','dias'=>'1'),
                            'correios'=>array('peso'=>'10.00','forma_entrega'=>'Sedex10')));

Em qualquer parâmetro é obrigatório que o tipo de frete seja especificado e seu respectivo prazo de entrega informando se os dias passados são uteis ou corridos.

Se o tipo de frete for os correios, é necessário especificar os parâmetros de entrega pelos correios (peso e forma de entrega).

addParcel($min,$max,$rate='')
------------------------------

Permite adicionar uma forma de parcelamento, em que $min se refere ao mínimo de parcelas da forma e $max se refere ao numero maximo de parcelas. $juros é um parâmetro opcional que informa os juros mensais (em %).

addComission($params)
------------------------------

Mais uma instrução adicional. Permite especificar comissões, em valores fixos ou percentuais, sobre o pagamento. Exemplos de uso:

Adicionando um comissionado com um valor fixo:

    addComission(array('login_moip'=>'login_do_comissionado','valor_fixo'=>15));

Adicionando um comissionado com um valor percentual:

    addComission(array('login_moip'=>'login_do_comissionado','valor_percentual'=>2.1));

queryInstruction($token)
-------------------------

Faz uma consulta no MoIP a respeito de uma transação, determinada pelo seu token. É útil para recuperar dados de clientes que não informaram dados no seu portal, mas informaram no MoIP no momento do checkout.

Além disso, há algumas informações úteis, como a taxa cobrada pelo MoIP, a data que o pagamento foi feito (se tiver sido feito).

Exemplo de uso:

    $moip = new MoIP();
    $instruction = $moip->setCredential(array('token'=>'seu_token','key'=>'sua_key'))
                        ->queryInstruction('Q2D0S1F1Z0X3N054M2M131C8W3P4Y4W9U9L0V0A0I0W0Y01020J8X9H7Z827');
    print_r($instruction);

A resposta do método é um objeto SimpleXmlElement, que pode ter basicamente duas formas distintas. A resposta seguinte corresponde a uma transação enviada, mas sem que o processo de pagamento tenha sido iniciado:

    SimpleXMLElement Object
    (
        [RespostaConsultar] => SimpleXMLElement Object
            (
                [ID] => 201103042124090000000000089788
                [Status] => Sucesso
            )

    )

Quando o processo é iniciado, alguns outros dados aparecerão na resposta, assim como mostrado no exemplo de resposta a seguir:

    SimpleXMLElement Object
    (
        [RespostaConsultar] => SimpleXMLElement Object
            (
                [ID] => 201103042126169290000000089791
                [Status] => Sucesso
                [Autorizacao] => SimpleXMLElement Object
                    (
                        [Pagador] => SimpleXMLElement Object
                            (
                                [Nome] => Herberth Amaral
                                [Email] => herberthamaral@labs.moip.com.br
                            )

                        [EnderecoCobranca] => SimpleXMLElement Object
                            (
                                [Logradouro] => Av. XYZ
                                [Numero] => 1234
                                [Complemento] => SimpleXMLElement Object
                                    (
                                    )

                                [Bairro] => Centro
                                [CEP] => 11111-111
                                [Cidade] => Sao Paulo 
                                [Estado] => SP
                                [Pais] => BRA
                                [TelefoneFixo] => 1111111111
                            )

                        [Recebedor] => SimpleXMLElement Object
                            (
                                [Nome] => Casas Goiás 
                                [Email] => casas@goias.com.br
                            )

                        [Pagamento] => SimpleXMLElement Object
                            (
                                [Data] => 2011-03-04T21:25:56.000-03:00
                                [TotalPago] => 124.00
                                [TaxaParaPagador] => 1.00
                                [TaxaMoIP] => 3.96
                                [ValorLiquido] => 119.04
                                [FormaPagamento] => BoletoBancario
                                [InstituicaoPagamento] => Bradesco
                                [Status] => BoletoImpresso
                            )

                    )

            )

    )

getXML()
---------


Útil para debugging. Retorna o XML que irá ser gerado, com base nos parâmetros já informados.

MoIP Status
------------

O MoIP Status reune funcionalidades desejadas que ainda não foram incluídos na API oficial do MoIP. Eis um exemplo de consulta de saldo:

      require 'MoIPStatus.php';

      $status = new MoIPStatus();
      $status->setCredenciais('seu_username_moip','sua_senha_moip')->getStatus();
      print $status->saldo; // R$ 120,34
      print $status->saldo_a_receber; // R$12,45 -- null se não houver saldo a receber

Você também pode obter as ultimas transações:

      require 'MoIPStatus.php';

      $status = new MoIPStatus();
      $status->setCredenciais('seu_username_moip','sua_senha_moip')->getStatus();
      print_r($status->ultimas_transacoes);

Um exemplo de saída do exemplo anterior seria:

    Array
        (
            [0] => Array
                (
                    [data] => 10/10/2010
                    [nome] => Jose da Silve
                    [pagamento] => concluido
                    [adicional] => Saque para Conta corrente
                    [valor] => - R$123.45
                )

            [1] => Array
                (
                    [data] => 10/10/2010
                    [nome] => Maria Pereira
                    [pagamento] => cancelado
                    [adicional] => Caneca X
                    [valor] => + R$2.00
                )

            [2] => Array
                (
                    [data] => 09/10/2010
                    [nome] => Ricardo Oliveira
                    [pagamento] => boleto impresso
                    [adicional] => Camisa do Link
                    [valor] => + R$30.00
                )

O atributo **ultimas_transacoes** será **null** se não houver ao menos uma transação nos ultimos 30 dias. 

As dependências necessárias para esta funcionalidade já estão incluídas por padrão.


INSTRUÇÕES PARA USO DA CLASSE MoIPNASP()
=============================================

Tentando facilitar o tratamento das informações enviadas pelo MoIP através do NASP, criamos a classe MoIPNASP()

Métodos disponíveis
=======================

Manipulação através de arquivos
--------------------------------

Os métodos abaixo auxiliam na manipulação dos dados usando arquvivos. Se já quiser um exemplo pronto disto basta abrir o arquivo nasp.sample.php, configura-lo para o seu ambiente e usar para manipular seus arquivos.

setFile($path, $filename)
-------------------------
Método utilizado para definir o caminho($path) e o nome do arquivo($filename) para onde serão enviadas as informações do NASP.

setContent($data)
-----------------------
Método utilizado para definir as informações que serão gravadas no arquivo definido em setFile().

write()
----------------------
Método usado para escrever as informações definidas em setContent()

getContent()
--------------
Método usado para retornar as informações gravadas no arquivo definido por setFile()


Manipulação através de banco de dados MySQL
-------------------------------------------

Os métodos abaixo auxiliam na manipulação dos dados usando arquvivos. Para facilitar, você pode executar o arquivo nasp.dbconfig.php que, ao passar as informações do database, ele cria a tabela de dados já no formato suportado pela classe.


setDatabase($hostname, $database,$user,$pass)
-----------------------------------------------
Método que define o banco de dados que será utilizado para armazenar as informações do NASP

insertData($data)
------------------
Método utilizado para persistir as informações($data) no banco de dados definido em setDatabase().

getData()
------------------
Método utilizado para retornar as informações do banco de dados definido em setDatabase().


Licença
-------

Copyright (c) 2011, Herberth Amaral
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
Neither the name of the MoIP nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
