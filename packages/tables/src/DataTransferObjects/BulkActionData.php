<?php

namespace Hybridly\Tables\DataTransferObjects;

use Illuminate\Http\Request;

final class BulkActionData
{
    public function __construct(
        public readonly string $action,
        public readonly string $fqcn,
        public readonly bool $all,
        public readonly array $except,
        public readonly array $only,
    ) {
    }

    public static function fromRequest(Request $request): static
    {
        return new static(
            action: $request->string('action'),
            fqcn: $request->string('fqcn'),
            all: $request->boolean('all'),
            except: $request->input('except', []),
            only: $request->input('only', []),
        );
    }
}
