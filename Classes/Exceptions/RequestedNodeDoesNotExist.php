<?php

namespace Sandstorm\NeosApi\Exceptions;

class RequestedNodeDoesNotExist extends \Neos\Flow\Exception
{
    /**
     * @var integer
     */
    protected $statusCode = 400;
}
