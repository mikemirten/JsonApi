<?php
declare(strict_types = 1);

namespace Mikemirten\Component\JsonApi\Document\Behaviour;

use Mikemirten\Component\JsonApi\Document\AbstractRelationship;

/**
 * Relationships-container behaviour
 *
 * @see http://jsonapi.org/format/#document-resource-object-relationships
 *
 * @package Mikemirten\Component\JsonApi\Document\Behaviour
 */
trait RelationshipsContainer
{
    /**
     * Relationships
     *
     * @var AbstractRelationship[]
     */
    protected $relationships = [];

    /**
     * Set relationship
     *
     * @param string               $name
     * @param AbstractRelationship $relationship
     */
    public function setRelationship(string $name, AbstractRelationship $relationship)
    {
        $this->relationships[$name] = $relationship;
    }

    /**
     * Has relationship
     *
     * @param  string $name
     * @return bool
     */
    public function hasRelationship(string $name): bool
    {
        return isset($this->relationships[$name]);
    }

    /**
     * Get relationship
     *
     * @param  string $name
     * @return AbstractRelationship
     */
    public function getRelationship(string $name): AbstractRelationship
    {
        return $this->relationships[$name];
    }

    /**
     * Get relationships
     *
     * @return AbstractRelationship[]
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }
}