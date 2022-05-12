<?php

namespace Oilstone\ApiTypesenseIntegration;

use Oilstone\ApiTypesenseIntegration\Models\ResourceModel as SearchModel;
use Oilstone\ApiTypesenseIntegration\Models\AllCollectionModel as SearchAllModel;
use Illuminate\Support\Str;
use Laravel\Scout\ModelObserver;
use Stitch\Events\Event;
use Stitch\Result\Record;
use Typesense\Exceptions\ObjectNotFound;

class MaintainSearchIndex extends ModelObserver
{
    /**
     * @param string $action
     * @return bool
     */
    public function handles(string $action): bool
    {
        return method_exists($this, $action);
    }

    /**
     * @param Event $event
     * @return $this
     */
    public function handle(Event $event): self
    {
        $action = $event->getName();

        if ($action === 'created' || $action === 'updated') {
            $action = 'saved';
        }

        if ($this->handles($action)) {
            $record = $event->getPayload()->record;

            if ($record instanceof Record) {
                $record = $record->hydrate();
            }

            $model = $record->getModel();
            $resource = $model->getResource();

            if (!is_array($record) && method_exists($record, 'toArray')) {
                $record = $record->toArray();
            }

            if (method_exists($resource, 'searchableId')) {
                $record['id'] = $resource->searchableId($record['id']);
            }

            $prepared = PrepareSearchData::prepare($record, $model);
            $indexCollection = optional($resource)->getEndpoint() ?? Str::kebab($model->getTable()->getName());

            try {
                if (!$prepared) {
                    $action = 'deleted';
                }

                $this->{$action}(SearchModel::make($indexCollection, $prepared ?: $record, $resource));
                $this->{$action}(SearchAllModel::make($indexCollection, $prepared ?: $record, $resource));
            } catch (ObjectNotFound) {
                // Ignore errors when an index object is not found
            }
        }

        return $this;
    }
}
