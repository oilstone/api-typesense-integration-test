<?php

namespace Oilstone\ApiTypesenseIntegration;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Oilstone\ApiTypesenseIntegration\Models\AllCollectionModel;
use Oilstone\ApiTypesenseIntegration\Models\ResourceModel;

class MakeSearchable implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    public string $type;

    /**
     * @var array
     */
    public array $models;

    /**
     * @param string $type
     * @param mixed $models
     * @return void
     */
    public function __construct(Collection $models)
    {
        $this->type = optional($models->first())->getType();
        $this->models = $models->map(fn ($m) => $m->toSearchableArray())->toArray();
    }

    /**
     * Handle the job.
     *
     * @return void
     */
    public function handle()
    {
        if (count($this->models) === 0) {
            return;
        }

        $baseModel = ResourceModel::make($this->type, $this->models[0]);
        $baseAllModel = AllCollectionModel::make($this->type, $this->models[0]);

        $resource = app(Str::of($baseModel->getType())->studly()->singular()->append('Resource')->toString());
        $allModels = new Collection;

        $models = collect($this->models)->map(function (array $record) use ($resource, $allModels) {
            $prepared = PrepareSearchData::prepare($record, $resource->makeModel(false));

            if ($prepared) {
                $allModels->push(ResourceModel::make($this->type, $prepared, $resource));

                return ResourceModel::make($this->type, $prepared, $resource);
            }

            return null;
        });

        $baseModel->searchableUsing()->update($models->filter(fn ($m) => $m !== null));
        $baseModel->searchableUsing()->delete($models->filter(fn ($m) => $m === null));

        $baseAllModel->searchableUsing()->update($allModels->filter(fn ($m) => $m !== null));
        $baseAllModel->searchableUsing()->delete($allModels->filter(fn ($m) => $m === null));
    }
}
