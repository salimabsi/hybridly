<?php

namespace Hybridly\Tables\Columns\Concerns;

/** @mixin \Hybridly\Tables\Columns\Column */
trait IsSearchable
{
    protected bool $isIndividuallySearchable = false;
    protected bool $isSearchable = false;
    protected ?array $searchColumns = null;
    protected ?\Closure $searchQuery = null;

    public function searchable(bool|array $condition = true, ?\Closure $query = null, bool $isIndividual = false): static
    {
        if (\is_array($condition)) {
            $this->isSearchable = true;
            $this->searchColumns = $condition;
        } else {
            $this->isSearchable = $condition;
            $this->searchColumns = null;
        }

        $this->isIndividuallySearchable = $isIndividual;
        $this->searchQuery = $query;

        return $this;
    }

    public function getSearchColumns(): array
    {
        return $this->searchColumns ?? $this->getDefaultSearchColumns();
    }

    public function isSearchable(): bool
    {
        return $this->isSearchable;
    }

    protected function getDefaultSearchColumns(): array
    {
        return [str($this->getName())->afterLast('.')];
    }
}
