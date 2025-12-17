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
use App\Models\Zipcode;
use App\Models\ActivityLog;
use App\Models\Vote;
use App\Models\VoteResponse;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Url;

class SharedCalendar extends Component
{
    use WithFileUploads, ManagesCalendarGroups;

    public Calendar $calendar;

    // --- Auth State ---
    public $isGuest = false;

    // --- Navigation State ---
    public $currentMonth;
    public $currentYear;

    #[Url]
    public $selectedDate;

    // --- Modal Visibility ---
    public $isModalOpen = false;
    public $isDeleteModalOpen = false;
    public $isUpdateModalOpen = false;
    public $isInviteModalOpen = false;
    public $isManageMembersModalOpen = false;
    public $isPromoteOwnerModalOpen = false;
    public $isLeaveCalendarModalOpen = false;
    public $isDeleteCalendarModalOpen = false;
    public $isLogsModalOpen = false;
    public $isParticipantsModalOpen = false;
    public $isManageMemberLabelsModalOpen = false;
    public $isManageRolesModalOpen = false;
    public $isPollResetModalOpen = false;

    // --- Invite State ---
    public $inviteModalTab = 'create';
    public $inviteLink = null;
    public $inviteUsername = '';
    public $inviteRole = 'member';

    // --- Logs State ---
    public $logSearch = '';

    // --- Interaction State ---
    public $viewingParticipantsEventId = null;
    public $commentInputs = [];
    public $commentLimits = [];
    public $pollSelections = [];

    // --- Delete/Sensitive Action State ---
    public $deleteCalendarPassword = '';
    public $promoteOwnerPassword = '';
    public $memberToPromoteId = null;

    // --- Member Label Management State ---
    public $managingMemberId = null;
    public $managingMemberName = '';

    // --- Event Management State ---
    public $eventId = null;
    public $eventToDeleteId = null;
    public $eventToDeleteDate = null;
    public $eventToDeleteIsRepeating = false;
    public $editingInstanceDate = null;

    // --- Form Fields ---
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

    #[Validate(['photos.*' => 'image|max:10240'])]
    public $photos = [];
    public $existing_images = [];

    // --- Features ---
    public $comments_enabled = true;
    public $opt_in_enabled = false;
    public $poll_title = '';
    public $poll_options = [];
    public $poll_max_selections = 1;
    public $poll_is_public = true;

    // --- Filters ---
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

    // --- PERMISSION HELPERS ---

    public function checkPermission($permissionSlug)
    {
        // 1. Owner: Full Access
        if ($this->isOwner) return true;

        // 2. Guest: Restricted View
        if ($this->isGuest) {
            // Guests can only view events and comments
            return in_array($permissionSlug, ['view_events', 'view_comments']);
        }

        // 3. Member: Check Database
        if (!Auth::check()) return false;
        return Auth::user()->hasPermissionInCalendar($this->calendar, $permissionSlug);
    }

    public function abortIfNoPermission($permissionSlug)
    {
        if (!$this->checkPermission($permissionSlug)) {
            $this->dispatch('action-message', message: 'Permission denied: ' . $permissionSlug);
            throw new \Exception('Permission denied: ' . $permissionSlug);
        }
    }

    public function mount(Calendar $calendar)
    {
        $this->calendar = $calendar;
        $user = Auth::user();

        // Check Membership
        $isMember = $user && $this->calendar->users->contains($user->id);

        // Check Guest Token (Cookie)
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

    // --- INTERACTION ---

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

        $this->commentInputs[$eventId] = '';
    }

    public function deleteComment($commentId)
    {
        $comment = Comment::find($commentId);
        if (!$comment) return;

        // Logic: You can delete your own comments freely.
        // To delete others', you need the specific permission.
        if ($comment->user_id !== Auth::id()) {
            $this->abortIfNoPermission('delete_any_comment');
        }

        $comment->delete();
    }

    public function toggleOptIn($eventId)
    {
        $this->abortIfNoPermission('rsvp_event');

        $user = Auth::user();
        if (!$user) return;

        $event = Event::find($eventId);
        if (!$event || !$event->opt_in_enabled) return;

        $participant = $event->participants()->where('user_id', $user->id)->first();

        if ($participant) {
            $event->participants()->updateExistingPivot($user->id, [
                'status' => $participant->pivot->status === 'opted_in' ? 'opted_out' : 'opted_in'
            ]);
        } else {
            $event->participants()->attach($user->id, ['status' => 'opted_in']);
        }
    }

