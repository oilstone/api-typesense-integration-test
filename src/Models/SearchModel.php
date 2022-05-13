<?php

namespace Oilstone\ApiTypesenseIntegration\Models;

use Api\Result\Contracts\Record;
use Api\Schema\Property;
use Api\Schema\Schema;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Laravel\Scout\Searchable;

class SearchModel extends EloquentModel
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
     * @var Record
     */
    protected Record $record;

    /**
     * @var Schema
     */
    protected Schema $schema;

    /**
     * Make all attributes mass assignable
     *
     * @var string[]|bool
     */
    protected $guarded = false;

    /**
     * @param string $type
     * @param Record $record
     * @param Schema $schema
     * @return static
     */
    public static function make(string $type, Record $record, Schema $schema): static
    {
        return (new static($record->getAttributes()))
            ->setSchema($schema)
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
                if (!($property->optional ?? false)) {
                    switch ($property->getType()) {
                        case 'integer':
                            $value = $value ?: 0;
                            break;

                        case 'boolean':
                            $value = boolval($value ?: false);
                            break;

                        default:
                            $value = $value ?: '';
                    }
                }

                $attributes[$key] = $value;
            }
        }

        return $attributes;
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
        return array_column(array_filter($this->getIndexFields(), fn (array $field) => $field['index']), 'name');
    }

    /**
     * Get the value of record
     *
     * @return Record
     */
    public function getRecord(): Record
    {
        return $this->record;
    }

    /**
     * Set the value of record
     *
     * @param Record  $record
     * @return static
     */
    public function setRecord(Record $record): static
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
     * @return static
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of schema
     *
     * @return Schema
     */
    public function getSchema(): ?Schema
    {
        return $this->schema;
    }

    /**
     * Set the value of schema
     *
     * @param Schema  $schema
     * @return static
     */
    public function setSchema(?Schema $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * @return array
     */
    protected function getIndexFields(): array
    {
        return array_map(function (Property $property) {
            return [
                'name' => $property->getName(),
                'type' => $property->getType(),
                'facet' => $property->facet ?? false,
                'optional' => $property->optional ?? false,
                'index' => $property->searchable ?? false,
            ];
        }, $this->getIndexSchema());
    }

    /**
     * @return array
     */
    protected function getIndexSchema(): array
    {
        return array_values(array_filter($this->schema->getProperties(), fn (Property $property) => $property->indexed || $property->searchable));
    }

    /**
     * @param string $key
     * @return Property|null
     */
    protected function getIndexProperty(string $key): ?Property
    {
        return $this->schema->getProperty($key);
    }

    /**
     * @return string
     */
    protected function getSortingField(): string
    {
        foreach ($this->schema->getProperties() as $property) {
            if ($property->defaultSort) {
                return $property->getName() . ':' . (is_string($property->defaultSort) ? $property->defaultSort : 'asc');
            }
        }

        return 'id';
    }

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs(): string
    {
        return config('scout.prefix') . $this->type;
    }
}
