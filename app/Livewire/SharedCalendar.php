<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Calendar;
use App\Models\Role;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Gender;
use App\Models\Country;
use App\Models\ActivityLog;
use App\Models\Vote;
use App\Models\VoteResponse;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Url;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SharedEventsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\HandlesGeocoding;

class SharedCalendar extends Component
{
    use WithFileUploads, ManagesCalendarGroups, HandlesGeocoding;

    public Calendar $calendar;

    // --- Auth State ---
    public $isGuest = false;

    // --- Navigation State ---
    public $currentMonth;
    public $currentYear;

    #[Url]
    public $selectedDate;

    /**
     * Active Modal State. Replaces many booleans.
     * Options: 'create_event', 'delete_confirmation', 'update_confirmation',
     * 'invite', 'manage_members', 'promote_owner', 'leave_calendar',
     * 'delete_calendar', 'logs', 'participants', 'manage_member_labels',
     * 'manage_roles', 'poll_reset', 'export'
     */
    public $activeModal = null;

    // --- Export State ---
    public $exportFormat = 'excel';
    public $exportMode = 'all';
    public $exportLabelId = null;

    // --- Invite State ---
    public $inviteModalTab = 'create';
    public $inviteLink = null;
    public $inviteUsername = '';
    public $inviteRole = 'member';

    // --- Logs State ---
    public $logSearch = '';
    public $logActionFilter = '';

    // --- Interaction State ---
    public $viewingParticipantsEventId = null;
    public $commentInputs = [];
    public $commentLimits = [];
    public $pollSelections = [];

    // --- Sensitive Actions ---
    public $deleteCalendarPassword = '';
    public $promoteOwnerPassword = '';
    public $memberToPromoteId = null;

    // --- Member Management ---
    public $managingMemberId = null;
    public $managingMemberName = '';

    // --- Event Form State ---
    public $eventId = null;
    public $eventToDeleteId = null;
    public $eventToDeleteDate = null;
    public $eventToDeleteIsRepeating = false;
    public $editingInstanceDate = null;

    public $editingCalendarName = '';

    #[Validate('required|min:3')]
    public $title = '';

    #[Validate('required|date')]
    public $start_date = '';

    #[Validate('required')]
    public $start_time = '10:00';

    #[Validate('required|date|after_or_equal:start_date')]
    public $end_date = '';

    #[Validate('required')]
    public $end_time = '11:00';

    public $is_all_day = false;
    public $location = '';
    public $url = '';
    public $description = '';
    public $repeat_frequency = 'none';
    public $repeat_end_date = null;

    // --- File Upload Logic ---
    public $photos = [];
    #[Validate(['temp_photos.*' => 'file|mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,txt,zip|max:10240'])]
    public $temp_photos = [];
    public $uploadIteration = 0;
    public $existing_images = [];

    // --- Event Features ---
    public $comments_enabled = true;
    public $opt_in_enabled = false;
    public $poll_title = '';
    public $poll_options = [];
    public $poll_max_selections = 1;
    public $poll_is_public = true;

    // --- Event Filters ---
    public $selected_group_ids = [];
    public $group_restrictions = [];
    public $selected_gender_ids = [];

    #[Validate('nullable|integer|min:0|max:150')]
    public $min_age = null;

    #[Validate('nullable|numeric|min:0|max:1000')]
    public $max_distance_km = null;

    public $event_zipcode = '';
    public $event_country_id = null;
    public $is_nsfw = false;

    protected $listeners = ['open-create-event-modal' => 'openModal'];

    // --- FILE UPLOAD HOOK ---
    public function updatedTempPhotos()
    {
        $this->validate([
            'temp_photos.*' => 'file|mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,txt,zip|max:10240'
        ]);

        foreach ($this->temp_photos as $photo) {
            $this->photos[] = $photo;
        }

        $this->temp_photos = [];
        $this->uploadIteration++;
    }

    // --- PERMISSION HELPERS ---

    public function checkPermission($permissionSlug)
    {
        if ($this->isOwner) return true;
        if ($this->isGuest) {
            return in_array($permissionSlug, ['view_events', 'view_comments']);
        }
        if (!Auth::check()) return false;

        // Note: For optimal performance, permissions should be eager loaded.
        // Assuming models efficiently handle relationships or caching.
        return Auth::user()->hasPermissionInCalendar($this->calendar, $permissionSlug);
    }

    public function abortIfNoPermission($permissionSlug)
    {
        if (!$this->checkPermission($permissionSlug)) {
            $this->dispatch('action-message', message: 'Permission denied: ' . $permissionSlug);
            throw new \Exception('Permission denied: ' . $permissionSlug);
        }
    }

    // --- LIFECYCLE ---

    public function mount(Calendar $calendar)
    {
        $this->calendar = $calendar;
        $user = Auth::user();

        // Eager load current user Pivot for Permissions to avoid N+1 queries in blade loops
        if ($user) {
            $this->calendar->load(['users' => fn($q) => $q->where('users.id', $user->id)]);
        }

        $isMember = $user && $this->calendar->users->contains($user->id);
        $guestToken = request()->cookie('guest_access_' . $calendar->id);
        $this->isGuest = $guestToken && $this->calendar->calendarUsers()
                ->where('guest_token', $guestToken)
                ->exists();

        if (!$isMember && !$this->isGuest) {
            abort(403, 'Access denied.');
        }

        if ($this->selectedDate) {
            try {
                $date = Carbon::parse($this->selectedDate);
                $this->currentMonth = $date->month;
                $this->currentYear = $date->year;
            } catch (\Exception $e) {
                $this->goToToday();
            }
        } else {
            $this->goToToday();
        }

        $this->start_date = $this->selectedDate;
        $this->end_date = $this->selectedDate;
        $this->poll_options = ['', ''];
    }

