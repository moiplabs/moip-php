<?php

/**
 * MoIP Normalized Read-Only Environment Class
 *
 * @author     Paulo Cesar
 * @version    0.0.1
 * @package    Moip
 * @subpackage MoipEnvironment
 * @license    <a href="http://www.opensource.org/licenses/bsd-license.php">BSD License</a>
 */
/**
 * @property string $base_url Complete URI of the environment without trailing slash
 * @property string $name Name of the environment
 */
class MoipEnvironment {

	private $_base_url;
	private $_name;

	function __construct($base_url, $name)
	{
		$this->_base_url = $base_url;
		$this->_name = $name;
	}

	function __get($name)
	{
		if ($name === 'base_url')
		{
			return $this->_base_url;
		}
		elseif ($name === 'name')
		{
			return $this->_name;
		}

		return null;
	}
}