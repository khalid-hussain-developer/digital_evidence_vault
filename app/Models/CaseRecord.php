<?php

namespace App\Models;

use App\Enums\CaseStatus;
use Database\Factories\CaseRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['case_number', 'title', 'description', 'status', 'created_by'])]
class CaseRecord extends Model
{
    /** @use HasFactory<CaseRecordFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CaseStatus::class,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'case_user', 'case_id', 'user_id')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(Evidence::class, 'case_id');
    }

    /**
     * Limit the query to cases the given user is allowed to see:
     * every case for an Admin, otherwise only cases they created or
     * were assigned to.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhereHas('assignedUsers', function (Builder $query) use ($user) {
                    $query->where('users.id', $user->id);
                });
        });
    }

    public function isAssignedTo(User $user): bool
    {
        return $this->created_by === $user->id
            || $this->assignedUsers->contains('id', $user->id);
    }
}
