<?php

namespace Oilstone\ApiTypesenseIntegration\Models;

use Oilstone\ApiTypesenseIntegration\Schemas\CollectionSchema;

class AllCollectionModel extends ResourceModel
{


    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $attributes = parent::toSearchableArray();

        $attributes['id'] = $this->getType() . '-' . $attributes['id'];
        $attributes['content_type'] = $this->getType();
        $attributes['slug'] = $attributes['slug'] ?? '';

        return $attributes;
    }

    /**
     * @return array
     */
    protected function getIndexSchema(): array
    {
        return CollectionSchema::make()->getFullSchema();
    }

    /**
     * @return string
     */
    protected function getSortingField(): string
    {
        return 'publish_at';
    }

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return config('scout.prefix') . 'all';
    }
}
