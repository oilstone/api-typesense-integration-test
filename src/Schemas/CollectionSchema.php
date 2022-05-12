<?php

namespace Oilstone\ApiTypesenseIntegration\Schemas;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class CollectionSchema
{
    /**
     * @var array
     */
    protected static array $schemaProperties = [];

    /**
     * @return void
     */
    public function __construct()
    {
        if (!static::$schemaProperties) {
            static::$schemaProperties = [
                [
                    'name' => 'id',
                    'type' => 'string',
                    'modifier' => fn ($id) => (string) $id,
                ],
                [
                    'name' => 'content_type',
                    'type' => 'string',
                    'facet' => true,
                ],
                [
                    'name' => 'language',
                    'type' => 'string',
                    'facet' => true,
                    'modifier' => fn ($language) => $language ?: 'en',
                ],
                [
                    'name' => 'translates_id',
                    'type' => 'int32',
                    'optional' => true,
                ],
                [
                    'name' => 'event_type',
                    'type' => 'string',
                    'optional' => true,
                    'insteadOf' => ['event_type_id'],
                ],
                [
                    'name' => 'location_type',
                    'type' => 'string',
                    'optional' => true,
                    'insteadOf' => ['location_type_id'],
                ],
                [
                    'name' => 'job_title',
                    'type' => 'string',
                    'optional' => true,
                    'insteadOf' => ['job_title_id'],
                ],
                [
                    'name' => 'job_group',
                    'type' => 'string',
                    'optional' => true,
                    'insteadOf' => ['job_group_id'],
                ],
                [
                    'name' => 'publication_type',
                    'type' => 'string',
                    'optional' => true,
                    'insteadOf' => ['publication_type_id'],
                ],
                [
                    'name' => 'is_published',
                    'type' => 'bool',
                ],
                [
                    'name' => 'slug',
                    'type' => 'string',
                    'facet' => true,
                ],
                [
                    'name' => 'title',
                    'type' => 'string',
                    'optional' => true,
                    'insteadOf' => ['first_name', 'last_name'],
                ],
                [
                    'name' => 'lede',
                    'type' => 'string',
                    'optional' => true,
                ],
                [
                    'name' => 'summary',
                    'type' => 'string',
                    'optional' => true,
                ],
                [
                    'name' => 'body',
                    'type' => 'string',
                    'optional' => true,
                ],
                [
                    'name' => 'image',
                    'type' => 'string',
                    'index' => false,
                    'optional' => true,
                ],
                [
                    'name' => 'image_credit',
                    'type' => 'string',
                    'index' => false,
                    'optional' => true,
                ],
                [
                    'name' => 'image_alt',
                    'type' => 'string',
                    'index' => false,
                    'optional' => true,
                ],
                [
                    'name' => 'window_title',
                    'type' => 'string',
                    'optional' => true,
                ],
                [
                    'name' => 'meta_description',
                    'type' => 'string',
                    'optional' => true,
                ],
                [
                    'name' => 'section',
                    'type' => 'string',
                    'optional' => true,
                ],
                [
                    'name' => 'search_index',
                    'type' => 'string',
                    'optional' => true,
                    'modifier' => fn ($searchIndex) => (string) $searchIndex,
                ],
                [
                    'name' => 'start_date',
                    'type' => 'int64',
                    'modifier' => fn ($date) => Carbon::parse($date)->timestamp,
                    'optional' => true,
                ],
                [
                    'name' => 'end_date',
                    'type' => 'int64',
                    'modifier' => fn ($date) => Carbon::parse($date)->timestamp,
                    'optional' => true,
                ],
                [
                    'name' => 'publish_at',
                    'type' => 'int64',
                    'modifier' => fn ($date) => Carbon::parse($date)->timestamp,
                ],
            ];
        }
    }

    /**
     * @return CollectionSchema
     */
    public static function make(): CollectionSchema
    {
        return new static();
    }

    /**
     * @return array
     */
    public function getFullSchema(): array
    {
        return static::$schemaProperties;
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function getSchemaProperty(string $name): ?array
    {
        return Arr::first(static::$schemaProperties, fn ($index) => $index['name'] === $name);
    }
}
