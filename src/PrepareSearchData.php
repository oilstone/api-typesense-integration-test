<?php

namespace Oilstone\ApiTypesenseIntegration;

use Illuminate\Support\Str;
use Stitch\Model;

class PrepareSearchData
{
    /**
     * @param mixed $record
     * @param Model|null $model
     * @return null|array
     */
    public static function prepare(mixed $record, Model $model = null): ?array
    {
        if (!is_array($record) && method_exists($record, 'toArray')) {
            $record = $record->toArray();
        }

        if (!($prepared['is_published'] ?? true)) {
            return null;
        }

        if ($prepared['deleted_at'] ?? false) {
            return null;
        }

        if (isset($record['language']) && $record['language'] !== 'en' && $model) {
            $parent = $model->find($record['translates_id']);

            if ($parent) {
                $parent = $parent->toArray();

                foreach ($record as $key => $value) {
                    if ($value === '' || $value === null) {
                        $record[$key] = $parent[$key];
                    }
                }
            }
        }

        return $record;
    }

    /**
     * @param string $language
     * @param string $foreignResourceType
     * @param mixed $foreignId
     * @return mixed
     */
    public static function translateForeignKey(string $language, string $foreignResourceType, $foreignId): mixed
    {
        $foreignKeyModel = app(Str::of($foreignResourceType)->studly()->singular()->append('Resource')->toString())->makeModel(false);

        $record = $foreignKeyModel->find($foreignId);

        if ($language !== 'en') {
            $record = $foreignKeyModel->where('language', $language)->where('translates_id', $foreignId)->first() ?? $record;
        }

        return $record;
    }
}
