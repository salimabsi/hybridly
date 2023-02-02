<?php

namespace Hybridly\Tables\Actions\Concerns;

trait HasAction
{
    protected \Closure|null $action = null;

    public function action(\Closure|null $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getAction(): ?\Closure
    {
        return $this->action;
    }
}
