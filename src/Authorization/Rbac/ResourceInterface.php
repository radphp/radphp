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
     * Set resource name
     *
     * @param string $name Resource name
     *
     * @return self
     */
    public function setName($name);

    /**
     * Get resource name
     *
     * @return string
     */
    public function getName();

    /**
     * Set resource title
     *
     * @param string $title Resource title
     *
     * @return self
     */
    public function setTitle($title);

    /**
     * Get resource title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set resource description
     *
     * @param string $description Resource description
     *
     * @return self
     */
    public function setDescription($description);

    /**
     * Get resource description
     *
     * @return string
     */
    public function getDescription();
}
