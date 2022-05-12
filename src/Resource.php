<?php

namespace Oilstone\ApiTypesenseIntegration;

use Oilstone\ApiTypesenseIntegration\Decorators\Decorator as Searchable;
use Oilstone\ApiResourceLoader\Resources\Stitch;
use Oilstone\ApiTypesenseIntegration\Schemas\CollectionSchema;

class Resource extends Stitch
{
    /**
     * @var array
     */
    protected array $indexSchema = [];

    /**
     * @var string|null
     */
    protected ?string $indexSort = null;

    public function __construct()
    {
        $this->decorators[] = Searchable::class;

        parent::__construct();
    }

    /**
     * @param array $property
     * @return void
     */
    public function addIndexProperty(array $property): void
    {
        $this->indexSchema[] = $property;
    }

    /**
     * @param string $name
     * @return void
     */
    public function addIndexPropertyType(string $name, array $propertySettings = []): void
    {
        if ($property = CollectionSchema::make()->getSchemaProperty($name)) {
            $this->indexSchema[] = array_merge($property, $propertySettings);
        }
    }

    /**
     * @param array $property
     * @return array|null
     */
    public function getIndexProperty(string $propertyName): ?array
    {
        foreach ($this->indexSchema as $property) {
            if ($property['name'] === $propertyName) {
                return $property;
            }

            if (($property['resolves'] ?? null) === $propertyName) {
                return $property;
            }

            if (in_array($propertyName, $property['insteadOf'] ?? [])) {
                return $property;
            }
        }

        return null;
    }

    /**
     * @param array $attributes
     * @return null|array
     */
    public function getIndexArray(array $attributes): ?array
    {
        if (!array_key_exists('search_index', $attributes)) {
            $attributes['search_index'] = '';
        }

        $attributes['search_index'] = $attributes['search_index'] ?: '';

        return $attributes;
    }

    /**
     * @return string|null
     */
    public function getIndexSort(): ?string
    {
        return $this->indexSort;
    }

    /**
     * @param string|null $indexSort
     * @return void
     */
    public function setIndexSort($indexSort): void
    {
        $this->indexSort = $indexSort;
    }

    /**
     * @return array
     */
    public function getIndexSchema(): array
    {
        return $this->indexSchema;
    }

    /**
     * @param array $indexSchema
     * @return void
     */
    public function setIndexSchema(array $indexSchema): void
    {
        $this->indexSchema = $indexSchema;
    }

    /**
     * @param array|null $attributes
     * @return null|string
     */
    public function searchableAs(array $attributes = null): ?string
    {
        return null;
    }
}
