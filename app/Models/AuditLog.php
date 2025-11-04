<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * User bị tác động (nếu có)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Admin thực hiện hành động
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Model bị tác động (polymorphic)
     */
    public function model()
    {
        return $this->morphTo('model');
    }

    /**
     * Helper: Tạo audit log
     */
    public static function createLog(
        string $action,
        string $description,
        ?User $user = null,
        ?User $admin = null,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        return self::create([
            'user_id' => $user?->id,
            'admin_id' => $admin?->id ?? auth()->id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Scope: Lọc theo action
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Lọc theo user
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope: Lọc theo admin
     */
    public function scopeByAdmin($query, User $admin)
    {
        return $query->where('admin_id', $admin->id);
    }

    /**
     * Accessor: Format changes
     */
    public function getChangesAttribute(): string
    {
        if (!$this->old_values || !$this->new_values) {
            return '';
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[] = "{$key}: {$oldValue} → {$newValue}";
            }
        }

        return implode(', ', $changes);
    }
}