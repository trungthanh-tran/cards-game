<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The "booted" method of the model.
     * Tự động tạo wallet khi user được tạo
     */
    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($user) {
            $user->wallet()->create([
                'balance' => 0
            ]);
        });
    }

    /**
     * Relationship: User có một wallet
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Relationship: User có nhiều orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Filament: Kiểm tra user có thể truy cập admin panel không
     * 
     * @param Panel $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Chỉ cho phép email có @admin.com hoặc user có quyền admin
        // Bạn có thể thay đổi logic này theo nhu cầu
        
        // Cách 1: Kiểm tra email
        if (str_ends_with($this->email, '@test.card')) {
            return true;
        }

        // Cách 2: Kiểm tra role (nếu bạn thêm field 'role' vào users table)
        // if ($this->role === 'admin') {
        //     return true;
        // }

        // Cách 3: Kiểm tra specific email
        $adminEmails = [
            'admin@admin.com',
            'admin@example.com',
        ];
        
        return in_array($this->email, $adminEmails);
    }

    /**
     * Scope: Lấy user đang active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Accessor: Format phone number
     */
    public function getFormattedPhoneAttribute()
    {
        if (!$this->phone) {
            return null;
        }
        
        // Format: 0123456789 => 0123 456 789
        return preg_replace('/(\d{4})(\d{3})(\d{3})/', '$1 $2 $3', $this->phone);
    }

    /**
     * Helper: Kiểm tra user có đủ tiền trong ví không
     */
    public function hasBalance($amount)
    {
        return $this->wallet && $this->wallet->balance >= $amount;
    }

    /**
     * Helper: Lấy tổng số tiền đã chi tiêu
     */
    public function getTotalSpentAttribute()
    {
        return $this->orders()
            ->where('status', 'completed')
            ->sum('total_amount');
    }

    /**
     * Helper: Lấy tổng số đơn hàng đã hoàn thành
     */
    public function getCompletedOrdersCountAttribute()
    {
        return $this->orders()
            ->where('status', 'completed')
            ->count();
    }
}