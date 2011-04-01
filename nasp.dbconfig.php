<?php
/**
 * Template to make tha NASP configuration more easy
 *
 * @author Alê Borba <ale.alvesborba@gmail.com>
 * @version 0.0.1
 * @license <a href="http://www.opensource.org/licenses/bsd-license.php">BSD License</a>
 */

//Get the flag sended via $_GET
$infos = isset ($_GET['config']) && $_GET['config'] == true ? $_GET['config'] : false;

if ($infos) {

    include_once 'autoload.inc.php';

    //Get the informations sended via $_POST
    $hostname   = isset ($_POST['dbhost']) ? $_POST['dbhost'] : null;
    $database   = isset ($_POST['dbname']) ? $_POST['dbname'] : null;
    $user       = isset ($_POST['dbuser']) ? $_POST['dbuser'] : null;
    $pass       = isset ($_POST['dbpass']) ? $_POST['dbpass'] : null;

    $nasp = new MoIPNASP();

    $nasp->setDatabase($hostname,$database,$user,$pass);

    $nasp->createTable();
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>NASP's Database Configuration</title>
        <style type="text/css">
            .conteudo{
                width: 75%;
                margin: auto;
                margin-top: 100px;
            }
            .logoMoip{
                text-align: center;
            }
            .titulo{
                text-align: center;
                margin-bottom: 30px;
                font-weight: bold;
            }
            .formConfig{
                border: solid black 1px;
                text-align: center;
                width: 350px;
                margin: auto;
                padding: 30px;
            }
            .dbinfo{
                height: 30px;
            }
            .dbinfo input{
                float: right;
            }
            .dbinfo label{
                float: left;
                height: 30px;
            }
        </style>
    </head>
    <body>
        <div class="conteudo">
            <div class="logoMoip">
                <img src="http://labs.moip.com.br/forum/images/misc/moip-labs-1.0.png">
            </div>
            <div class="titulo">
                NASP's Database Configuration
            </div>
            <div class="formConfig">
                <form method="POST" id="formConfig" action="nasp.dbconfig.php?config=true">
                    <div class="dbinfo">
                        <label for="dbhost">Hostname</label>
                        <input type="text" id="dbhost" name="dbhost" />
                    </div>
                    <div class="dbinfo">
                        <label for="dbname">Database</label>
                        <input type="text" id="dbname" name="dbname" />
                    </div>
                    <div class="dbinfo">
                        <label for="dbuser">User</label>
                        <input type="text" id="dbuser" name="dbuser" />
                    </div>
                    <div class="dbinfo">
                        <label for="dbpass">Password</label>
                        <input type="password" id="dbpass" name="dbpass" />
                    </div>
                    <input type="submit" value="Send"/>
                    <input type="reset" value="Cancel"/>
                </form>
            </div>
            <?php

            if ($infos) {
                echo "<div class='titulo'>
                          <h3>Database configured successfully!!</h3>
                      </div>";
            }

            ?>
            
        </div>
    </body>
</html>
