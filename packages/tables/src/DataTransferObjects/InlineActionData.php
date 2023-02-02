<?php

namespace Hybridly\Tables\DataTransferObjects;

use Illuminate\Http\Request;

final class InlineActionData
{
    public function __construct(
        public readonly string $action,
        public readonly int $record,
        public readonly string $fqcn,
        public readonly string $type,
    ) {
    }

    public static function fromRequest(Request $request): static
    {
        return new static(
            action: $request->string('action'),
            record: $request->integer('record'),
            fqcn: $request->string('fqcn'),
            type: $request->string('type'),
        );
    }
}
