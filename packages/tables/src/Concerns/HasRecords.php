<?php

namespace Hybridly\Tables\Concerns;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\PaginatedDataCollection;

/** @mixin \Hybridly\Tables\Table */
trait HasRecords
{
    protected PaginatedDataCollection|CursorPaginatedDataCollection|null $records = null;

    protected function getFilteredTableQuery(): Builder
    {
        $query = $this->getTableQuery();

        // $this->applyFiltersToTableQuery($query);
        // $this->applySearchToTableQuery($query);

        // foreach ($this->getCachedTableColumns() as $column) {
        //     $column->applyEagerLoading($query);
        //     $column->applyRelationshipAggregates($query);
        // }

        return $query;
    }

    protected function applySortingToTableQuery(Builder $query): Builder
    {
        if (\is_null($sort = $this->getCurrentSort())) {
            return $query;
        }

        return $query->orderBy($sort['column'], $sort['direction']);
    }

    protected function getCurrentSort(): ?array
    {
        if (blank($sort = $this->request->get('sort'))) {
            return null;
        }

        $isSortAllowed = $this->getSortableColumns()
            ->map->getName()
            ->contains(ltrim($sort, '-'));

        if (!$isSortAllowed) {
            return null;
        }

        $name = ltrim($sort, '-');

        return [
            'sort' => $sort,
            'column' => $name,
            'direction' => '-' === $sort[0] ? 'desc' : 'asc',
            'inverse' => '-' === $sort[0] ? $name : ('-' . $name),
        ];
    }

    public function getTableRecords(): PaginatedDataCollection|CursorPaginatedDataCollection
    {
        if ($this->records) {
            return $this->records;
        }

        $query = $this->getFilteredTableQuery();

        $this->applySortingToTableQuery($query);

        $data = $this->getDataClass();

        $this->records = $data::collection($query->paginate());

        return $this->records;
    }
}