    // --- VISIBILITY FILTER ---
    // (Consolidating distance calculation if possible, or keeping specific logic)

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Simple Haversine implementation or similar
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    private function filterEventVisibility($event)
    {
        $user = Auth::user();
        $userId = $user ? $user->id : null;

        if (($userId && $event->created_by === $userId) || $this->isOwner) {
            return true;
        }

        // Gender Check
        if ($event->genders->isNotEmpty()) {
            if (!$user || !$user->gender_id || !$event->genders->contains('id', $user->gender_id)) {
                return false;
            }
        }

        // Age Check
        if ($event->min_age) {
            if (!$user || !$user->birth_date || $user->birth_date->age < $event->min_age) {
                return false;
            }
        }

        // Label Restrictions
        $restrictedGroups = $event->groups
            ->where('pivot.is_restricted', true)
            ->where('is_selectable', true);

        if ($restrictedGroups->isNotEmpty()) {
            if ($restrictedGroups->pluck('id')->intersect($this->userRoleIds)->isEmpty()) {
                return false;
            }
        }

        // Distance Check
        if ($event->max_distance_km && $event->latitude && $event->longitude) {
            if (!$user || !$user->zipcode) {
                return false;
            }
            $distance = $this->calculateDistance(
                $event->latitude,
                $event->longitude,
                $user->zipcode->latitude,
                $user->zipcode->longitude
            );
            if ($distance > $event->max_distance_km) {
                return false;
            }
        }

        return true;
    }

    // --- DATA FETCHING ---

    public function getEventsProperty()
    {
        if (!$this->checkPermission('view_events')) {
            return collect();
        }

        $viewStart = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfMonth()->subDays(7);
        $viewEnd = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->endOfMonth()->addDays(14);

        $query = $this->calendar->events()
            ->with(['groups', 'genders', 'country', 'votes.options.responses', 'participants']);

        if ($this->checkPermission('view_comments')) {
            $query->with('comments.user');
        }

        $rawEvents = $query
            ->where(function($q) use ($viewStart, $viewEnd) {
                $q->whereBetween('start_date', [$viewStart, $viewEnd])
                    ->orWhere('repeat_frequency', '!=', 'none');
            })
            ->get();

        $filteredEvents = $rawEvents->filter(fn($event) => $this->filterEventVisibility($event));

        $processedEvents = collect();

        foreach ($filteredEvents as $event) {
            $exclusions = $event->images['excluded_dates'] ?? [];

            // Non-repeating events
            if ($event->repeat_frequency === 'none') {
                if ($event->start_date->lt($viewEnd) && $event->end_date->gt($viewStart)) {
                    if (!in_array($event->start_date->format('Y-m-d'), $exclusions)) {
                        $processedEvents->push($event);
                    }
                }
                continue;
            }

            // Recurring Logic
            $eventDuration = $event->start_date->diff($event->end_date);
            $currentDate = Carbon::parse($event->start_date);

            while ($currentDate->lte($viewEnd)) {
                if ($event->repeat_end_date && $currentDate->format('Y-m-d') > $event->repeat_end_date->format('Y-m-d')) {
                    break;
                }

                if ($currentDate->gte($viewStart)) {
                    $dateString = $currentDate->format('Y-m-d');

                    if (!in_array($dateString, $exclusions)) {
                        $instance = clone $event;
                        $instance->id = $event->id;
                        $instance->start_date = $currentDate->copy()->setTimeFrom($event->start_date);
                        $instance->end_date = $instance->start_date->copy()->add($eventDuration);

                        $instance->setRelation('votes', $event->votes);
                        $instance->setRelation('participants', $event->participants);

                        if ($event->relationLoaded('comments')) {
                            $instance->setRelation('comments', $event->comments);
                        }

                        $processedEvents->push($instance);
                    }
                }

                switch ($event->repeat_frequency) {
                    case 'daily': $currentDate->addDay(); break;
                    case 'weekly': $currentDate->addWeek(); break;
                    case 'monthly': $currentDate->addMonth(); break;
                    case 'yearly': $currentDate->addYear(); break;
                    default: break 2;
                }
            }
        }

        return $processedEvents->sortBy('start_date');
    }

    // --- CRUD ACTIONS ---

    public function performCreate()
    {
        $start = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $end = Carbon::parse($this->end_date . ' ' . $this->end_time);

        $imagesPayload = ['urls' => []];
        if ($this->checkPermission('add_images')) {
            $imagesPayload['urls'] = $this->handleImageUploads();
        }

        // Geocoding via Trait
        $coords = $this->geocodeLocation($this->location);
        $lng = $coords[0] ?? null;
        $lat = $coords[1] ?? null;

        $event = Event::create([
            'calendar_id' => $this->calendar->id,
            'created_by' => Auth::id(),
            'name' => $this->title,
            'description' => $this->description,
            'start_date' => $start,
            'end_date' => $end,
            'is_all_day' => $this->is_all_day,
            'location' => $this->location,
            'latitude' => $lat,
            'longitude' => $lng,
            'url' => $this->url,
            'repeat_frequency' => $this->repeat_frequency,
            'repeat_end_date' => $this->repeat_frequency !== 'none' ? $this->repeat_end_date : null,
            'series_id' => Str::uuid()->toString(),
            'images' => $imagesPayload,
            'min_age' => $this->min_age,
            'max_distance_km' => $this->max_distance_km,
            'event_zipcode' => $this->event_zipcode,
            'event_country_id' => $this->event_country_id,
            'is_nsfw' => $this->is_nsfw,
            'comments_enabled' => $this->comments_enabled,
            'opt_in_enabled' => $this->opt_in_enabled,
        ]);

        if ($this->checkPermission('add_labels')) {
            $event->groups()->sync($this->getSyncData());
        }

        $event->genders()->sync($this->selected_gender_ids);

        if ($this->checkPermission('create_poll')) {
            $this->handlePollCreation($event);
        }

        $this->calendar->logActivity('created', 'Event', $event->id, Auth::user(), ['name' => $event->name]);
    }

