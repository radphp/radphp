<?php

namespace Rad\Logging\Adapter;

use Rad\Logging\AbstractAdapter;

/**
 * Null Adapter
 *
 * @package Rad\Logging\Adapter
 */
class NullAdapter extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function log($level, $message, $time, array $context = [])
    {
        // Null log
    }
}
