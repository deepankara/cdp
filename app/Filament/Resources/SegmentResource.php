<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SegmentResource\Pages;
use App\Filament\Resources\SegmentResource\RelationManagers;
use App\Models\Segment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use App\Filament\Imports\CustomersImporter;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Session;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;


class SegmentResource extends Resource
{
    protected static ?string $model = Segment::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return 'https://cdn-icons-png.flaticon.com/512/4577/4577216.png';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                Select::make('database_type')
                ->options([
                    '1' => 'Login',
                    '2' => 'Others',
                ]),
                // TextInput::make('reporting_name')->required(),
                // TextInput::make('reporting_email')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                SelectColumn::make('database_type')
                ->options([
                    '1' => 'Login',
                    '2' => 'Others',
                ])->disabled()->label('Category')
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make()
                // ->hidden(fn ($record) => $record->database_type !== 2),

                ImportAction::make('importProducts')->hidden(fn ($record) => $record->database_type !== 2)
                ->modalDescription(new HtmlString(
                    ' <a href="https://drive.usercontent.google.com/download?id=1zO8ekHWx9U7mrbx_0Hoxxu6od7uxJqWw&export=download&authuser=0" class="text-blue-600 underline" download>Download Sample Books CSV</a>'
                ))
                ->before(function (ImportAction $action,Segment $record) {
                    Session::put('segment_id',$record->id);
                })
                ->importer(CustomersImporter::class),

                Action::make('analytics')->url(fn (Segment $record): string => route('filament.admin.resources.segments.emaildump', $record))
                ->openUrlInNewTab()
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSegments::route('/'),
            'emaildump' => Pages\SegmentDumpEmail::route('/{record}/email-dump'),
        ];
    }
}
