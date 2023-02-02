<?php

namespace Hybridly\Tables\Columns\Concerns;

use Illuminate\Database\Eloquent\Model;

/** @mixin \Hybridly\Tables\Columns\Column */
trait HasRecord
{
    protected ?Model $record = null;

    public function record(Model $record): static
    {
        $this->record = $record;

        return $this;
    }

    public function getRecord(): ?Model
    {
        return $this->record;
    }
}
