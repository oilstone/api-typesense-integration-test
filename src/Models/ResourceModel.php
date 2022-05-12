<?php

namespace Oilstone\ApiTypesenseIntegration\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Laravel\Scout\Searchable;
use Oilstone\ApiResourceLoader\Resources\Resource;

class ResourceModel extends EloquentModel
{
    use Searchable;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected string $type;

    /**
     * @var array
     */
    protected $record;

    /**
     * @var Resource|null
     */
    protected $resource = null;

    /**
     * Make all attributes mass assignable
     *
     * @var string[]|bool
     */
    protected $guarded = false;

    /**
     * @param array $record
     * @return self
     */
    public static function make(string $type, array $record, Resource $resource = null): self
    {
        return (new static($record))
            ->setResource($resource)
            ->setType($type)
            ->setTable($type)
            ->setRecord($record);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $attributes = [];

        foreach ($this->getAttributes() as $key => $value) {
            if ($property = $this->getIndexProperty($key)) {
                if (($property['resolves'] ?? false) === $key) {
                    $key = $property['name'];
                }

                if ($property['modifier'] ?? false) {
                    $value = $property['modifier']($value);
                } else {
                    $value = $value ?: null;
                }

                if (!($property['optional'] ?? false)) {
                    switch ($property['type']) {
                        case 'int32':
                        case 'int64':
                            $value = $value ?: 0;
                            break;

                        case 'bool':
                            $value = boolval($value ?: false);
                            break;

                        default:
                            $value = $value ?: '';
                    }
                }

                $attributes[$key] = $value;
            }
        }

        return optional($this->resource)->getIndexArray($attributes) ?? $attributes;
    }

    /**
     * The Typesense schema to be created.
     *
     * @return array
     */
    public function getCollectionSchema(): array
    {
        return [
            'name' => $this->searchableAs(),
            'fields' => $this->getIndexFields(),
            'default_sorting_field' => $this->getSortingField(),
        ];
    }

    /**
     * The fields to be queried against. See https://typesense.org/docs/0.21.0/api/documents.html#search.
     *
     * @return array
     */
    public function typesenseQueryBy(): array
    {
        return array_map(fn ($field) => $field['name'], array_values(array_filter($this->getIndexSchema(), fn ($field) => ($field['index'] ?? true) === true)));
    }

    /**
     * Get the value of record
     *
     * @return array
     */
    public function getRecord(): mixed
    {
        return $this->record;
    }

    /**
     * Set the value of record
     *
     * @param array  $record
     * @return self
     */
    public function setRecord(array $record): self
    {
        $this->record = $record;

        return $this;
    }

    /**
     * Get the value of type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @param string  $type
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of resource
     *
     * @return Resource|null
     */
    public function getResource(): ?Resource
    {
        return $this->resource;
    }

    /**
     * Set the value of resource
     *
     * @param Resource|null  $resource
     * @return self
     */
    public function setResource(?Resource $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return array
     */
    protected function getIndexFields(): array
    {
        return array_map(function (array $field) {
            return [
                'name' => $field['name'],
                'type' => $field['type'],
                'facet' => $field['facet'] ?? false,
                'optional' => $field['optional'] ?? !($field['index'] ?? true),
                'index' => $field['index'] ?? true,
            ];
        }, $this->getIndexSchema());
    }

    /**
     * @return array
     */
    protected function getIndexSchema(): array
    {
        return optional($this->resource)->getIndexSchema() ?? [];
    }

    /**
     * @param string $key
     * @return null|array
     */
    protected function getIndexProperty(string $key): ?array
    {
        return optional($this->resource)->getIndexProperty($key);
    }

    /**
     * @return string
     */
    protected function getSortingField(): string
    {
        return optional($this->resource)->getIndexSort() ?? 'id';
    }

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return optional($this->resource)->searchableAs($this->getAttributes()) ?? (config('scout.prefix') . $this->getTable());
    }
}