    public function openParticipantsModal($eventId)
    {
        $this->viewingParticipantsEventId = $eventId;
        $this->isParticipantsModalOpen = true;
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

        foreach ($selections as $optionId) {
            VoteResponse::create([
                'vote_option_id' => $optionId,
                'user_id' => Auth::id()
            ]);
        }

        unset($this->pollSelections[$voteId]);
    }

    public function addPollOption() { $this->poll_options[] = ''; }
    public function removePollOption($index) { array_splice($this->poll_options, $index, 1); }

    // --- DATA & FILTERING ---

    public function getEventsProperty()
    {
        // 1. SECURITY: Strict View Check
        if (!$this->checkPermission('view_events')) {
            return collect();
        }

        $viewStart = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfMonth()->subDays(7);
        $viewEnd = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->endOfMonth()->addDays(14);

        $user = Auth::user();
        $userId = $user ? $user->id : null;
        $userRoleIds = $this->userRoleIds; // From ManagesCalendarGroups trait
        $isOwner = $this->isOwner;

        // Query Builder
        $query = $this->calendar->events()
            ->with([
                'groups',
                'genders',
                'country',
                'votes.options.responses',
                'participants'
            ]);

        // CONDITIONAL LOADING: Only load comments if user has permission
        if ($this->checkPermission('view_comments')) {
            $query->with('comments.user');
        }

        $rawEvents = $query
            ->where(function($q) use ($viewStart, $viewEnd) {
                $q->whereBetween('start_date', [$viewStart, $viewEnd])
                    ->orWhere('repeat_frequency', '!=', 'none');
            })
            ->get();

        // 2. FILTERING
        $filteredEvents = $rawEvents->filter(function($event) use ($user, $userId, $userRoleIds, $isOwner) {
            if ($userId && $event->created_by === $userId) return true;
            if ($isOwner) return true;

            if ($event->genders->isNotEmpty()) {
                if (!$user || !$user->gender_id || !$event->genders->contains('id', $user->gender_id)) return false;
            }

            if ($event->min_age) {
                if (!$user || !$user->birth_date || $user->birth_date->age < $event->min_age) return false;
            }

            $restrictedGroups = $event->groups
                ->where('pivot.is_restricted', true)
                ->where('is_selectable', true);

            if ($restrictedGroups->isNotEmpty()) {
                if ($restrictedGroups->pluck('id')->intersect($userRoleIds)->isEmpty()) return false;
            }

            if ($event->max_distance_km && $event->event_zipcode) {
                if (!$user || !$user->zipcode) return false;
                $eventZip = Zipcode::where('code', $event->event_zipcode)->first();
                if (!$eventZip) return false;
                $distance = $user->zipcode->distanceTo($eventZip);
                if (is_null($distance) || $distance > $event->max_distance_km) return false;
            }

            return true;
        });

        // 3. RECURRENCE EXPANSION
        $processedEvents = collect();
        foreach ($filteredEvents as $event) {
            $exclusions = $event->images['excluded_dates'] ?? [];

            if ($event->repeat_frequency === 'none') {
                if ($event->start_date->lt($viewEnd) && $event->end_date->gt($viewStart)) {
                    if (!in_array($event->start_date->format('Y-m-d'), $exclusions)) {
                        $processedEvents->push($event);
                    }
                }
                continue;
            }

            $eventDuration = $event->start_date->diff($event->end_date);
            $currentDate = Carbon::parse($event->start_date);

            while ($currentDate->lte($viewEnd)) {
                if ($event->repeat_end_date && $currentDate->format('Y-m-d') > $event->repeat_end_date->format('Y-m-d')) break;

                if ($currentDate->gte($viewStart)) {
                    $dateString = $currentDate->format('Y-m-d');
                    if (!in_array($dateString, $exclusions)) {
                        $instance = clone $event;
                        $instance->id = $event->id;
                        $instance->start_date = $currentDate->copy()->setTimeFrom($event->start_date);
                        $instance->end_date = $instance->start_date->copy()->add($eventDuration);

                        $instance->setRelation('votes', $event->votes);
                        $instance->setRelation('participants', $event->participants);

                        // Only attach comments if we loaded them
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

    // --- HELPERS & MODALS ---

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
        // Permission Check: View Active Links
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

    // --- ACTIONS ---

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
        $this->isManageMemberLabelsModalOpen = true;
        $this->isManageMembersModalOpen = false;
    }

    public function closeManageMemberLabels()
    {
        $this->isManageMemberLabelsModalOpen = false;
        $this->isManageMembersModalOpen = true;
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
        } else {
            $group->users()->attach($user->id, ['assigned_at' => now()]);
        }
    }

    public function openManageMembersModal() { $this->isManageMembersModalOpen = true; }

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
        $this->isManageRolesModalOpen = true;
    }

    public function openLogsModal() { $this->abortIfNoPermission('view_logs'); $this->isLogsModalOpen = true; }

    public function openPermissionsModal($tab = null, $userId = null)
    {
        // If trying to open specific User Permissions, enforce that specific permission
        if ($tab === 'users') {
            if (!$this->checkPermission('manage_user_permissions')) {
                $this->abortIfNoPermission('manage_user_permissions');
            }
        }
        // Otherwise, generic check (must have at least one management perm)
        elseif (
            !$this->checkPermission('manage_role_permissions') &&
            !$this->checkPermission('manage_label_permissions') &&
            !$this->checkPermission('manage_user_permissions')
        ) {
            $this->abortIfNoPermission('manage_role_permissions');
        }

        // Pass parameters to the modal component
        $this->dispatch('open-permissions-modal', tab: $tab, userId: $userId);
        $this->isManageMembersModalOpen = false;
    }

    public function openInviteModal() { $this->abortIfNoPermission('invite_users'); $this->reset('inviteLink', 'inviteUsername'); $this->inviteModalTab = 'create'; $this->isInviteModalOpen = true; }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->isDeleteModalOpen = false;
        $this->isUpdateModalOpen = false;
        $this->isInviteModalOpen = false;
        $this->isManageMembersModalOpen = false;
        $this->isPromoteOwnerModalOpen = false;
        $this->isLeaveCalendarModalOpen = false;
        $this->isDeleteCalendarModalOpen = false;
        $this->isLogsModalOpen = false;
        $this->isManageRolesModalOpen = false;
        $this->isParticipantsModalOpen = false;
        $this->isManageMemberLabelsModalOpen = false;
        $this->isPollResetModalOpen = false;

        $this->reset('deleteCalendarPassword', 'inviteUsername', 'inviteLink', 'promoteOwnerPassword', 'memberToPromoteId', 'logSearch', 'viewingParticipantsEventId', 'managingMemberId');
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function kickMember($userId)
    {
        $this->abortIfNoPermission('kick_users');
        if ($userId === Auth::id()) return;
        $this->calendar->users()->detach($userId);
        $this->dispatch('action-message', message: 'Member removed.');
    }

    public function changeRole($userId, $newRoleSlug)
    {
        // NEW: Use 'manage_user_permissions' instead of 'manage_permissions'
        $this->abortIfNoPermission('manage_user_permissions');

        if ($newRoleSlug === 'owner') {
            if (!$this->isOwner) return;
            $this->memberToPromoteId = $userId;
            $this->isPromoteOwnerModalOpen = true;
            return;
        }
        $role = Role::where('slug', $newRoleSlug)->first();
        $this->calendar->users()->updateExistingPivot($userId, ['role_id' => $role->id]);
    }

    public function promoteOwner()
    {
        $this->validate(['promoteOwnerPassword' => 'required|current_password']);
        if (!$this->isOwner || !$this->memberToPromoteId) return;
        $ownerRole = Role::where('slug', 'owner')->first();
        $memberRole = Role::where('slug', 'member')->first();
        $this->calendar->users()->updateExistingPivot(Auth::id(), ['role_id' => $memberRole->id]);
        $this->calendar->users()->updateExistingPivot($this->memberToPromoteId, ['role_id' => $ownerRole->id]);
        return redirect()->route('calendar.shared', $this->calendar);
    }

    // ... (Rest of the file: Event logic, Polls, render method, etc. remain unchanged) ...
    // Note: ensure saveEvent(), performUpdate(), etc. use the updated permissions 'add_labels' as shown in previous steps.

    public function openModal($date = null)
    {
        $this->abortIfNoPermission('create_events');
        $this->resetForm();
        if ($date) {
            $this->selectedDate = $date;
            $this->start_date = $date;
            $this->end_date = $date;
        }
        $this->isModalOpen = true;
    }

    public function editEvent($id, $instanceDate = null)
    {
        $event = $this->calendar->events()->with(['groups', 'genders', 'votes.options'])->find($id);
        if (!$event) return;

        if ($event->created_by === Auth::id()) {
            if (!$this->checkPermission('create_events')) return;
        } else {
            $this->abortIfNoPermission('edit_any_event');
        }

        $this->resetForm();
        $this->eventId = $event->id;
        $this->editingInstanceDate = $instanceDate ?? $event->start_date->format('Y-m-d');
        $this->title = $event->name;
        $this->description = $event->description;
        $this->location = $event->location;
        $this->url = $event->url;
        $this->is_all_day = $event->is_all_day;
        $this->repeat_frequency = $event->repeat_frequency;
        $this->repeat_end_date = $event->repeat_end_date ? $event->repeat_end_date->format('Y-m-d') : null;
        $this->existing_images = $event->images['urls'] ?? [];
        $this->comments_enabled = $event->comments_enabled;
        $this->opt_in_enabled = $event->opt_in_enabled;
        $this->selected_group_ids = $event->groups->pluck('id')->toArray();
        foreach ($event->groups as $group) {
            $this->group_restrictions[$group->id] = $group->pivot->is_restricted ?? false;
        }
        $this->selected_gender_ids = $event->genders->pluck('id')->toArray();
        $this->min_age = $event->min_age;
        $this->max_distance_km = $event->max_distance_km;
        $this->event_zipcode = $event->event_zipcode;
        $this->event_country_id = $event->event_country_id;
        $this->is_nsfw = $event->is_nsfw ?? false;

        $vote = $event->votes->first();
        if ($vote) {
            $this->poll_title = $vote->title;
            $this->poll_max_selections = $vote->max_allowed_selections;
            $this->poll_is_public = (bool) $vote->is_public;
            $this->poll_options = $vote->options->pluck('option_text')->toArray();
            while(count($this->poll_options) < 2) $this->poll_options[] = '';
        } else {
            $this->poll_title = '';
            $this->poll_options = ['', ''];
            $this->poll_max_selections = 1;
            $this->poll_is_public = true;
        }

        if ($instanceDate) {
            $this->start_date = $instanceDate;
            $duration = $event->start_date->diff($event->end_date);
            $this->end_date = Carbon::parse($instanceDate)->add($duration)->format('Y-m-d');
        } else {
            $this->start_date = $event->start_date->format('Y-m-d');
            $this->end_date = $event->end_date->format('Y-m-d');
        }
        $this->start_time = $event->start_date->format('H:i');
        $this->end_time = $event->end_date->format('H:i');
        $this->isModalOpen = true;
    }

    public function saveEvent()
    {
        if ($this->eventId) {
            $event = $this->calendar->events()->find($this->eventId);
            if ($event->created_by !== Auth::id()) $this->abortIfNoPermission('edit_any_event');
        } else {
            $this->abortIfNoPermission('create_events');
        }

        $this->validate();

        if (!empty($this->photos) && !$this->checkPermission('add_images')) {
            $this->addError('photos', 'You do not have permission to add images.');
            return;
        }

        $hasPoll = !empty(trim($this->poll_title)) && count(array_filter($this->poll_options)) >= 2;
        if ($hasPoll && !$this->eventId && !$this->checkPermission('create_poll')) {
            $this->addError('poll_title', 'You do not have permission to create polls.');
            return;
        }

        if (!empty($this->selected_group_ids) && !$this->checkPermission('add_labels')) {
            $this->addError('selected_group_ids', 'You do not have permission to attach labels to events.');
            return;
        }

        if ($this->eventId) {
            $event = $this->calendar->events()->find($this->eventId);

            $vote = $event->votes()->first();
            $newOptions = array_values(array_filter($this->poll_options, fn($o) => !empty(trim($o))));
            $pollHasChanges = false;

            if ($vote) {
                $currentOptions = $vote->options()->pluck('option_text')->toArray();
                if (
                    $vote->title !== $this->poll_title ||
                    $vote->max_allowed_selections !== (int)$this->poll_max_selections ||
                    $vote->is_public !== (bool)$this->poll_is_public ||
                    $currentOptions !== $newOptions
                ) {
                    $pollHasChanges = true;
                }
            } elseif (!empty(trim($this->poll_title))) {
                $pollHasChanges = true;
            }

            if ($vote && $pollHasChanges && $vote->total_votes > 0) {
                $this->isPollResetModalOpen = true;
                return;
            }

            if ($event->repeat_frequency !== 'none') {
                $this->isUpdateModalOpen = true;
                return;
            }
            $this->performUpdate($event);
        } else {
            $this->performCreate();
        }
        $this->isModalOpen = false;
    }

    public function confirmPollReset()
    {
        $event = $this->calendar->events()->find($this->eventId);
        if ($event) {
            $this->performUpdate($event);
        }
        $this->isPollResetModalOpen = false;
        $this->isModalOpen = false;
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

        if (empty(trim($this->poll_title))) {
            if ($vote) $vote->delete();
            return;
        }

        if ($vote) {
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

    public function performCreate()
    {
        $start = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $end = Carbon::parse($this->end_date . ' ' . $this->end_time);

        $imagesPayload = ['urls' => []];
        if ($this->checkPermission('add_images')) $imagesPayload['urls'] = $this->handleImageUploads();

        $event = Event::create([
            'calendar_id' => $this->calendar->id,
            'created_by' => Auth::id(),
            'name' => $this->title,
            'description' => $this->description,
            'start_date' => $start,
            'end_date' => $end,
            'is_all_day' => $this->is_all_day,
            'location' => $this->location,
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

        if ($this->checkPermission('add_labels')) $event->groups()->sync($this->getSyncData());

        $event->genders()->sync($this->selected_gender_ids);
        if ($this->checkPermission('create_poll')) $this->handlePollCreation($event);

        ActivityLog::create([
            'calendar_id' => $this->calendar->id,
            'user_id' => Auth::id(),
            'action' => 'created',
            'resource_type' => 'Event',
            'resource_id' => $event->id,
            'details' => ['name' => $event->name],
        ]);
    }

    public function performUpdate($event)
    {
        $start = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $end = Carbon::parse($this->end_date . ' ' . $this->end_time);

        $currentImages = $event->images ?? [];
        if ($this->checkPermission('add_images')) $currentImages['urls'] = $this->handleImageUploads();

        $event->update([
            'name' => $this->title,
            'description' => $this->description,
            'start_date' => $start,
            'end_date' => $end,
            'is_all_day' => $this->is_all_day,
            'location' => $this->location,
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

        if ($this->checkPermission('add_labels')) $event->groups()->sync($this->getSyncData());

        $event->genders()->sync($this->selected_gender_ids);

        if ($this->checkPermission('create_poll')) {
            $this->handlePollUpdate($event);
        }
    }

    public function confirmUpdate($mode)
    {
        $event = $this->calendar->events()->find($this->eventId);
        if (!$event) { $this->closeModal(); return; }

        $start = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $end = Carbon::parse($this->end_date . ' ' . $this->end_time);

        $newImages = ['urls' => []];
        if ($this->checkPermission('add_images')) $newImages['urls'] = $this->handleImageUploads();
        elseif (isset($event->images['urls'])) $newImages['urls'] = $event->images['urls'];

        $replData = [
            'name' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
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
        }
        $this->closeModal();
        $this->dispatch('event-updated');
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

        if ($isRepeating) $this->isDeleteModalOpen = true;
        else $this->confirmDelete('single');
    }

    public function confirmDelete($mode)
    {
        $event = $this->calendar->events()->find($this->eventToDeleteId);
        if (!$event) { $this->closeModal(); return; }

        if ($mode === 'single' || ($mode === 'future' && $event->start_date->format('Y-m-d') === $this->eventToDeleteDate)) {
            if ($mode === 'future') $this->deleteBranchedFutureEvents($event, $this->eventToDeleteDate);
            $event->delete();
        } elseif ($mode === 'future') {
            $stopDate = Carbon::parse($this->eventToDeleteDate)->subDay();
            $event->update(['repeat_end_date' => $stopDate]);
            $this->deleteBranchedFutureEvents($event, $this->eventToDeleteDate);
        } elseif ($mode === 'instance') {
            $images = $event->images ?? [];
            $excluded = $images['excluded_dates'] ?? [];
            $excluded[] = $this->eventToDeleteDate;
            $images['excluded_dates'] = array_unique($excluded);
            $event->update(['images' => $images]);
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
            if ($relEvent->start_date->format('Y-m-d') >= $cutoffDate) $relEvent->delete();
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

        $this->closeModal();
        $this->dispatch('action-message', message: 'Invitation sent!');
    }

    public function deleteInvite($id)
    {
        $this->abortIfNoPermission('manage_invites');
        $invite = Invitation::where('id', $id)->where('calendar_id', $this->calendar->id)->first();
        if ($invite) $invite->delete();
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
    }

    public function setInviteTab($tab)
    {
        // Logic check: Cannot view 'list' if no permission
        if ($tab === 'list' && !$this->checkPermission('view_active_links')) return;
        $this->inviteModalTab = $tab;
    }

    public function promptDeleteCalendar() { $this->resetErrorBag(); $this->deleteCalendarPassword = ''; $this->isDeleteCalendarModalOpen = true; }
    public function deleteCalendar() { $this->validate(['deleteCalendarPassword' => 'required|current_password']); if (!$this->isOwner) abort(403); $this->calendar->delete(); return redirect()->route('dashboard'); }
    public function promptLeaveCalendar() { $this->isLeaveCalendarModalOpen = true; }
    public function leaveCalendar() { if ($this->isOwner) return; $this->calendar->users()->detach(Auth::id()); return redirect()->route('dashboard'); }

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
}
