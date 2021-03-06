<?php

namespace Oro\Bundle\ApiBundle\Request;

/**
 * Represents a collection of API sub-resources for a specific entity.
 */
class ApiResourceSubresources
{
    /** @var string */
    private $entityClass;

    /** @var ApiSubresource[] */
    private $subresources = [];

    /**
     * @param $entityClass
     */
    public function __construct($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * Gets the class name of the entity.
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * Checks if at least one sub-resource exists in this collection.
     *
     * @return bool
     */
    public function hasSubresources()
    {
        return !empty($this->subresources);
    }

    /**
     * Gets a list of all sub-resources.
     *
     * @return ApiSubresource[] [association name => ApiSubresource, ...]
     */
    public function getSubresources()
    {
        return $this->subresources;
    }

    /**
     * Gets a sub-resource.
     *
     * @param string $associationName
     *
     * @return ApiSubresource|null
     */
    public function getSubresource($associationName)
    {
        return $this->subresources[$associationName] ?? null;
    }

    /**
     * Adds a sub-resource.
     *
     * @param string              $associationName
     * @param ApiSubresource|null $subresource
     *
     * @return ApiSubresource
     */
    public function addSubresource($associationName, ApiSubresource $subresource = null)
    {
        if (null === $subresource) {
            $subresource = new ApiSubresource();
        }
        $this->subresources[$associationName] = $subresource;

        return $subresource;
    }

    /**
     * Removes a sub-resource.
     *
     * @param string $associationName
     */
    public function removeSubresource($associationName)
    {
        unset($this->subresources[$associationName]);
    }
}
