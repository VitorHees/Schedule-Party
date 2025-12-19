<!DOCTYPE html>
<html>
<head>
    <title>Calendar Export</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f9fafb; font-weight: bold; color: #111; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #7c3aed; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #7c3aed; font-size: 20px; }
        .header p { margin: 5px 0 0; color: #6b7280; }

        .badge { display: inline-block; padding: 2px 5px; border-radius: 3px; font-size: 9px; margin-right: 3px; color: white; background-color: #6b7280; }
        .badge-purple { background-color: #7c3aed; }
        .badge-red { background-color: #ef4444; }
        .badge-blue { background-color: #3b82f6; }

        .meta-row { margin-top: 2px; font-size: 10px; color: #555; }
        .sub-section { margin-top: 6px; border-top: 1px dashed #e5e7eb; padding-top: 4px; }
        a { color: #7c3aed; text-decoration: none; }

        .attachment-link {
            display: block;
            margin-top: 2px;
            font-size: 9px;
            color: #7c3aed;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ $calendarName }}</h1>
    <p>Exported on {{ now()->format('F j, Y') }}</p>
</div>

<table>
    <thead>
    <tr>
        <th style="width: 35%;">Event Info</th>
        <th style="width: 20%;">Date & Location</th>
        <th style="width: 20%;">Features & Polls</th>
        <th style="width: 25%;">Restrictions & Range</th>
    </tr>
    </thead>
    <tbody>
    @foreach($events as $event)
        <tr>
            {{-- 1. Info --}}
            <td>
                <strong style="font-size: 13px;">{{ $event->name }}</strong>
                @if($event->description)
                    <div style="margin-top: 4px; color: #555;">
                        {{ Str::limit($event->description, 100) }}
                    </div>
                @endif
                @if($event->url)
                    <div class="meta-row" style="margin-top: 4px;">
                        <a href="{{ $event->url }}" target="_blank">{{ $event->url }}</a>
                    </div>
                @endif

                {{-- Attachments Loop --}}
                @if(!empty($event->images['urls']) && is_array($event->images['urls']))
                    <div style="margin-top: 6px;">
                        @foreach($event->images['urls'] as $url)
                            @php
                                $filename = basename($url);
                                $ext = strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
                            @endphp
                            <a href="{{ asset($url) }}" target="_blank" class="attachment-link">
                                [View {{ $filename }}]
                            </a>
                        @endforeach
                    </div>
                @endif
            </td>

            {{-- 2. Date/Loc --}}
            <td>
                <strong>{{ $event->start_date->format('M j, Y') }}</strong>
                <div class="meta-row">{{ $event->start_date->format('H:i') }} - {{ $event->end_date->format('H:i') }}</div>
                <div class="meta-row" style="margin-top: 6px;">
                    Loc: {{ $event->location ?: 'Online/No Location' }}
                </div>
            </td>

            {{-- 3. Features --}}
            <td>
                <div style="margin-bottom: 4px;">
                    <span class="badge">{{ $event->comments_count }} Comments</span>
                    @if($event->opt_in_enabled)
                        <span class="badge badge-blue">{{ $event->participants->where('pivot.status', 'opted_in')->count() }} RSVP</span>
                    @endif
                </div>

                @if($event->votes->isNotEmpty())
                    <div class="sub-section">
                        @php $vote = $event->votes->first(); @endphp
                        <strong>Poll: {{ $vote->title }}</strong>
                        <ul style="margin: 2px 0 0 12px; padding: 0; list-style-type: circle; font-size: 9px;">
                            @foreach($vote->options as $opt)
                                <li>{{ $opt->option_text }}: {{ $opt->responses_count }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </td>

            {{-- 4. Restrictions --}}
            <td>
                <div style="margin-bottom: 6px;">
                    @foreach($event->groups as $group)
                        <span class="badge badge-purple">{{ $group->name }}</span>
                    @endforeach
                </div>

                @if($event->is_nsfw)
                    <div class="badge badge-red">NSFW</div>
                @endif
                @if($event->min_age)
                    <div class="meta-row">Min Age: {{ $event->min_age }}</div>
                @endif
                @if($event->genders->isNotEmpty())
                    <div class="meta-row">Genders: {{ $event->genders->pluck('name')->implode(', ') }}</div>
                @endif

                @if($event->event_zipcode || $event->country || $event->max_distance_km)
                    <div class="sub-section">
                        @if($event->country)
                            <div class="meta-row">Country: {{ $event->country->name }}</div>
                        @endif
                        @if($event->event_zipcode)
                            <div class="meta-row">Zip: {{ $event->event_zipcode }}</div>
                        @endif
                        @if($event->max_distance_km)
                            <div class="meta-row">Max Dist: {{ $event->max_distance_km }}km</div>
                        @endif
                    </div>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
