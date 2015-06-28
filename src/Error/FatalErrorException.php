<?php

namespace Rad\Error;

use Rad\Core\Exception\BaseException;

/**
 * Fatal Error Exception
 *
 * @package Rad\Error
 */
class FatalErrorException extends BaseException
{
    /**
     * Rad\Error\FatalErrorException Constructor
     *
     * @param string      $message Message string.
     * @param int         $code    Code.
     * @param string|null $file    File name.
     * @param int|null    $line    Line number.
     */
    public function __construct($message, $code = 500, $file = null, $line = null)
    {
        parent::__construct($message, $code);

        if ($file) {
            $this->file = $file;
        }

        if ($line) {
            $this->line = $line;
        }
    }
}
