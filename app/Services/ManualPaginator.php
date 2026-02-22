<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;

trait ManualPaginator
{
    protected function paginateCollection($collection, int $perPage = 50)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $collection->forPage($currentPage, $perPage),
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
            ]
        );
    }
}
