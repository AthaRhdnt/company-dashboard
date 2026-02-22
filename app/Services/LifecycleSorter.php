<?php
namespace App\Services;

use Illuminate\Support\Collection;

class LifecycleSorter
{
    public static function sort(Collection $collection): Collection
    {
        // Sort by time INSIDE each lifecycle
        $collection = $collection->sortBy(function ($model) {
            return $model->transaction_status === 'Ongoing'
                ? $model->id
                : -$model->id;
        });

        // Then bucket by lifecycle
        return $collection
            ->sortBy(fn($model) => $model->sort_group)
            ->values();
    }
}
