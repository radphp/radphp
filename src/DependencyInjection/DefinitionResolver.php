<?php

namespace Rad\DependencyInjection;

use Closure;
use ReflectionClass;
use ReflectionMethod;

/**
 * Definition Resolver
 *
 * @package Rad\DependencyInjection
 */
class DefinitionResolver
{
    protected $container;
    protected $defaultDefinition = [
        'class' => '',
        'arguments' => [],
        'call' => []
    ];

    /**
     * Rad\DependencyInjection\DefinitionResolver constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Resolver
     *
     * @param mixed $definition
     * @param array $args
     *
     * @return mixed|object
     * @throws Exception
     */
    public function resolver($definition, array $args = [])
    {
        if ($definition instanceof Closure) {
            $resolvedDefinition = call_user_func_array($definition, $args);
        } elseif (is_object($definition)) {
            $resolvedDefinition = $definition;
        } elseif (is_string($definition)) {
            if (class_exists($definition)) {
                $reflectionObj = new ReflectionClass($definition);
                $resolvedDefinition = $reflectionObj->newInstanceArgs($args);
            } else {
                throw new Exception(sprintf('Class "%s" does not exist.', $definition));
            }
        } elseif (is_array($definition)) {
            $resolvedDefinition = self::fromArray($definition);
        } else {
            throw new Exception(sprintf('Definition type "%s" does not support.', gettype($definition)));
        }

        return $resolvedDefinition;
    }

    /**
     * Load definition from array
     *
     * @param array $definition
     *
     * @return object
     */
    protected function fromArray(array $definition)
    {
        $definition += $this->defaultDefinition;

        $refClass = new ReflectionClass($definition['class']);
        $instance = $refClass->newInstanceArgs(self::parseArguments($definition['arguments']));

        foreach ($definition['call'] as $methodName => $args) {
            $refMethod = new ReflectionMethod($instance, $methodName);
            $refMethod->invokeArgs($instance, self::parseArguments($args));
        }

        return $instance;
    }

    /**
     * Parse arguments
     *
     * @param array $args
     *
     * @return array
     * @throws Exception\ServiceNotFoundException
     */
    protected function parseArguments(array $args)
    {
        $output = [];

        foreach ($args as $arg) {
            if (is_string($arg) && strpos($arg, '@') === 0) {
                $service = substr($arg, 1);
                $output[] = $this->container->get($service);

                continue;
            }

            $output[] = $arg;
        }

        return $output;
    }
}
