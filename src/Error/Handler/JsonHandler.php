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

        return json_encode($output, $jsonOptions);
    }
}
