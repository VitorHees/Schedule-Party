<!DOCTYPE html>
<html>
<head>
    <title>Events Export</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; vertical-align: top; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #6b21a8; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #6b21a8; }
        .header p { margin: 5px 0 0; color: #6b7280; }
        .label { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-right: 4px; color: white; background-color: #6b7280; }
        .meta-row { margin-top: 4px; font-size: 11px; color: #555; }
        a { color: #6b21a8; text-decoration: underline; word-break: break-all; }

        /* Updated Attachment Styles */
        .attachment-link {
            display: block;
            margin-top: 4px;
            font-size: 10px;
            color: #6b21a8;
            text-decoration: none;
        }
        .attachment-link span {
            border-bottom: 1px dashed #6b21a8;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Personal Calendar Events</h1>
    <p>Generated on {{ now()->format('F j, Y') }}</p>
</div>

<table>
    <thead>
    <tr>
        <th style="width: 30%;">Event Details</th>
        <th style="width: 20%;">Date & Time</th>
        <th style="width: 30%;">Location & Link</th>
        <th style="width: 20%;">Labels & Attachments</th>
    </tr>
    </thead>
    <tbody>
    @foreach($events as $event)
        <tr>
            {{-- Title & Description --}}
            <td>
                <strong style="font-size: 14px;">{{ $event->name }}</strong>
                @if($event->description)
                    <div style="margin-top: 5px; color: #666; font-size: 11px;">
                        {{ Str::limit($event->description, 150) }}
                    </div>
                @endif
            </td>

            {{-- Date --}}
            <td>
                <div style="font-weight: bold;">{{ $event->start_date->format('M j, Y') }}</div>
                <div class="meta-row">{{ $event->start_date->format('H:i') }} - {{ $event->end_date->format('H:i') }}</div>
            </td>

            {{-- Location & Link --}}
            <td>
                <div>{{ $event->location ?: '-' }}</div>
                @if($event->url)
                    <div class="meta-row" style="margin-top: 5px;">
                        {{-- Shows the actual website URL --}}
                        <a href="{{ $event->url }}" target="_blank">{{ $event->url }}</a>
                    </div>
                @endif
            </td>

            {{-- Labels & Attachments (Files) --}}
            <td>
                <div style="margin-bottom: 5px;">
                    @foreach($event->groups as $group)
                        <span class="label" style="background-color: {{ $group->color }};">{{ $group->name }}</span>
                    @endforeach
                </div>

                {{-- UPDATED: Loop through all attachments --}}
                @if(!empty($event->images['urls']) && is_array($event->images['urls']))
                    <div style="margin-top: 8px;">
                        @foreach($event->images['urls'] as $url)
                            @php
                                $filename = basename($url);
                                $ext = strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
                            @endphp
                            <a href="{{ asset($url) }}" target="_blank" class="attachment-link">
                                {{-- Display: "filename.pdf (PDF)" --}}
                                <span>View {{ $filename }} ({{ $ext }})</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
