<?php

namespace App\Filament\Resources\Stops\Schemas;

use App\Enums\StopType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StopForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Location Details')
                    ->schema([
                        TextInput::make('title')
                            ->required(),
                        Textarea::make('description')
                            ->nullable()
                            ->columnSpanFull(),
                        Grid::make()
                            ->schema([
                                TextInput::make('latitude')
                                    ->numeric()
                                    ->required()
                                    ->placeholder('42.9012'),
                                TextInput::make('longitude')
                                    ->numeric()
                                    ->required()
                                    ->placeholder('-78.8721'),
                            ]),
                        Grid::make()
                            ->schema([
                                TextInput::make('trail_order')
                                    ->numeric()
                                    ->default(0),
                                Select::make('type')
                                    ->options(collect(StopType::cases())->mapWithKeys(
                                        fn (StopType $type) => [$type->value => $type->label()]
                                    ))
                                    ->default(StopType::Scenic->value)
                                    ->required(),
                            ]),
                        Toggle::make('is_published'),
                    ]),
                Section::make('Media')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('photo')
                            ->collection('photo')
                            ->multiple()
                            ->image()
                            ->reorderable()
                            ->label('Photos'),
                        SpatieMediaLibraryFileUpload::make('audio')
                            ->collection('audio')
                            ->multiple()
                            ->acceptedFileTypes(['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4'])
                            ->label('Audio'),
                        SpatieMediaLibraryFileUpload::make('video')
                            ->collection('video')
                            ->multiple()
                            ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                            ->label('Video'),
                    ]),
            ]);
    }
}
