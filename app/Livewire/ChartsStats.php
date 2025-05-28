<?php
namespace App\Livewire;

use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use DB;
use Filament\Support\RawJs;
use App\Models\EmailAnalyticsTable;
use App\Models\WhatsappAnalytics;
use App\Models\SmsAnalytics;
use Illuminate\Support\Collection;

class ChartsStats extends ChartWidget
{
    protected static ?string $heading = 'Quarterly Analytics';

    protected function getData(): array
    {
        $startDate = now()->subMonths(3)->startOfMonth();
        $endDate = now()->endOfMonth();

        $months = collect();
        for ($i = 0; $i < 3; $i++) {
            $months->prepend(now()->subMonths($i)->startOfMonth()); // Use prepend to reverse order
        }

        $emailData = collect();
        $whatsappData = collect();
        $smsData = collect();

        foreach ($months as $month) {
            $emailData->push(
                EmailAnalyticsTable::whereNotNull('indian_time')
                    ->whereMonth('indian_time', $month->month)
                    ->whereYear('indian_time', $month->year)
                    ->distinct('sg_message_id')
                    ->count('sg_message_id')
            );
        }

        // Get WhatsApp counts for each month
        foreach ($months as $month) {
            $whatsappData->push(
                WhatsappAnalytics::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->whereNotNull('time')
                    ->distinct('wa_id')
                    ->count('wa_id')
            );
        }

        // Get SMS counts for each month
        foreach ($months as $month) {
            $smsData->push(
                SmsAnalytics::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count('phone')
            );
        }

        return [
            'datasets' => [
                [
                    'label' => 'Emails',
                    'data' => $emailData,
                    'pointBackgroundColor' => ' #21618c',
                    'backgroundColor' => ' #21618c',
                    'borderColor' => ' #21618c',
                    'tension' => '0.5'
                ],
                [
                    'label' => 'WhatsApp',
                    'data' => $whatsappData,
                    'pointBackgroundColor' => ' #239b56',
                    'backgroundColor' => ' #239b56',
                    'borderColor' => ' #239b56',
                    'tension' => '0.5'
                ],
                [
                    'label' => 'SMS',
                    'data' => $smsData,
                    'pointBackgroundColor' => ' #6c3483',
                    'backgroundColor' => ' #6c3483',
                    'borderColor' => ' #6c3483',
                    'tension' => '0.5'
                ],
            ],
            'labels' => $months->map(fn ($month) => $month->format('My')), // This will return the correct order
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
            {
                indexAxis: 'x',
            }
        JS);
    }
}
