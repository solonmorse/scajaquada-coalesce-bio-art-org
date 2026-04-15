<?php

namespace App\Filament\Resources\Stops;

use App\Filament\Resources\Stops\Pages\CreateStop;
use App\Filament\Resources\Stops\Pages\EditStop;
use App\Filament\Resources\Stops\Pages\ListStops;
use App\Filament\Resources\Stops\RelationManagers\AssetsRelationManager;
use App\Filament\Resources\Stops\Schemas\StopForm;
use App\Filament\Resources\Stops\Tables\StopsTable;
use App\Models\Stop;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StopResource extends Resource
{
    protected static ?string $model = Stop::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StopForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StopsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AssetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStops::route('/'),
            'create' => CreateStop::route('/create'),
            'edit' => EditStop::route('/{record}/edit'),
        ];
    }
}
