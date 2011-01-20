MOIP-PHP - Biblioteca PHP para acesso à API do MoIP 
====================================================

Você já deve ter visto todos os nossos plugins prontos e provavelmente deve ter pensado consigo mesmo: "uma biblioteca pronta para PHP iria facilitar muito a minha vida de desenvolvedor, pra eu não precisar mais de ficar validando regras de negócio na mão ou trabalhando diretamente com o cURL."

Pois seus problemas acabaram-se :-)

A MoIP-PHP é uma biblioteca que implementa uma camada de abstração prientada à objetos para geração do XML de instruções da MoIP, permitindo que você gere instruções sem poluir seu código com várias linhas de XML. Apesar de ainda não contemplar todas as funcionalidades do MoIP, já serve para casos mais simples. Um exemplo rápido:

      require 'MoIP.php';
      $moip = new MoIP();
      $moip->setCredenciais(array('key'=>'sua_key','token'=>'seu_token'));
      $moip->setIDProprio(123456);
      $moip->setValor('123456')
      $moip->setRazao('Teste do MoIP-PHP');
      $moip->valida();
      $moip->envia();
      echo $moip->getResposta()->token;

O MoIP-PHP utiliza o padrão [Fluent Interfaces](http://martinfowler.com/bliki/FluentInterface.html), portanto, você pode fazer o exemplo acima da seguinte forma:

      require 'MoIP.php';
      $moip = new MoIP();
      echo $moip->setCredenciais(array('key'=>'sua_key','token'=>'seu_token'))
                ->setIDProprio(123456)
                ->setValor('123456')
                ->setRazao('Teste do MoIP-PHP')
                ->valida()
                ->envia()
                ->getResposta()
                ->token;


O método getResposta() retorna um objeto contendo com os atributos "token" e "sucesso" (um tipo booleano).

O MoIP-PHP possui testes unitários utilizando o framework [PHPUnit](http://phpunit.de). Se você quiser se certificar que o MoIP-PHP funciona no seu ambiente, é só chamar o phpunit com o arquivo de testes:


> $ phpunit MoIPTests.php

Métodos disponíveis
--------------------

> setCredenciais ($credenciais)

Informa as credenciais (token,key) ao objeto MoIP. Necessárias à autenticação. Você *precisa* informar as suas credenciais antes de enviar a instrução, pois não é possível autenticar no sistema da MoIP sem estas informações.

O parâmetro $credenciais é um array associativo contendo as chaves _key_ e _token_ (ex: array('key'=>'sua_key','token'=>'seu_token')). Se você ainda não possui estes dados, entre em contato com a equipe do MoiP e solicite-os.

> setAmbiente($ambiente)

Configura o ambiente a ser utilizado. Suporta apenas dois valores: 'producao' e 'sandbox'

> setIDProprio($id_proprio)

Informa seu ID para a transação.

> setRazao($razao) 

Informa a razão do pagamento. Campo obrigatório.

> addFormaPagamento($forma,$args=null)

Adiciona um tipo de forma de pagamento. $forma pode ser:

 - 'boleto' 
 - 'financiamento'
 - 'debito'
 - 'cartao_credito'
 - 'cartao_debito' 
 - 'carteira_moip' 

O parametro opcional $args serve para informar dados adicionais do pagamento em boleto bancário, como:

    array('dias_expiracao'=>array('dias'=>5,'tipo'=>'corridos'));

> setValor($valor) [obrigatório]

Especifica o valor da transação no formato do MoIP (sem vírgulas, sendo que os dois ultimos digitos representam os centavos)

> setPagamentoDireto($params)

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

    
> setPagador($pagador)

Informa os dados do pagador em que ''$pagador''. Um exemplo de $pagador:

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
> addMensagem($msg)

Adiciona uma mensagem na instrução para serem mostradas ao pagador. Você pode adicionar quantas mensagens quiser.

> setUrlRetorno($url)

Informa a URL de retorno, que redireciona o cliente à página de seu site, por exemplo, após o pagamento. É necessário que a ferramenta URL de Retorno esteja habilitada em sua conta MoIP. Para habilitá-la, acesse sua conta MoIP em Meus Dados > Preferências > URL de Retorno

> setUrlNotificacao($url)

Informa a URL de notificação, que envia as informações sobre as alterações de status do pagamento. Estas informações são enviadas ao seu sistema para controle dos recebimentos. É necessário que a ferramenta NASP esteja habilitada em sua conta MoIP. Para habilitá-la, acesse sua conta MoIP em Meus Dados > Preferências > Notificação das Transações. Neste menu, marque a opção “*Receber notificação instantânea de transação” e confirme as alterações.

> setAcrescimo($valor)

Adiciona um valor no pagamento. Pode ser usado para cobrança de multas, fretes e outros.

> setDeducao($valor)

Deduz um valor do pagamento. É usado principalmente para descontos.

> addEntrega($params)

Adiciona um parâmetro de entrega, permitindo especificar o cálculo do frete (sendo que o frete pode ser próprio ou dos correios).

Um exemplo mínimo:

    $moip = new MoIP();
    
    //adiciona um parâmetro de entrega de frete próprio, custando R$2,30 que será entregue em 3 dias corridos
    $moip->addEntrega(array('tipo'=>'proprio',
                            'valor_fixo'=>'2.30',
                            'prazo'=>array('tipo'=>'corridos','dias'=>'3'));
   
    //adiciona um parâmetro de entrega de frete via correios, com 10KG, via uma encomenda normal,
    //podendo ser entregue em até 3 dias uteis. 
    $moip->addEntrega(array('tipo'=>'correios',
                            'prazo'=>array('tipo'=>'uteis','dias'=>'3'),
                            'correios'=>array('peso'=>'10.00','forma_entrega'=>'EncomendaNormal')));
    
    // adiciona um parâmetro de entrega de frete via correios, com 10KG, via Sedex 10.
    // podendo ser entregue em até 1 dia corrido.
    $moIP->addEntrega(array('tipo'=>'correios',                              
                            'prazo'=>array('tipo'=>'corridos','dias'=>'1'),
                            'correios'=>array('peso'=>'10.00','forma_entrega'=>'Sedex10')));

Em qualquer parâmetro é obrigatório que o tipo de frete seja especificado e seu respectivo prazo de entrega informando se os dias passados são uteis ou corridos.

Se o tipo de frete for os correios, é necessário especificar os parâmetros de entrega pelos correios (peso e forma de entrega).

> addParcela($min,$max,$juros='')

Permite adicionar uma forma de parcelamento, em que $min se refere ao mínimo de parcelas da forma e $max se refere ao numero maximo de parcelas. $juros é um parâmetro opcional que informa os juros mensais (em %).

> addComissao($params)

Mais uma instrução adicional. Permite especificar comissões, em valores fixos ou percentuais, sobre o pagamento. Exemplos de uso:

Adicionando um comissionado com um valor fixo:

    addComissao(array('login_moip'=>'login_do_comissionado','valor_fixo'=>15)); 

Adicionando um comissionado com um valor percentual:

    addComissao(array('login_moip'=>'login_do_comissionado','valor_percentual'=>2.1));
> getXML()

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

Licença
-------

MoIP-PHP Copyright (C) 2010 Herberth Amaral

This library is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
