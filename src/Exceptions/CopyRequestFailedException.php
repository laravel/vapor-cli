<?php

namespace Laravel\VaporCli\Exceptions;

use RuntimeException;

class CopyRequestFailedException extends RuntimeException
{
    /**
     * The file index.
     *
     * @var int
     */
    public $index;

    /**
     * Create a new exception instance.
     *
     * @param  string  $message
     * @param  int  $index
     */
    public function __construct($message = '', $index = 0)
    {
        parent::__construct($message);

        $this->index = $index;
    }

    /**
     * Get the file index.
     *
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }
}