    public function performUpdate($event)
    {
        $start = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $end = Carbon::parse($this->end_date . ' ' . $this->end_time);

        $currentImages = $event->images ?? [];
        if ($this->checkPermission('add_images')) {
            $currentImages['urls'] = $this->handleImageUploads();
        }

        // Geocoding via Trait
        $lat = $event->latitude;
        $lng = $event->longitude;

        if ($this->location !== $event->location || (!$lat && $this->location)) {
            $coords = $this->geocodeLocation($this->location);
            if ($coords) {
                $lng = $coords[0];
                $lat = $coords[1];
            }
        }

        $event->update([
            'name' => $this->title,
            'description' => $this->description,
            'start_date' => $start,
            'end_date' => $end,
            'is_all_day' => $this->is_all_day,
            'location' => $this->location,
            'latitude' => $lat,
            'longitude' => $lng,
            'url' => $this->url,
            'repeat_frequency' => $this->repeat_frequency,
            'repeat_end_date' => $this->repeat_frequency !== 'none' ? $this->repeat_end_date : null,
            'images' => $currentImages,
            'min_age' => $this->min_age,
            'max_distance_km' => $this->max_distance_km,
            'event_zipcode' => $this->event_zipcode,
            'event_country_id' => $this->event_country_id,
            'is_nsfw' => $this->is_nsfw,
            'comments_enabled' => $this->comments_enabled,
            'opt_in_enabled' => $this->opt_in_enabled,
        ]);

        if ($this->checkPermission('add_labels')) {
            $event->groups()->sync($this->getSyncData());
        }

        $event->genders()->sync($this->selected_gender_ids);

        if ($this->checkPermission('create_poll')) {
            $this->handlePollUpdate($event);
        }

        $this->calendar->logActivity('updated', 'Event', $event->id, Auth::user(), ['name' => $event->name]);
    }

    public function confirmUpdate($mode)
    {
        $event = $this->calendar->events()->find($this->eventId);
        if (!$event) {
            $this->closeModal();
            return;
        }

        $start = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $end = Carbon::parse($this->end_date . ' ' . $this->end_time);

        $newImages = ['urls' => []];
        if ($this->checkPermission('add_images')) {
            $newImages['urls'] = $this->handleImageUploads();
        } elseif (isset($event->images['urls'])) {
            $newImages['urls'] = $event->images['urls'];
        }

        // Geocoding via Trait
        $lat = $event->latitude;
        $lng = $event->longitude;

        if ($this->location !== $event->location || (!$lat && $this->location)) {
            $coords = $this->geocodeLocation($this->location);
            if ($coords) {
                $lng = $coords[0];
                $lat = $coords[1];
            }
        }

        $replData = [
            'name' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'latitude' => $lat,
            'longitude' => $lng,
            'is_all_day' => $this->is_all_day,
            'min_age' => $this->min_age,
            'max_distance_km' => $this->max_distance_km,
            'event_zipcode' => $this->event_zipcode,
            'event_country_id' => $this->event_country_id,
            'is_nsfw' => $this->is_nsfw,
            'images' => $newImages,
            'comments_enabled' => $this->comments_enabled,
            'opt_in_enabled' => $this->opt_in_enabled,
        ];

        if ($mode === 'instance') {
            $images = $event->images ?? [];
            $excluded = $images['excluded_dates'] ?? [];
            $excluded[] = $this->editingInstanceDate;
            $images['excluded_dates'] = array_unique($excluded);
            $event->update(['images' => $images]);

            $newEvent = $event->replicate();
            $newEvent->fill($replData);
            $newEvent->start_date = $start;
            $newEvent->end_date = $end;
            $newEvent->repeat_frequency = 'none';
            $newEvent->series_id = $event->series_id;
            $newEvent->push();

            if ($this->checkPermission('add_labels')) $newEvent->groups()->sync($this->getSyncData());
            $newEvent->genders()->sync($this->selected_gender_ids);
            if ($this->checkPermission('create_poll')) $this->handlePollCreation($newEvent);

            $this->calendar->logActivity('created_instance', 'Event', $newEvent->id, Auth::user(), ['original_id' => $event->id, 'name' => $newEvent->name]);

        } elseif ($mode === 'future') {
            $commonSeriesId = $event->series_id;
            $originalEndDate = $event->repeat_end_date;
            $stopDate = Carbon::parse($this->editingInstanceDate)->subDay();
            $event->update(['repeat_end_date' => $stopDate]);

            $newEvent = $event->replicate();
            $newEvent->fill($replData);
            $newEvent->start_date = $start;
            $newEvent->end_date = $end;
            $newEvent->repeat_frequency = $this->repeat_frequency;
            $newEvent->repeat_end_date = $this->repeat_frequency !== 'none' ? $this->repeat_end_date : $originalEndDate;
            $newEvent->series_id = $commonSeriesId;
            $newEvent->push();

            if ($this->checkPermission('add_labels')) $newEvent->groups()->sync($this->getSyncData());
            $newEvent->genders()->sync($this->selected_gender_ids);
            if ($this->checkPermission('create_poll')) $this->handlePollCreation($newEvent);

            $this->calendar->logActivity('split_series', 'Event', $newEvent->id, Auth::user(), ['original_id' => $event->id, 'name' => $newEvent->name]);
        }

        $this->closeModal();
        $this->dispatch('event-updated');
    }

