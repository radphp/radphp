<?php

namespace Rad\Authorization\Rbac;

/**
 * Resource Interface
 *
 * @package Rad\Authorization\Rbac
 */
interface ResourceInterface
{
    /**
     * Get resource name
     *
     * @return string
     */
    public function getName();

    /**
     * Get resource description
     *
     * @return string|null
     */
    public function getDescription();
}
