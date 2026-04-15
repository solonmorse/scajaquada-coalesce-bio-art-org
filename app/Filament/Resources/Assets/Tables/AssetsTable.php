<?php

namespace App\Filament\Resources\Assets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssetsTable
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
                    ->color(fn (string $state): string => match ($state) {
                        'audio' => 'info',
                        'video' => 'warning',
                        'photo' => 'success',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('stop.title')
                    ->label('Stop')
                    ->placeholder('—')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Created by')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'audio' => 'Audio',
                        'video' => 'Video',
                        'photo' => 'Photo',
                    ]),
                SelectFilter::make('stop_id')
                    ->label('Stop')
                    ->relationship('stop', 'title'),
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
