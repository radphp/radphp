<?php

namespace Rad\Error\Handler;

use Exception;
use Rad\Error\AbstractHandler;

/**
 * Json Handler
 *
 * @package Rad\Error\Handler
 */
class JsonHandler extends AbstractHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(Exception $exception)
    {
        $output = [];
        $jsonOptions = 0;
        header('Content-Type: application/json');

        $output['message'] = $exception->getMessage();
        $output['code'] = $exception->getCode();

        if ($this->error->isDebug()) {
            $output['file'] = $exception->getFile();
            $output['line'] = $exception->getLine();
            $output['trace'] = $exception->getTrace();
            $jsonOptions = JSON_PRETTY_PRINT;
        }

        $jsonOutput = json_encode($output, $jsonOptions);

        if (false !== $jsonOutput) {
            return $jsonOutput;
        } elseif (json_last_error() === JSON_ERROR_RECURSION) {
            unset($output['trace']);

            return json_encode($output, $jsonOptions);
        } else {
            throw new Exception(json_last_error_msg(), json_last_error(), $exception);
        }
    }
}
