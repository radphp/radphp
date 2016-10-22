<?php

namespace Rad\Configure;

/**
 * Configurable Trait
 *
 * @package Rad\Configure
 */
trait ConfigurableTrait
{
    use DotAccessibleDataTrait {
        set as private internalSet;
        get as private internalGet;
        merge as private internalMerge;
    }

    /**
     * @var array
     */
    protected $configsContainer = [];

    /**
     * Load config
     *
     * @param array $config Config data
     * @param bool  $merge  Is config merge or overwrite
     */
    public function load(array $config, $merge = true)
    {
        if (true === $merge) {
            $this->internalMerge($config, $this->configsContainer);
        } else {
            $this->configsContainer = $config;
        }
    }

    /**
     * Set config
     *
     * @param string $identifier Parameter name.
     * @param mixed  $value      Value to set
     *
     * @return self
     */
    public function set($identifier, $value)
    {
        $this->internalSet($this->configsContainer, $identifier, $value);

        return $this;
    }

    /**
     * Get config
     *
     * @param string $identifier Parameter name.
     * @param null   $default    Default value
     *
     * @return array|null
     */
    public function get($identifier, $default = null)
    {
        $value = $this->internalGet($this->configsContainer, $identifier);

        if (is_null($value)) {
            return $default;
        }

        return $value;
    }

    /**
     * Indicates whether parameter exists or not
     *
     * @param string $identifier Parameter name.
     *
     * @return bool
     */
    public function has($identifier)
    {
        return $this->internalGet($this->configsContainer, $identifier) !== null;
    }
}