    // --- EXPORT ---

    public function openExportModal()
    {
        $this->abortIfNoPermission('view_events');
        $this->reset(['exportFormat', 'exportMode', 'exportLabelId']);
        $this->exportFormat = 'excel';
        $this->exportMode = 'all';
        $this->activeModal = 'export';
    }

    public function exportEvents()
    {
        $this->abortIfNoPermission('view_events');

        $this->validate([
            'exportFormat' => 'required|in:excel,pdf',
            'exportMode' => 'required|in:all,label',
            'exportLabelId' => 'required_if:exportMode,label',
        ]);

        $query = $this->calendar->events()
            ->with([
                'groups',
                'genders',
                'votes.options' => function($q) {
                    $q->withCount('responses');
                },
                'participants',
                'country'
            ])
            ->withCount(['comments', 'participants']);

        if ($this->exportMode === 'label') {
            $query->whereHas('groups', function($q) {
                $q->where('groups.id', $this->exportLabelId);
            });
        }

        $rawEvents = $query->orderBy('start_date')->get();
        $visibleEvents = $rawEvents->filter(fn($event) => $this->filterEventVisibility($event));

        if ($visibleEvents->isEmpty()) {
            $this->addError('exportMode', 'No visible events found to export.');
            return;
        }

        $this->activeModal = null;

        if ($this->exportFormat === 'excel') {
            return Excel::download(new SharedEventsExport($visibleEvents), 'calendar-export.xlsx');
        }

        if ($this->exportFormat === 'pdf') {
            $pdf = Pdf::loadView('exports.shared-events-pdf', [
                'events' => $visibleEvents,
                'calendarName' => $this->calendar->name
            ]);
            $pdf->setPaper('a4', 'landscape');

            return response()->streamDownload(
                fn () => print($pdf->output()),
                'calendar-export.pdf'
            );
        }
    }

    // --- OTHER ACTIONS ---

    public function openModal($date = null)
    {
        $this->abortIfNoPermission('create_events');
        $this->resetForm();
        if ($date) {
            $this->selectedDate = $date;
            $this->start_date = $date;
            $this->end_date = $date;
        }
        $this->activeModal = 'create_event';
    }

    public function openInviteModal()
    {
        $this->abortIfNoPermission('invite_users');
        $this->reset('inviteLink', 'inviteUsername');
        $this->inviteModalTab = 'create';
        $this->activeModal = 'invite';
    }

