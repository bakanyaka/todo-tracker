<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Assignee
 *
 * @mixin IdeHelperAssignee
 * @property int $id
 * @property string $login
 * @property string $firstname
 * @property string $lastname
 * @property string $mail
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Issue[] $assignedIssues
 * @property-read int|null $assigned_issues_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Issue[] $participatedIssues
 * @property-read int|null $participated_issues_count
 * @method static \Illuminate\Database\Eloquent\Builder|Assignee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Assignee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Assignee query()
 * @method static \Illuminate\Database\Eloquent\Builder|Assignee whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Assignee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Assignee whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Assignee whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Assignee whereMail($value)
 */
	class IdeHelperAssignee extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Issue
 *
 * @mixin IdeHelperIssue
 * @property int $id
 * @property string $subject
 * @property string|null $department
 * @property string|null $assigned_to
 * @property BusinessDate $created_on
 * @property bool $control
 * @property int|null $service_id
 * @property int $priority_id
 * @property \App\BusinessDate|null $closed_on
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $status_id
 * @property float $on_pause_hours
 * @property BusinessDate $status_changed_on
 * @property \App\BusinessDate|null $updated_on
 * @property int $project_id
 * @property int|null $assigned_to_id
 * @property int|null $tracker_id
 * @property int|null $parent_id
 * @property string|null $start_date
 * @property \App\BusinessDate|null $due_date
 * @property-read float|null $actual_time
 * @property-read int|null $estimated_hours
 * @property-read int $is_paused
 * @property-read bool $is_tracked_by_current_user
 * @property-read int|float|null $percent_of_time_left
 * @property-read float|null $time_left
 * @property-read \App\Models\Priority $priority
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\Service|null $service
 * @property-read \App\Models\Status $status
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TimeEntry[] $time_entries
 * @property-read int|null $time_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static Builder|Issue active()
 * @method static Builder|Issue closed()
 * @method static Builder|Issue closedWithin($startDate, $endDate)
 * @method static Builder|Issue createdWithin($startDate, $endDate)
 * @method static Builder|Issue filter(\App\Filters\IssueFilters $filters)
 * @method static Builder|Issue inProcurement()
 * @method static Builder|Issue newModelQuery()
 * @method static Builder|Issue newQuery()
 * @method static Builder|Issue notInProcurement()
 * @method static Builder|Issue open()
 * @method static Builder|Issue paused()
 * @method static Builder|Issue query()
 * @method static Builder|Issue whereAssignedTo($value)
 * @method static Builder|Issue whereAssignedToId($value)
 * @method static Builder|Issue whereClosedOn($value)
 * @method static Builder|Issue whereControl($value)
 * @method static Builder|Issue whereCreatedAt($value)
 * @method static Builder|Issue whereCreatedOn($value)
 * @method static Builder|Issue whereDepartment($value)
 * @method static Builder|Issue whereDueDate($value)
 * @method static Builder|Issue whereId($value)
 * @method static Builder|Issue whereOnPauseHours($value)
 * @method static Builder|Issue whereParentId($value)
 * @method static Builder|Issue wherePriorityId($value)
 * @method static Builder|Issue whereProjectId($value)
 * @method static Builder|Issue whereServiceId($value)
 * @method static Builder|Issue whereStartDate($value)
 * @method static Builder|Issue whereStatusChangedOn($value)
 * @method static Builder|Issue whereStatusId($value)
 * @method static Builder|Issue whereSubject($value)
 * @method static Builder|Issue whereTrackerId($value)
 * @method static Builder|Issue whereUpdatedAt($value)
 * @method static Builder|Issue whereUpdatedOn($value)
 */
	class IdeHelperIssue extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Priority
 *
 * @mixin IdeHelperPriority
 * @property int $id
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|Priority newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Priority newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Priority query()
 * @method static \Illuminate\Database\Eloquent\Builder|Priority whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Priority whereName($value)
 */
	class IdeHelperPriority extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Project
 *
 * @mixin IdeHelperProject
 * @property int $id
 * @property string $name
 * @property string $identifier
 * @property string|null $description
 * @property int|null $parent_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Project[] $children
 * @property-read int|null $children_count
 * @method static \Illuminate\Database\Eloquent\Builder|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project query()
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereParentId($value)
 */
	class IdeHelperProject extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Service
 *
 * @mixin IdeHelperService
 * @property int $id
 * @property string $name
 * @property int $hours
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $project_id
 * @method static \Illuminate\Database\Eloquent\Builder|Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service query()
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereUpdatedAt($value)
 */
	class IdeHelperService extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Status
 *
 * @mixin IdeHelperStatus
 * @property int $id
 * @property string $name
 * @property bool $is_closed
 * @property bool $is_paused
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Status newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Status newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Status query()
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereIsClosed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereIsPaused($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereUpdatedAt($value)
 */
	class IdeHelperStatus extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Synchronization
 *
 * @mixin IdeHelperSynchronization
 * @property int $id
 * @property \Illuminate\Support\Carbon $completed_at
 * @property int $updated_items_count
 * @property string $type
 * @method static \Illuminate\Database\Eloquent\Builder|Synchronization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Synchronization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Synchronization query()
 * @method static \Illuminate\Database\Eloquent\Builder|Synchronization whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Synchronization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Synchronization whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Synchronization whereUpdatedItemsCount($value)
 */
	class IdeHelperSynchronization extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TimeEntry
 *
 * @mixin IdeHelperTimeEntry
 * @property int $id
 * @property int $assignee_id
 * @property int $project_id
 * @property int|null $issue_id
 * @property float $hours
 * @property string $comments
 * @property \Illuminate\Support\Carbon $spent_on
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TimeEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TimeEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TimeEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder|TimeEntry spentWithin($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeEntry whereAssigneeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeEntry whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeEntry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeEntry whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeEntry whereIssueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeEntry whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeEntry whereSpentOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeEntry whereUpdatedAt($value)
 */
	class IdeHelperTimeEntry extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Tracker
 *
 * @mixin IdeHelperTracker
 * @property int $id
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|Tracker newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tracker newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tracker query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tracker whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tracker whereName($value)
 */
	class IdeHelperTracker extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @mixin IdeHelperUser
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $username
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $is_admin
 * @property string|null $guid
 * @property string|null $domain
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Issue[] $issues
 * @property-read int|null $issues_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUsername($value)
 */
	class IdeHelperUser extends \Eloquent implements \LdapRecord\Laravel\Auth\LdapAuthenticatable, \LdapRecord\Laravel\LdapImportable {}
}

