<?php
/**
 * MoIP's API connection class
 *
 * @author Herberth Amaral
 * @version 0.0.1
 * @licence <a href="http://opensource.org/licenses/gpl-3.0.html">GNU General Public License version 3 (GPLv3)</a>
 */
class MoIPClient
{
    function send($credentials,$xml,$url='https://desenvolvedor.moip.com.br/sandbox/ws/alpha/EnviarInstrucao/Unica',$method='POST')
    {
        $header[] = "Authorization: Basic " . base64_encode($credentials);
        if (!function_exists('curl_init'))
        {
            throw new Exception("É necessário  a função cURL habilitada no seu servidor para o correto funcionamento da classe");
        }


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_USERPWD, $credentials);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0");

        $method=='POST'?curl_setopt($curl, CURLOPT_POST, true):null;

        $xml!=''?curl_setopt($curl, CURLOPT_POSTFIELDS, $xml):null;
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return (object) array('answer'=>$ret,'erro'=>$err);
    }

}
?>