    public function closeModal()
    {
        $this->activeModal = null;

        $this->reset('deleteCalendarPassword', 'inviteUsername', 'inviteLink', 'promoteOwnerPassword', 'memberToPromoteId', 'logSearch', 'logActionFilter', 'viewingParticipantsEventId', 'managingMemberId', 'exportFormat', 'exportMode', 'exportLabelId');
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function resetForm()
    {
        $this->eventId = null;
        $this->editingInstanceDate = null;
        $this->title = '';
        $this->start_time = '10:00';
        $this->end_time = '11:00';
        $this->is_all_day = false;
        $this->location = '';
        $this->url = '';
        $this->description = '';
        $this->repeat_frequency = 'none';
        $this->repeat_end_date = null;
        $this->photos = [];
        $this->temp_photos = []; // Reset Buffer
        $this->existing_images = [];
        $this->selected_group_ids = [];
        $this->group_restrictions = [];
        $this->selected_gender_ids = [];
        $this->min_age = null;
        $this->max_distance_km = null;
        $this->event_zipcode = '';
        $this->event_country_id = null;
        $this->is_nsfw = false;
        $this->comments_enabled = true;
        $this->opt_in_enabled = false;
        $this->poll_title = '';
        $this->poll_options = ['', ''];
        $this->poll_max_selections = 1;
        $this->poll_is_public = true;
        $this->resetValidation();
    }

    public function kickMember($userId)
    {
        $this->abortIfNoPermission('kick_users');
        if ($userId === Auth::id()) return;
        $user = User::find($userId);
        $this->calendar->users()->detach($userId);

        $this->calendar->logActivity('kicked_user', 'Calendar', $this->calendar->id, Auth::user(), [
            'kicked_username' => $user->username
        ]);

        $this->dispatch('action-message', message: 'Member removed.');
    }

    public function changeRole($userId, $newRoleSlug)
    {
        $this->abortIfNoPermission('manage_user_permissions');

        if ($newRoleSlug === 'owner') {
            if (!$this->isOwner) return;
            $this->memberToPromoteId = $userId;
            $this->activeModal = 'promote_owner';
            return;
        }
        $role = Role::where('slug', $newRoleSlug)->first();
        $this->calendar->users()->updateExistingPivot($userId, ['role_id' => $role->id]);

        $targetUser = User::find($userId);
        $this->calendar->logActivity('changed_role', 'User', $targetUser->id, Auth::user(), [
            'target_user' => $targetUser->username,
            'new_role' => $role->name
        ]);
    }

    public function promoteOwner()
    {
        $this->validate(['promoteOwnerPassword' => 'required|current_password']);
        if (!$this->isOwner || !$this->memberToPromoteId) return;
        $ownerRole = Role::where('slug', 'owner')->first();
        $memberRole = Role::where('slug', 'member')->first();
        $this->calendar->users()->updateExistingPivot(Auth::id(), ['role_id' => $memberRole->id]);
        $this->calendar->users()->updateExistingPivot($this->memberToPromoteId, ['role_id' => $ownerRole->id]);

        $this->calendar->logActivity('promoted_owner', 'User', $this->memberToPromoteId, Auth::user());

        return redirect()->route('calendar.shared', $this->calendar);
    }

    public function confirmPollReset()
    {
        // Optimized Poll Reset: Only touches votes table if possible,
        // or uses full event update if other fields changed.
        $event = $this->calendar->events()->find($this->eventId);
        if ($event) {
            // Delete existing votes since options changed
            $event->votes()->each(function ($v) {
                $v->options()->each(fn($o) => $o->responses()->delete());
                $v->options()->delete();
                $v->delete();
            });
            // Re-create the poll
            $this->handlePollCreation($event);

            // If other fields changed, we should technically run full update,
            // but for this specific modal action, we assume they hit "Reset & Save"
            // which implies saving the whole form.
            $this->performUpdate($event);
        }
        $this->activeModal = null;
    }

    private function getSyncData()
    {
        $data = [];
        $groups = $this->calendar->groups->keyBy('id');
        foreach ($this->selected_group_ids as $groupId) {
            $group = $groups->get($groupId);
            $isRestricted = ($group && $group->is_selectable) ? ($this->group_restrictions[$groupId] ?? true) : false;
            $data[$groupId] = ['is_restricted' => $isRestricted];
        }
        return $data;
    }

    private function handlePollCreation(Event $event)
    {
        if (!empty(trim($this->poll_title)) && count(array_filter($this->poll_options)) >= 2) {
            $vote = $event->votes()->create([
                'title' => $this->poll_title,
                'max_allowed_selections' => $this->poll_max_selections,
                'is_public' => $this->poll_is_public
            ]);
            foreach ($this->poll_options as $optionText) {
                if (!empty(trim($optionText))) {
                    $vote->options()->create(['option_text' => $optionText]);
                }
            }
        }
    }

    private function handlePollUpdate(Event $event)
    {
        $vote = $event->votes()->first();
        $newOptions = array_values(array_filter($this->poll_options, fn($o) => !empty(trim($o))));

        // Logic check: If title changed or options length different, user might need to reset.
        // For simplicity in this method, we proceed, but the UI triggers 'poll_reset' modal if unsafe.

        if (empty(trim($this->poll_title))) {
            if ($vote) $vote->delete();
            return;
        }

        if ($vote) {
            // If strictly updating text without structural changes, could keep votes.
            // But usually safer to reset if options change.
            $vote->options()->each(function($option) {
                $option->responses()->delete();
                $option->delete();
            });

            $vote->update([
                'title' => $this->poll_title,
                'max_allowed_selections' => $this->poll_max_selections,
                'is_public' => $this->poll_is_public
            ]);
        } else {
            $vote = $event->votes()->create([
                'title' => $this->poll_title,
                'max_allowed_selections' => $this->poll_max_selections,
                'is_public' => $this->poll_is_public
            ]);
        }

        foreach ($newOptions as $optionText) {
            $vote->options()->create(['option_text' => $optionText]);
        }
    }

    public function promptDeleteEvent($eventId, $date, $isRepeating)
    {
        $event = $this->calendar->events()->find($eventId);
        if (!$event) return;

        if ($event->created_by !== Auth::id()) {
            $this->abortIfNoPermission('delete_any_event');
        } else {
            if (!$this->checkPermission('create_events')) return;
        }

        $this->eventToDeleteId = $eventId;
        $this->eventToDeleteDate = $date;
        $this->eventToDeleteIsRepeating = $isRepeating;

        if ($isRepeating) $this->activeModal = 'delete_confirmation';
        else $this->confirmDelete('single');
    }

    public function confirmDelete($mode)
    {
        $event = $this->calendar->events()->find($this->eventToDeleteId);
        if (!$event) {
            $this->closeModal();
            return;
        }

        $logDetails = ['name' => $event->name, 'mode' => $mode, 'date' => $this->eventToDeleteDate];

        if ($mode === 'single' || ($mode === 'future' && $event->start_date->format('Y-m-d') === $this->eventToDeleteDate)) {
            if ($mode === 'future') $this->deleteBranchedFutureEvents($event, $this->eventToDeleteDate);
            $event->delete();
            $this->calendar->logActivity('deleted', 'Event', $this->eventToDeleteId, Auth::user(), $logDetails);

        } elseif ($mode === 'future') {
            $stopDate = Carbon::parse($this->eventToDeleteDate)->subDay();
            $event->update(['repeat_end_date' => $stopDate]);
            $this->deleteBranchedFutureEvents($event, $this->eventToDeleteDate);
            $this->calendar->logActivity('ended_series', 'Event', $event->id, Auth::user(), $logDetails);

        } elseif ($mode === 'instance') {
            $images = $event->images ?? [];
            $excluded = $images['excluded_dates'] ?? [];
            $excluded[] = $this->eventToDeleteDate;
            $images['excluded_dates'] = array_unique($excluded);
            $event->update(['images' => $images]);
            $this->calendar->logActivity('deleted_instance', 'Event', $event->id, Auth::user(), $logDetails);
        }
        $this->closeModal();
        $this->dispatch('event-deleted');
    }

    public function deleteBranchedFutureEvents($originalEvent, $cutoffDate)
    {
        if (!$originalEvent->series_id) return;
        $relatedEvents = $this->calendar->events()
            ->where('series_id', $originalEvent->series_id)
            ->where('id', '!=', $originalEvent->id)
            ->get();

        foreach ($relatedEvents as $relEvent) {
            if ($relEvent->start_date->format('Y-m-d') >= $cutoffDate) {
                $relEvent->delete();
            }
        }
    }

    public function inviteUserByUsername()
    {
        $this->abortIfNoPermission('invite_users');
        $this->validate(['inviteUsername' => 'required|exists:users,username']);
        $user = User::where('username', $this->inviteUsername)->first();

        if ($this->calendar->users->contains($user->id)) {
            $this->addError('inviteUsername', 'User is already a member.');
            return;
        }

        $role = Role::where('slug', 'member')->first() ?? Role::where('slug', 'regular')->first();
        if ($this->inviteRole) $role = Role::where('slug', $this->inviteRole)->first() ?? $role;

        Invitation::create([
            'calendar_id' => $this->calendar->id,
            'created_by' => Auth::id(),
            'invite_type' => 'email',
            'email' => $user->email,
            'role_id' => $role->id,
            'expires_at' => now()->addDays(7)
        ]);

        $this->calendar->logActivity('invited_user', 'User', $user->id, Auth::user(), [
            'invited_email' => $user->email,
            'role' => $role->name
        ]);

        $this->closeModal();
        $this->dispatch('action-message', message: 'Invitation sent!');
    }

    public function deleteInvite($id)
    {
        $this->abortIfNoPermission('manage_invites');
        $invite = Invitation::where('id', $id)->where('calendar_id', $this->calendar->id)->first();
        if ($invite) {
            $email = $invite->email;
            $invite->delete();
            $this->calendar->logActivity('deleted_invite', 'Calendar', $this->calendar->id, Auth::user(), [
                'email' => $email
            ]);
        }
    }

    public function generateInviteLink()
    {
        $this->abortIfNoPermission('invite_users');
        $role = Role::where('slug', 'member')->first();
        if ($this->inviteRole) $role = Role::where('slug', $this->inviteRole)->first() ?? $role;

        $invitation = Invitation::create([
            'calendar_id' => $this->calendar->id,
            'created_by' => Auth::id(),
            'invite_type' => 'link',
            'role_id' => $role->id
        ]);
        $this->inviteLink = route('invitations.accept', $invitation->token);

        $this->calendar->logActivity('generated_link', 'Calendar', $this->calendar->id, Auth::user(), [
            'role' => $role->name
        ]);
    }

    public function setInviteTab($tab)
    {
        if ($tab === 'list' && !$this->checkPermission('view_active_links')) return;
        $this->inviteModalTab = $tab;
    }

    public function promptDeleteCalendar() { $this->resetErrorBag(); $this->deleteCalendarPassword = ''; $this->activeModal = 'delete_calendar'; }
    public function deleteCalendar() { $this->validate(['deleteCalendarPassword' => 'required|current_password']); if (!$this->isOwner) abort(403); $this->calendar->delete(); return redirect()->route('dashboard'); }
    public function promptLeaveCalendar() { $this->activeModal = 'leave_calendar'; }

    public function leaveCalendar()
    {
        if ($this->isOwner) return;
        $this->calendar->users()->detach(Auth::id());
        $this->calendar->logActivity('left_calendar', 'User', Auth::id(), Auth::user());
        return redirect()->route('dashboard');
    }

    private function handleImageUploads()
    {
        $urls = $this->existing_images;
        foreach ($this->photos as $photo) {
            $path = $photo->store('events', 'public');
            $urls[] = '/storage/' . $path;
        }
        return $urls;
    }

    public function getDurationInDaysProperty()
    {
        return Carbon::parse($this->start_date)->startOfDay()->diffInDays(Carbon::parse($this->end_date)->startOfDay());
    }

    public function selectDate($date) { $this->selectedDate = $date; }
    public function nextMonth() { $d = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth(); $this->currentMonth = $d->month; $this->currentYear = $d->year; }
    public function previousMonth() { $d = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth(); $this->currentMonth = $d->month; $this->currentYear = $d->year; }
    public function goToToday() { $now = Carbon::now(); $this->currentMonth = $now->month; $this->currentYear = $now->year; $this->selectedDate = $now->format('Y-m-d'); }
    public function removeExistingImage($index) { unset($this->existing_images[$index]); $this->existing_images = array_values($this->existing_images); }
    public function removePhoto($index) { array_splice($this->photos, $index, 1); }
    public function updatedIsNsfw() {}
    public function updatedMinAge() { if ($this->min_age > 150) $this->min_age = 150; }
    public function updatedMaxDistanceKm() { if ($this->max_distance_km > 1000) $this->max_distance_km = 1000; }

    public function render()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $daysInMonth = $date->daysInMonth;
        $firstDayOfWeek = $date->dayOfWeek;

        $eventsByDate = collect();
        foreach ($this->events as $event) {
            $start = Carbon::parse($event->start_date)->startOfDay();
            $end = Carbon::parse($event->end_date)->endOfDay();
            $current = $start->copy();

            while ($current->lte($end)) {
                $dateKey = $current->format('Y-m-d');
                if (!$eventsByDate->has($dateKey)) $eventsByDate->put($dateKey, collect());
                $eventsByDate[$dateKey]->push($event);
                $current->addDay();
            }
        }

        return view('livewire.shared-calendar', [
            'daysInMonth' => $daysInMonth,
            'firstDayOfWeek' => $firstDayOfWeek,
            'monthName' => $date->format('F'),
            'eventsByDate' => $eventsByDate,
            'calendarDate' => $date
        ])->title($this->calendar->name);
    }

