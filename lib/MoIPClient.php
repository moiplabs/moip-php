<?php
/**
 * MoIP's API connection class
 *
 * @author Herberth Amaral
 * @version 0.0.1
 * @license <a href="http://www.opensource.org/licenses/bsd-license.php">BSD License</a>
 */
class MoIPClient
{

    /**
     * Method send()
     * 
     * Send the request to API's server
     * 
     * @param string $credentials Token and key to the authentication
     * @param string $xml The XML request
     * @param string $url The server's URL
     * @param string $method Method used to send the request
     */
    public function send($credentials,$xml,$url='https://desenvolvedor.moip.com.br/sandbox/ws/alpha/EnviarInstrucao/Unica',$method='POST')
    {
        $header[] = "Authorization: Basic " . base64_encode($credentials);
        if (!function_exists('curl_init'))
            return $this->send_without_curl($credentials, $xml, $url);

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
        return (object) array('resposta'=>$ret,'erro'=>$err);
    }

}

?>
