<?php

namespace Rad\Network\Http\Response;

use InvalidArgumentException;
use Rad\Network\Http\Response;

/**
 * Json Response
 *
 * @package Rad\Network\Http\Response
 */
class JsonResponse extends Response
{
    /**
     * Rad\Network\Http\Response\JsonResponse constructor
     *
     * @param mixed  $content
     * @param int    $jsonOption
     * @param int    $status
     * @param string $reason
     * @param array  $headers
     */
    public function __construct($content, $jsonOption = 0, $status = 200, $reason = '', array $headers = [])
    {
        $content = json_encode($content, $jsonOption);
        $this->throwJsonException();

        $headers = $this->withContentType('application/json')->getHeaders();

        parent::__construct($content, $status, $reason, $headers);
    }

    /**
     * Factory method for chain ability.
     *
     * @param mixed  $content
     * @param int    $jsonOption
     * @param int    $status
     * @param string $reason
     * @param array  $headers
     *
     * @return Response
     */
    public static function create($content, $jsonOption = 0, $status = 200, $reason = '', array $headers = [])
    {
        return new static($content, $jsonOption, $status, $reason, $headers);
    }

    /**
     * Throw Json exception
     */
    protected function throwJsonException()
    {
        if (JSON_ERROR_NONE === json_last_error()) {
            return;
        }

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded';
                break;

            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON';
                break;

            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded';
                break;

            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error';
                break;

            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;

            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded';
                break;

            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded';
                break;

            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given';
                break;

            default:
                $error = 'Occurred error.';
                break;
        }

        throw new InvalidArgumentException($error);
    }
}
