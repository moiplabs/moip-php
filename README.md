MOIP-PHP - Biblioteca PHP para acesso à API do MoIP (v. 0.0.1)
====================================================

Você já deve ter visto todos os nossos plugins prontos e provavelmente deve ter pensado consigo mesmo: "uma biblioteca pronta para PHP iria facilitar muito a minha vida de desenvolvedor, pra eu não precisar mais de ficar validando regras de negócio na mão ou trabalhando diretamente com o cURL."

Pois seus problemas acabaram-se :-)

A MoIP-PHP é uma biblioteca que implementa uma camada de abstração prientada à objetos para geração do XML de instruções da MoIP, permitindo que você gere instruções sem poluir seu código com várias linhas de XML. Apesar de ainda não contemplar todas as funcionalidades do MoIP, já serve para casos mais simples. Um exemplo rápido:

      require 'MoIP.php';
      $moip = new MoIP();
      $moip->setCredenciais(array('key'=>'sua_key','token'=>'seu_token'));
      $moip->setIDProprio(123456);
      $moip->setRazao('Teste do MoIP-PHP');
      $moip->valida();
      $moip->envia();
      echo $moip->getResposta()->token;

O MoIP-PHP utiliza o padrão [Fluent Interfaces](http://martinfowler.com/bliki/FluentInterface.html), portanto, você pode fazer o exemplo acima da seguinte forma:

      require 'MoIP.php';
      $moip = new MoIP();
      echo $moip->setCredenciais(array('key'=>'sua_key','token'=>'seu_token'))
                ->setIDProprio(123456)
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

> setFormaPagamento($forma,$args=null)

Configura a forma de pagamento. Atualmente só suporta boleto bancário, portanto, o unico valor que o parametro $forma aceita é 'boleto'.

O parametro opcional $args serve para informar dados adicionais do pagamento, como 

MoIP Status
------------

O MoIP Status reune funcionalidades desejadas que ainda não foram incluídos na API oficial do MoIP. Atualmente, esta biblioteca só suporta verificação de saldo. Eis um exemplo:

      require 'MoIPStatus.php';

      $status = new MoIPStatus();
      $status->setCredenciais('seu_username_moip','sua_senha_moip')->getStatus();
      print $status->saldo; // R$ 120,34

Esta lib necessita do [phpQuery](http://code.google.com/p/phpquery/). Você pode instala-lo via PEAR:

      pear channel-discover phpquery-pear.appspot.com  
      pear install phpquery/phpQuery 

Se não houver possibilidade de instalação via PEAR, você ainda pode [baixar o pacote](http://code.google.com/p/phpquery/downloads/list) e substituir a chamada dele no início do arquivo **MoIPStatus.php**

Limitações
-----------
Esta é a primeira versão da biblioteca e nem todos os casos estão cobertos. Veja nossa lista de issues para ver o que falta e quais são os problemas encontrados.

Por enquanto, a biblioteca só suporta instruções únicas (remessa, por exemplo, não é suportado).

Se você quiser ver alguma funcionalidade na biblioteca, por favor, crie uma issue. Se você realmente quiser ajudar, faça um fork e nos mande um pull request. Ficaremos contentes de ver sua contribuição :-)


Licença
-------

MoIP-PHP Copyright (C) 2010 Herberth Amaral

This library is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