    public function postComment($eventId)
    {
        $this->abortIfNoPermission('create_comment');

        $content = $this->commentInputs[$eventId] ?? '';
        if (empty(trim($content))) return;

        $event = Event::find($eventId);
        if (!$event || !$event->comments_enabled) return;

        $event->comments()->create([
            'user_id' => Auth::id(),
            'content' => $content,
            'mentions' => []
        ]);

        $this->calendar->logActivity('commented', 'Event', $event->id, Auth::user(), [
            'event_name' => $event->name
        ]);

        $this->commentInputs[$eventId] = '';
    }

    public function deleteComment($commentId)
    {
        $comment = Comment::find($commentId);
        if (!$comment) return;

        if ($comment->user_id !== Auth::id()) {
            $this->abortIfNoPermission('delete_any_comment');
        }

        $eventId = $comment->event_id;
        $comment->delete();

        $this->calendar->logActivity('deleted', 'Comment', $eventId, Auth::user(), [
            'context' => 'Deleted a comment on an event'
        ]);
    }

    public function toggleOptIn($eventId)
    {
        $this->abortIfNoPermission('rsvp_event');

        $user = Auth::user();
        if (!$user) return;

        $event = Event::find($eventId);
        if (!$event || !$event->opt_in_enabled) return;

        $participant = $event->participants()->where('user_id', $user->id)->first();
        $newStatus = 'opted_in';

        if ($participant) {
            $newStatus = $participant->pivot->status === 'opted_in' ? 'opted_out' : 'opted_in';
            $event->participants()->updateExistingPivot($user->id, [
                'status' => $newStatus
            ]);
        } else {
            $event->participants()->attach($user->id, ['status' => 'opted_in']);
        }

        $this->calendar->logActivity($newStatus, 'Event', $event->id, $user, [
            'event_name' => $event->name
        ]);
    }

