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
        // Collect all attachment URLs (images & files)
        $attachmentLinks = [];
        if (!empty($event->images['urls']) && is_array($event->images['urls'])) {
            foreach($event->images['urls'] as $url) {
                $attachmentLinks[] = asset($url); // Convert to full URL
            }
        }

        // Join multiple files with a separator
        $attachmentsString = !empty($attachmentLinks) ? implode(' | ', $attachmentLinks) : 'N/A';

        return [
            $event->name,
            $event->start_date->format('Y-m-d H:i'),
            $event->end_date->format('Y-m-d H:i'),
            $event->location ?? 'N/A',
            $event->url ?? 'N/A', // Website Link
            $attachmentsString,   // UPDATED: All Attachments
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
            'Attachments', // Renamed
            'Labels',
            'Description',
        ];
    }
}
