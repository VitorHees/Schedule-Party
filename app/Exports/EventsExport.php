<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EventsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $events;

    public function __construct($events)
    {
        $this->events = $events;
    }

    public function collection()
    {
        return $this->events;
    }

    public function map($event): array
    {
        // Get the first image URL if it exists
        $firstImage = null;
        if (!empty($event->images['urls']) && is_array($event->images['urls'])) {
            $firstImage = asset($event->images['urls'][0]); // Full URL for Excel
        }

        return [
            $event->name,
            $event->start_date->format('Y-m-d H:i'),
            $event->end_date->format('Y-m-d H:i'),
            $event->location ?? 'N/A',
            $event->url ?? 'N/A', // Website Link
            $firstImage ?? 'N/A', // Image Link
            $event->groups->pluck('name')->implode(', '),
            $event->description,
        ];
    }

    public function headings(): array
    {
        return [
            'Event Title',
            'Start Date',
            'End Date',
            'Location',
            'Website',
            'Image URL',
            'Labels',
            'Description',
        ];
    }
}