    public function openParticipantsModal($eventId)
    {
        $this->viewingParticipantsEventId = $eventId;
        $this->activeModal = 'participants';
    }

    public function loadMoreComments($eventId)
    {
        if (!isset($this->commentLimits[$eventId])) {
            $this->commentLimits[$eventId] = 5;
        }
        $this->commentLimits[$eventId] += 5;
    }

    public function addReplyMention($eventId, $username)
    {
        $current = $this->commentInputs[$eventId] ?? '';
        $this->commentInputs[$eventId] = $current . '@' . $username . ' ';
    }

    public function castVote($voteId)
    {
        $this->abortIfNoPermission('vote_poll');

        $vote = Vote::find($voteId);
        if (!$vote) return;

        $rawSelections = $this->pollSelections[$voteId] ?? [];
        $selections = array_keys(array_filter($rawSelections));

        if (empty($selections)) return;

        if (count($selections) > $vote->max_allowed_selections) {
            $this->addError('poll_' . $voteId, 'You can only select up to ' . $vote->max_allowed_selections . ' options.');
            return;
        }

        $optionIds = $vote->options()->pluck('id');
        VoteResponse::whereIn('vote_option_id', $optionIds)
            ->where('user_id', Auth::id())
            ->delete();

        $votedFor = [];

        foreach ($selections as $optionId) {
            VoteResponse::create([
                'vote_option_id' => $optionId,
                'user_id' => Auth::id()
            ]);

            $option = $vote->options()->find($optionId);
            if ($option) {
                $votedFor[] = $option->option_text;
            }
        }

        $this->calendar->logActivity('voted', 'Vote', $vote->id, Auth::user(), [
            'event_id' => $vote->event_id,
            'poll_title' => $vote->title,
            'choices' => $votedFor
        ]);

        unset($this->pollSelections[$voteId]);
    }

    public function addPollOption() { $this->poll_options[] = ''; }
    public function removePollOption($index) { array_splice($this->poll_options, $index, 1); }

    public function getSelectedDateEventsProperty()
    {
        return $this->events->filter(function($event) {
            $checkDate = Carbon::parse($this->selectedDate);
            return $checkDate->between(
                Carbon::parse($event->start_date)->startOfDay(),
                Carbon::parse($event->end_date)->endOfDay()
            );
        });
    }

