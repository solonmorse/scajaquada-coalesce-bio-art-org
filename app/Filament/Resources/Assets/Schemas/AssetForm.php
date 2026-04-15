<?php

namespace App\Filament\Resources\Assets\Schemas;

use App\Models\Stop;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Asset Details')
                    ->schema([
                        TextInput::make('title')
                            ->required(),
                        Textarea::make('description')
                            ->nullable()
                            ->columnSpanFull(),
                        Select::make('type')
                            ->options([
                                'audio' => 'Audio',
                                'video' => 'Video',
                                'photo' => 'Photo',
                            ])
                            ->required()
                            ->live(),
                        Select::make('stop_id')
                            ->label('Attach to Stop')
                            ->options(Stop::orderBy('trail_order')->pluck('title', 'id'))
                            ->nullable()
                            ->searchable()
                            ->placeholder('— Unattached —'),
                    ]),
                Section::make('File')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('file')
                            ->collection('file')
                            ->acceptedFileTypes([
                                'image/jpeg', 'image/png', 'image/webp', 'image/gif',
                                'audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4',
                                'video/mp4', 'video/webm', 'video/ogg',
                            ])
                            ->label('File'),
                    ]),
            ]);
    }
}
