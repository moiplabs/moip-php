<?php

/**
 * MoIP's API connection class
 *
 * @author Herberth Amaral
 * @author Paulo Cesar
 * @version 0.0.2
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
	 * @throws Exception
	 * @return MoipResponse
     */
    public function send($credentials, $xml, $url='https://desenvolvedor.moip.com.br/sandbox/ws/alpha/EnviarInstrucao/Unica', $method='POST') {
        $header[] = "Authorization: Basic " . base64_encode($credentials);
        if (!function_exists('curl_init')){
            throw new Exception('This library needs cURL extension');
		}
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_USERPWD, $credentials);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0");

        if ($method == 'POST') curl_setopt($curl, CURLOPT_POST, true);

		if ($xml != '') curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        return new MoipResponse(array('resposta' => $ret, 'erro' => $err));
    }

    /**
	 * @param string $credentials token / key authentication Moip
	 * @param string $xml url request
	 * @param string $url url request
	 * @param string $error errors
	 * @return MoipResponse
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
                return new MoipResponse(array('response' => true, 'error' => null, 'xml' => $ret));
            else if ($info['http_code'] == "500")
                return new MoipResponse(array('response' => false, 'error' => 'Error processing XML', 'xml' => null));
            else if ($info['http_code'] == "401")
                return new MoipResponse(array('response' => false, 'error' => 'Authentication failed', 'xml' => null));
            else
                return new MoipResponse(array('response' => false, 'error' => $err, 'xml' => null));
        } else {
            return new MoipResponse(array('response' => false, 'error' => $error, 'xml' => null));
        }
    }


    /**
     * @param string $credentials token / key authentication Moip
     * @param string $url url request
     * @param string $error errors
     * @return MoipResponse
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
                return new MoipResponse(array('response' => true, 'error' => null, 'xml' => $ret));
            else if ($info['http_code'] == "500")
                return new MoipResponse(array('response' => false, 'error' => 'Error processing XML', 'xml' => null));
            else if ($info['http_code'] == "401")
                return new MoipResponse(array('response' => false, 'error' => 'Authentication failed', 'xml' => null));
            else
                return new MoipResponse(array('response' => false, 'error' => $err, 'xml' => null));
        } else {
            return new MoipResponse(array('response' => false, 'error' => $error, 'xml' => null));
        }
    }

}

/**
 * Read-only response
 * @property boolean|string $response
 * @property string $error
 * @property string $xml
 * @property string $payment_url
 * @property string $token
 */
class MoipResponse {
	private $response;

	function __construct(array $response)
	{
		$this->response = $response;
	}

	function __get($name)
	{
		if (isset($this->response[$name]))
		{
			return $this->response[$name];
		}
		return null;
	}
}