    public function getActiveInvitesProperty()
    {
        if (!$this->checkPermission('view_active_links')) {
            return collect();
        }

        return $this->calendar->invitations()
            ->with(['role', 'creator'])
            ->where('invite_type', 'link')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getMembersProperty()
    {
        return $this->calendar->users()
            ->orderByPivot('joined_at', 'desc')
            ->get()
            ->map(function ($user) {
                $roleId = $user->pivot->role_id;
                $user->role_name = Role::find($roleId)->name ?? 'Member';
                $user->role_slug = Role::find($roleId)->slug ?? 'member';
                return $user;
            });
    }

    public function getManagingMemberRolesProperty()
    {
        if (!$this->managingMemberId) return [];
        return User::find($this->managingMemberId)
            ->groups()
            ->where('calendar_id', $this->calendar->id)
            ->pluck('groups.id')
            ->toArray();
    }

    public function getLogsProperty()
    {
        if (!$this->checkPermission('view_logs')) return collect();

        return ActivityLog::with('user')
            ->where('calendar_id', $this->calendar->id)
            ->when($this->logSearch, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('username', 'like', '%' . $this->logSearch . '%');
                });
            })
            ->when($this->logActionFilter, function ($query) {
                $query->where('action', $this->logActionFilter);
            })
            ->latest()
            ->take(50)
            ->get();
    }

    public function getParticipantsListProperty()
    {
        if (!$this->viewingParticipantsEventId) return collect();
        $event = Event::find($this->viewingParticipantsEventId);
        if (!$event) return collect();

        return $event->participants()
            ->wherePivot('status', 'opted_in')
            ->get();
    }

    public function getGendersProperty() { return Gender::where('name', '!=', 'Prefer not to say')->get(); }
    public function getCountriesProperty() { return Country::all(); }

    public function getIsOwnerProperty()
    {
        if (!Auth::check()) return false;
        $ownerRole = Role::where('slug', 'owner')->first();
        return $this->calendar->users()
            ->where('user_id', Auth::id())
            ->wherePivot('role_id', $ownerRole->id)
            ->exists();
    }

    public function getIsAdminProperty()
    {
        if (!Auth::check()) return false;
        if ($this->isOwner) return true;
        $adminRole = Role::where('slug', 'admin')->first();
        return $this->calendar->users()
            ->where('user_id', Auth::id())
            ->wherePivot('role_id', $adminRole->id)
            ->exists();
    }

    public function toggleRestriction($groupId)
    {
        if (!isset($this->group_restrictions[$groupId])) {
            $this->group_restrictions[$groupId] = true;
        } else {
            $this->group_restrictions[$groupId] = !$this->group_restrictions[$groupId];
        }
    }

    public function openManageMemberLabels($userId)
    {
        if (!$this->checkPermission('assign_labels')) return;
        $this->managingMemberId = $userId;
        $this->managingMemberName = User::find($userId)->username;
        $this->activeModal = 'manage_member_labels';
    }

    public function closeManageMemberLabels()
    {
        $this->activeModal = 'manage_members';
        $this->managingMemberId = null;
    }

    public function toggleMemberLabel($groupId)
    {
        if (!$this->checkPermission('assign_labels') || !$this->managingMemberId) return;
        $group = $this->calendar->groups()->find($groupId);
        if (!$group->is_selectable) return;
        $user = User::find($this->managingMemberId);

        if ($group->users()->where('users.id', $user->id)->exists()) {
            $group->users()->detach($user->id);
            $this->calendar->logActivity('removed_label_from_user', 'Group', $group->id, Auth::user(), [
                'group_name' => $group->name,
                'target_user' => $user->username
            ]);
        } else {
            $group->users()->attach($user->id, ['assigned_at' => now()]);
            $this->calendar->logActivity('assigned_label_to_user', 'Group', $group->id, Auth::user(), [
                'group_name' => $group->name,
                'target_user' => $user->username
            ]);
        }
    }

    public function openManageMembersModal() { $this->activeModal = 'manage_members'; }

    public function openManageRolesModal()
    {
        if (
            !$this->checkPermission('create_labels') &&
            !$this->checkPermission('join_labels') &&
            !$this->checkPermission('join_private_labels')
        ) {
            $this->abortIfNoPermission('create_labels');
        }
        $this->resetRoleForm();
        $this->activeModal = 'manage_roles';
    }

    public function openLogsModal()
    {
        $this->abortIfNoPermission('view_logs');
        $this->logSearch = '';
        $this->logActionFilter = '';
        $this->activeModal = 'logs';
    }

    public function openPermissionsModal($tab = null, $userId = null)
    {
        if ($tab === 'users') {
            if (!$this->checkPermission('manage_user_permissions')) {
                $this->abortIfNoPermission('manage_user_permissions');
            }
        }
        elseif (
            !$this->checkPermission('manage_role_permissions') &&
            !$this->checkPermission('manage_label_permissions') &&
            !$this->checkPermission('manage_user_permissions')
        ) {
            $this->abortIfNoPermission('manage_role_permissions');
        }

        $this->dispatch('open-permissions-modal', tab: $tab, userId: $userId);
        $this->activeModal = null; // Permissions are likely a separate livewire component or handled differently
    }

    public function openEditNameModal()
    {
        // Only owner can edit
        if (!$this->isOwner) return;

        $this->resetErrorBag();

        // Initialize the property with the current name
        $this->editingCalendarName = $this->calendar->name;

        $this->activeModal = 'edit_calendar_name';
    }

    public function updateCalendarName()
    {
        if (!$this->isOwner) abort(403);

        // Validate the separate property
        $this->validate([
            'editingCalendarName' => 'required|min:3|max:255',
        ]);

        // Update the model manually
        $this->calendar->update([
            'name' => $this->editingCalendarName
        ]);

        $this->calendar->logActivity('updated_name', 'Calendar', $this->calendar->id, Auth::user(), [
            'new_name' => $this->calendar->name
        ]);

        $this->closeModal();
        $this->dispatch('action-message', message: 'Calendar name updated.');
    }
}
