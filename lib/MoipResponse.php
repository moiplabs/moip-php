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

	private function set_keys()
	{
		$this->strtolower_keys = array_change_key_case(array_combine(array_keys($this->response), array_keys($this->response)));
	}

	function __construct(array $response, $flatten = true)
	{
		$this->response = $response;

		if ($flatten)
		{
			$this->flatten();
		}

		$this->set_keys();
	}

	private function to_key($name)
	{
		$name = strtolower($name);
		if (isset($this->strtolower_keys[$name]))
		{
			return $this->strtolower_keys[$name];
		}
		return false;
	}

	function __get($name)
	{
		if ($name = $this->to_key($name))
		{
			return $this->response[$name];
		}

		return null;
	}

	function __isset($name)
	{
		return $this->to_key($name);
	}

	public function serialize($json = false, $lowercase = false)
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

	public function as_array($lowercase = false)
	{
		return $lowercase ? array_combine(array_keys($this->strtolower_keys), $this->response) : $this->response;
	}
}