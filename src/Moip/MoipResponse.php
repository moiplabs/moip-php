<?php

namespace Moip;

/**
 * Class MoipResponse
 * @package Moip
 */
class MoipResponse
{
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