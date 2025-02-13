<?php

use Hybridly\Refining\Filters\CallbackFilter;
use Hybridly\Refining\Filters\Filter;
use Hybridly\Tests\Fixtures\Database\ProductFactory;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

it('can be serialized', function () {
    $filter = CallbackFilter::make('airpods_gen', function (Builder $builder, mixed $value) {
        return $builder->where('generation', $value);
    })->metadata([
        'foo' => 'bar',
    ]);

    expect($filter)
        ->toBeInstanceOf(Filter::class)
        ->jsonSerialize()->toBe([
            'name' => 'airpods_gen',
            'hidden' => false,
            'label' => 'Airpods gen',
            'type' => 'callback',
            'metadata' => [
                'foo' => 'bar',
            ],
            'is_active' => false,
            'value' => null,
        ]);
});

it('accepts custom parameters', function () {
    ProductFactory::new()->count(10)->create();
    ProductFactory::new()->create([
        'name' => 'Black AirPods',
    ]);

    $airpods = mock_refiner(
        query: ['filters' => ['airpods_gen' => 'AirPods']],
        refiners: [
            CallbackFilter::make('airpods_gen', function (Builder $builder, mixed $value, string $fallback) {
                $builder
                    ->where('name', $value)
                    ->orWhere('name', $fallback);
            }, ['fallback' => 'Black AirPods']),
        ],
    )->get();

    expect($airpods->first())->name->toBe('Black AirPods');
});

it('throws a validation exception when the type of the received value cannot be cast to the type of the expected one', function () {
    mock_refiner(
        query: ['filters' => ['airpods_gen' => 'abcd']],
        refiners: [
            CallbackFilter::make('airpods_gen', fn (Builder $builder, int $value) => null),
        ],
    )->get();
})->throws(ValidationException::class, 'This filter is invalid.');

it('filters according to the given callback', function () {
    ProductFactory::new()->count(10)->create();
    ProductFactory::new()->count(4)->sequence(
        ['name' => 'AirPods (2nd generation)'],
        ['name' => 'AirPods (3rd generation)'],
        ['name' => 'AirPods Pro (2nd generation)'],
        ['name' => 'AirPods Max'],
    )->create();

    $filters = mock_refiner(
        query: ['filters' => ['airpods_gen' => 2]],
        refiners: [
            CallbackFilter::make('airpods_gen', fn (Builder $builder, int $value) => match ($value) {
                2 => $builder->where('name', 'like', '%(2nd generation)'),
                3 => $builder->where('name', 'like', '%(3rd generation)'),
                default => null
            }),
        ],
    );

    expect($filters)
        ->first()->name->toBe('AirPods (2nd generation)')
        ->count()->toBe(2);
});
