<?php

namespace Rad\Configure\Loader;

use Rad\Configure\Exception;
use Rad\Configure\LoaderInterface;

/**
 * Php Loader
 *
 * @package Rad\Configure\Loader
 */
class PhpLoader implements LoaderInterface
{
    /**
     * @var array|string
     */
    protected $data;

    /**
     * PhpLoader constructor.
     *
     * @param string|array $data Set array of data or filename, it contain php array
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        if (is_array($this->data)) {
            return $this->data;
        }

        if (!file_exists($this->data) || !is_readable($this->data)) {
            throw new Exception(sprintf('Input file "%s" is not exist or is not readable', $this->data));
        }

        $output = include $this->data;

        if (!is_array($output)) {
            throw new Exception('You must return array in config file');
        }

        return $output;
    }
}
