<?php

namespace Laravel\VaporCli\Exceptions;

use RuntimeException;
use Throwable;

class CopyRequestFailedException extends RuntimeException
{
    /**
     * The file index.
     *
     * @var integer
     */
    public $index;

    /**
     * Create a new exception instance.
     *
     * @param  string  $message
     * @param  integer  $index
     */
    public function __construct($message = "", $index = 0)
    {
        parent::__construct($message);
        $this->index = $index;
    }

    /**
     * Get the file index.
     *
     * @return integer
     */
    public function getIndex()
    {
        return $this->index;
    }
}
