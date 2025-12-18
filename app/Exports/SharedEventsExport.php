<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SharedEventsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        // Image Link
        $imageLink = 'N/A';
        if (!empty($event->images['urls']) && is_array($event->images['urls'])) {
            $imageLink = asset($event->images['urls'][0]);
        }

        // Poll Data
        $pollData = 'N/A';
        if ($event->votes->isNotEmpty()) {
            $vote = $event->votes->first();
            $options = $vote->options->map(fn($o) => "{$o->option_text} ({$o->responses_count})")->implode(', ');
            $pollData = "Title: {$vote->title} | Options: [{$options}]";
        }

        // Restrictions & Range
        $restrictions = [];
        if ($event->min_age) $restrictions[] = "Age {$event->min_age}+";
        if ($event->genders->isNotEmpty()) $restrictions[] = "Genders: " . $event->genders->pluck('name')->implode(', ');
        if ($event->is_nsfw) $restrictions[] = "NSFW";

        // Add Geo Logic to Restrictions
        if ($event->country) $restrictions[] = "Country: " . $event->country->name;
        if ($event->event_zipcode) $restrictions[] = "Zip: " . $event->event_zipcode;
        if ($event->max_distance_km) $restrictions[] = "Max Dist: " . $event->max_distance_km . "km";

        $restrictionString = empty($restrictions) ? 'None' : implode('; ', $restrictions);

        // Participation
        $participants = 'N/A';
        if ($event->opt_in_enabled) {
            $count = $event->participants->where('pivot.status', 'opted_in')->count();
            $participants = "{$count} Opted-in";
        }

        return [
            $event->name,
            $event->start_date->format('Y-m-d H:i'),
            $event->end_date->format('Y-m-d H:i'),
            $event->location ?? 'N/A', // Simple location name
            $event->url ?? 'N/A',
            $imageLink,
            $event->groups->pluck('name')->implode(', '), // Labels
            $restrictionString,
            $pollData,
            $participants,
            $event->comments_count . ' Comments',
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
            'Restrictions (Age/Gender/Geo)',
            'Poll Details',
            'Participation',
            'Comments',
            'Description',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
