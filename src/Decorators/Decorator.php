<?php

namespace Oilstone\ApiTypesenseIntegration\Decorators;

use Api\Schema\Schema;
use Oilstone\ApiResourceLoader\Decorators\StitchDecorator;
use Oilstone\ApiResourceLoader\Resources\Resource;
use Oilstone\ApiTypesenseIntegration\Resource as SearchResource;
use Oilstone\ApiResourceLoader\Resources\Stitch;
use Stitch\DBAL\Schema\Table;

class Decorator extends StitchDecorator
{
    /**
     * @param Resource $resource
     * @return void
     */
    public function decorate(Resource $resource): void
    {
        if ($resource instanceof Stitch) {
            $resource->addModelListener(MaintainSearchIndex::class);
        }

        if ($resource instanceof SearchResource) {
            $resource->addIndexPropertyType('search_index');
        }
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function decorateSchema(Schema $schema): void
    {
        $schema->getProperty('search_index')->sometimes()->nullable();
    }

    /**
     * @param Table $table
     * @return void
     * @noinspection PhpUndefinedMethodInspection
     */
    public function decorateModel(Table $table): void
    {
        $table->string('search_index');
    }
}
