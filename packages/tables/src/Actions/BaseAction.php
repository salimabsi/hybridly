<?php

namespace Hybridly\Tables\Actions;

use Hybridly\Tables\Actions;
use Hybridly\Tables\Concerns;
use Hybridly\Tables\Concerns\EvaluatesClosures;
use Illuminate\Support\Traits\Conditionable;

abstract class BaseAction implements \JsonSerializable
{
    use Actions\Concerns\HasAction;
    use Concerns\HasLabel;
    use Concerns\HasName;
    use Concerns\IsHideable;
    use Conditionable;
    use EvaluatesClosures;

    final public function __construct(string $name)
    {
        $this->name($name);
        $this->label(str($name)->headline()->lower()->ucfirst());
    }

    public static function make(string $name): static
    {
        $static = resolve(static::class, ['name' => $name]);

        return $static;
    }

    protected function getDefaultEvaluationParameters(): array
    {
        return [
            'action' => $this,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
        ];
    }
}
