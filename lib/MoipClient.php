<?php

/**
 * MoIP's API connection class
 *
 * @author Herberth Amaral
 * @version 0.0.1
 * @license <a href="http://www.opensource.org/licenses/bsd-license.php">BSD License</a>
 */
class MoIPClient {

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
    public function send($credentials, $xml, $url='https://desenvolvedor.moip.com.br/sandbox/ws/alpha/EnviarInstrucao/Unica', $method='POST') {
        $header[] = "Authorization: Basic " . base64_encode($credentials);
        if (!function_exists('curl_init'))
            return $this->send_without_curl($credentials, $xml, $url);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_USERPWD, $credentials);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0");

        $method == 'POST' ? curl_setopt($curl, CURLOPT_POST, true) : null;

        $xml != '' ? curl_setopt($curl, CURLOPT_POSTFIELDS, $xml) : null;
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return (object) array('resposta' => $ret, 'erro' => $err);
    }

    /**
     *
     * @param <URI> $uri
     * @param <string> $auth
     * @param <string> $data
     * @return ResposeType
     * @return Response
     */
    function curlPost($credentials, $xml, $url, $error=null) {

        if (!$error) {
            $header[] = "Expect:";
            $header[] = "Authorization: Basic " . base64_encode($credentials);

            $ch = curl_init();
            $options = array(CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $xml,
                CURLOPT_RETURNTRANSFER => true,
                CURLINFO_HEADER_OUT => true
            );

            curl_setopt_array($ch, $options);
            $ret = curl_exec($ch);
            $err = curl_error($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);


            if ($info['http_code'] == "200")
                return (object) array('response' => true, 'error' => null, 'xml' => $ret);
            else if ($info['http_code'] == "500")
                return (object) array('response' => false, 'error' => 'Error processing XML', 'xml' => null);
            else if ($info['http_code'] == "401")
                return (object) array('response' => false, 'error' => 'Authentication failed', 'xml' => null);
            else
                return (object) array('response' => false, 'error' => $err, 'xml' => null);
        }else {
            return (object) array('response' => false, 'error' => $error, 'xml' => null);
        }
    }


    /**
     *
     * @param <string> $credentials token / key authentication Moip
     * @param <XML> $xml xml instruction
     * @param <string> $url url request
     * @param <string> $error errors
     * @return ResposeType
     * @return Response
     */
    function curlGet($credentials, $url, $error=null) {

        if (!$error) {
            $header[] = "Expect:";
            $header[] = "Authorization: Basic " . base64_encode($credentials);

            $ch = curl_init();
            $options = array(CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true
                );

            curl_setopt_array($ch, $options);
            $ret = curl_exec($ch);
            $err = curl_error($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);


            if ($info['http_code'] == "200")
                return (object) array('response' => true, 'error' => null, 'xml' => $ret);
            else if ($info['http_code'] == "500")
                return (object) array('response' => false, 'error' => 'Error processing XML', 'xml' => null);
            else if ($info['http_code'] == "401")
                return (object) array('response' => false, 'error' => 'Authentication failed', 'xml' => null);
            else
                return (object) array('response' => false, 'error' => $err, 'xml' => null);
        }else {
            return (object) array('response' => false, 'error' => $error, 'xml' => null);
        }
    }

}
?>
