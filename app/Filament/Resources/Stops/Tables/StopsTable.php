<?php

namespace App\Filament\Resources\Stops\Tables;

use App\Enums\StopType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class StopsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (StopType $state): string => $state->color())
                    ->formatStateUsing(fn (StopType $state): string => $state->label())
                    ->searchable(),
                TextColumn::make('latitude')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('trail_order')
                    ->numeric()
                    ->sortable(),
                ToggleColumn::make('is_published'),
                TextColumn::make('user.name')
                    ->label('Created by')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('trail_order')
            ->filters([
                SelectFilter::make('type')
                    ->options(collect(StopType::cases())->mapWithKeys(
                        fn (StopType $type) => [$type->value => $type->label()]
                    )),
                TernaryFilter::make('is_published')
                    ->label('Published'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
