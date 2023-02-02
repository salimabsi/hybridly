<?php

namespace Hybridly\Tables\Http\Controllers;

use Hybridly\Tables\Actions\BaseAction;
use Hybridly\Tables\Contracts\HasTable;
use Hybridly\Tables\DataTransferObjects\BulkActionData;
use Hybridly\Tables\DataTransferObjects\EndpointCallData;
use Hybridly\Tables\DataTransferObjects\InlineActionData;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class HybridEndpointController
{
    public const INLINE_ACTION = 'action:inline';
    public const BULK_ACTION = 'action:bulk';

    public function __invoke(Request $request): mixed
    {
        $call = EndpointCallData::fromRequest($request);

        return match ($call->type) {
            static::INLINE_ACTION => $this->executeInlineAction(InlineActionData::fromRequest($request)),
            static::BULK_ACTION => $this->executeBulkAction(BulkActionData::fromRequest($request)),
            default => throw new \Exception('Invalid action type: ' . $call->type)
        };
    }

    private function resolveAction(InlineActionData|BulkActionData $data): array
    {
        $table = resolve($data->fqcn); // TODO: improve exception

        // TODO: custom exception
        if (!\in_array(HasTable::class, class_implements($data->fqcn), true)) {
            throw new \Exception('Table class must implement ' . HasTable::class);
        }

        // TODO: cache actions
        $actions = match ($data::class) {
            InlineActionData::class => $table->getCachedInlineActions(),
            BulkActionData::class => $table->getCachedBulkActions(),
        };

        $action = $actions->first(fn (BaseAction $action) => $action->getName() === $data->action);

        if (!$action) {
            throw new \Exception('Invalid action: ' . $data->action);
        }

        return [$table, $action];
    }

    private function executeInlineAction(InlineActionData $data): mixed
    {
        [$table, $action] = $this->resolveAction($data);

        foreach ($table->getRecords() as $i => $record) {
            if ($data->record !== $i) {
                continue;
            }

            $result = $table->evaluate($action->getAction(), [
                'record' => $record,
            ]);

            if ($result instanceof Response) {
                $result->send();
                exit;
            }
        }

        return back();
    }

    private function executeBulkAction(BulkActionData $data): mixed
    {
        [$table, $action] = $this->resolveAction($data);

        // TODO: perfs for "all" (see how Filament does it)
        $records = collect($table->getRecords()->all())->filter(function ($_, int $i) use ($data) {
            if (\in_array($i, $data->except, strict: false)) {
                return false;
            }

            if (!$data->all && !\in_array($i, $data->only, strict: false)) {
                return false;
            }

            return true;
        });

        $result = $table->evaluate($action->getAction(), [
            'records' => $records,
        ]);

        if ($result instanceof Response) {
            $result->send();
            exit;
        }

        return back();
    }
}
