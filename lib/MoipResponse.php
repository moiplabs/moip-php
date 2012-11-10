<?php

/**
 * MoIP Normalized Response Class
 *
 * @author     Paulo Cesar
 * @version    0.0.2
 * @license    <a href="http://www.opensource.org/licenses/bsd-license.php">BSD License</a>
 * @package    Moip
 * @subpackage MoipResponse
 */

/**
 * Read-only response
 * @property boolean|string $response
 * @property string         $error
 * @property string         $xml
 * @property string         $payment_url
 * @property string         $token
 */
class MoipResponse implements Serializable, Countable {

	private $response = array();
	private $strtolower_keys = array();

	function flatten()
	{
		$flat = array();
		$collector = function ($v, $k) use (&$flat)
		{
			$flat[$k] = $v;
		};

		array_walk_recursive($this->response, $collector);

		$this->response = $flat;
	}

	function __construct(array $response, $flatten = true)
	{
		$this->response = $response;

		if ($flatten)
		{
			$this->flatten();
		}

		$this->strtolower_keys = array_change_key_case(array_combine(array_keys($this->response), array_keys($this->response)));
	}

	function __get($name)
	{
		$name = strtolower($name);

		if (isset($this->strtolower_keys[$name]))
		{
			return $this->response[$this->strtolower_keys[$name]];
		}

		return null;
	}

	function __isset($name)
	{
		return isset($this->response[strtolower($name)]);
	}

	public function serialize($json = false)
	{
		if ($json)
		{
			return json_encode($this->response, true);
		}
		else
		{
			return serialize($this->response);
		}
	}

	public function unserialize($serialized)
	{
		if ($response = json_decode($serialized))
		{
			if (function_exists('json_last_error') && json_last_error() === JSON_ERROR_NONE)
			{
				$this->response = $response;
			}
		}
		else
		{
			$this->response = unserialize($serialized);
		}
	}

	public function count()
	{
		return count($this->response);
	}
}