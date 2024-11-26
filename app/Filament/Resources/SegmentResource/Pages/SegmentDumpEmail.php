<?php

namespace App\Filament\Resources\SegmentResource\Pages;

use App\Filament\Resources\SegmentResource;
use Filament\Resources\Pages\Page;
use App\Models\EmailDump;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Filament\Tables\Table;
use App\Models\EmailAnalyticsTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Tables\Columns\IconColumn;
use App\Filament\Exports\EmailDumpExporter;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SegmentResource\Widgets\EmailDumpAnalytics;


class SegmentDumpEmail extends Page implements HasTable
{
    protected static string $resource = SegmentResource::class;

    use InteractsWithRecord,InteractsWithTable ;

    public function mount(int | string $record): void
    {
        Session::put('segment_id',$record);
        $this->record = $this->resolveRecord($record);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmailDumpAnalytics::class,
        ];
    }

    protected static string $view = 'filament.resources.segment-resource.pages.segment-dump-email';

    public function table(Table $table): Table
    {
        return $table
            ->query(EmailDump::query()->orderBy('created_at','desc')->where('segment_id',Session::get('segment_id')))
            ->headerActions([
                ExportAction::make()->exporter(EmailDumpExporter::class)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('segment_id',Session::get('segment_id')))
            ])
            ->columns([
                TextColumn::make('email')->searchable(),
                IconColumn::make('status')
                ->icon(fn (string $state): string => match ($state) {
                    'clean' => 'heroicon-o-check-badge',
                    'dirty' => 'heroicon-o-x-mark',
                    'error' => 'heroicon-o-x-mark',
                })
                ->color(fn (string $state): string => match ($state) {
                    'dirty' => 'danger',
                    'error' => 'danger',
                    'clean' => 'success',
                }),
                // TextColumn::make('reason')->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                ->options([
                    'clean' => 'Valid',
                    'error' => 'Error',
                    'dirty' => 'Invalid',
                ])->native(false)
            ])
            ;
    }
}
