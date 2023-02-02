<?php

namespace Hybridly\Tables;

use Hybridly\Tables\Actions\Action;
use Hybridly\Tables\Actions\BulkAction;
use Hybridly\Tables\Columns\Column;
use Hybridly\Tables\Concerns\EvaluatesClosures;
use Hybridly\Tables\Concerns\HasRecords;
use Hybridly\Tables\Contracts\HasTable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\PaginatedDataCollection;

class Table implements HasTable
{
    use EvaluatesClosures;
    use HasRecords;

    private mixed $cachedRecords = null;

    public function __construct(
        protected Request $request,
    ) {
    }

    public static function make(): static
    {
        return resolve(static::class);
    }

    protected function getTableColumns(): array
    {
        return [];
    }

    protected function getTableFilters(): array
    {
        return [];
    }

    /** @var array<Action> */
    protected function getInlineActions(): array
    {
        return [];
    }

    public function getCachedInlineActions(): Collection
    {
        // TODO
        return collect($this->getInlineActions())
            ->filter(fn (Action $action): bool => !$action->isHidden());
    }

    protected function getBulkActions(): array
    {
        return [];
    }

    public function getCachedBulkActions(): Collection
    {
        // TODO
        return collect($this->getBulkActions())
            ->filter(fn (BulkAction $action): bool => !$action->isHidden());
    }

    protected function getTableQuery(): Builder
    {
        return $this->getModel()->query();
    }

    protected function getModel(): Model
    {
        $model = $this->getModelClass();

        return new $model();
    }

    protected function getDataClass(): string
    {
        return str(static::class)
            ->classBasename()
            ->beforeLast('Table')
            ->prepend('\\App\\Data\\')
            ->append('Data')
            ->toString();
    }

    protected function getModelClass(): string
    {
        return str(static::class)
            ->classBasename()
            ->beforeLast('Table')
            ->prepend('\\App\\Models\\')
            ->toString();
    }

    public function getColumns(): Collection
    {
        return collect($this->getTableColumns())
            ->filter(fn (Column $column): bool => !$column->isHidden());
    }

    public function getRecords(): PaginatedDataCollection|CursorPaginatedDataCollection
    {
        if (!$this->cachedRecords) {
            $this->cachedRecords = $this->getTableRecords();
        }

        return $this->cachedRecords;
    }

    public function getSortableColumns(): Collection
    {
        return $this->getColumns()
            ->filter(fn (Column $column): bool => $column->isSortable());
    }

    public function jsonSerialize(): mixed
    {
        return [
            'fqcn' => static::class,
            'columns' => $this->getColumns(),
            'records' => $this->getRecords()->all(),
            'current_sort' => $this->getCurrentSort(),
            'inline_actions' => $this->getCachedInlineActions(),
            'bulk_actions' => $this->getCachedBulkActions(),
        ];
    }
}
