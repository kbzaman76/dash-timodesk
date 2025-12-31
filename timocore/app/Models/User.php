<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use App\Traits\UserNotify;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, UserNotify, GlobalStatus, SoftDeletes;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'ver_code',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'ver_code_send_at' => 'datetime',
    ];

    public const ROLE_ORGANIZER = 1;
    public const ROLE_MANAGER = 2;
    public const ROLE_STAFF = 3;

    public function isOrganizer()
    {
        return $this->role == self::ROLE_ORGANIZER;
    }

    public function isStaff()
    {
        return $this->role == self::ROLE_STAFF;
    }

    public function isManager()
    {
        return $this->role == self::ROLE_MANAGER;
    }

    public static function roleNameToId(string|int $role): int
    {
        if (is_numeric($role)) {
            return (int) $role;
        }

        return match (strtolower($role)) {
            'organizer' => self::ROLE_ORGANIZER,
            'manager' => self::ROLE_MANAGER,
            'staff' => self::ROLE_STAFF,
            default => (int) $role,
        };
    }

    public function hasRoleId(int $roleId): bool
    {
        return (int) $this->role === $roleId;
    }

    public function hasAnyRoleId(int ...$roleIds): bool
    {
        return in_array((int) $this->role, $roleIds, true);
    }

    public function hasAnyRoleName(string ...$roles): bool
    {
        $ids = array_map([self::class, 'roleNameToId'], $roles);
        return $this->hasAnyRoleId(...$ids);
    }


    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')->withTimestamps();
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class)->withTimestamps();
    }

    public function tracks()
    {
        return $this->hasMany(Track::class);
    }

    public function loginLogs()
    {
        return $this->hasMany(UserLogin::class);
    }


    public function deposits()
    {
        return $this->hasMany(Deposit::class)->where('status', '!=', Status::PAYMENT_INITIATE);
    }

    public function tickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function mobileNumber(): Attribute
    {
        return new Attribute(
            get: fn() => $this->dial_code . $this->mobile,
        );
    }

    // SCOPES
    public function scopeStaff($query)
    {
        return $query->where('has_organization', Status::NO);
    }

    public function scopeActive($query)
    {
        return $query->where('status', Status::USER_ACTIVE)->where('ev', Status::VERIFIED);
    }

    public function scopeTracking($query)
    {
        return $query->where('tracking_status', Status::YES);
    }

    public function scopeBanned($query)
    {
        return $query->where('status', Status::USER_BAN);
    }

    public function scopeEmailUnverified($query)
    {
        return $query->where('ev', Status::UNVERIFIED);
    }

    public function scopeEmailVerified($query)
    {
        return $query->where('ev', Status::VERIFIED);
    }

    public function statusBadge(): Attribute
    {
        return new Attribute(
            get: fn() => $this->badgeData(),
        );
    }

    public function badgeData()
    {
        $html = '';
        if ($this->status == Status::USER_ACTIVE) {
            $html = '<span class="badge badge--success">' . trans('Enabled') . '</span>';
        } elseif ($this->status == Status::USER_PENDING) {
            $html = '<span class="badge badge--warning">' . trans('Pending') . '</span>';
        } elseif ($this->status == Status::USER_REJECTED) {
            $html = '<span class="badge badge--danger">' . trans('Rejected') . '</span>';
        } else {
            $html = '<span class="badge badge--warning">' . trans('Disabled') . '</span>';
        }
        return $html;
    }

    public function trackingStatusBadge(): Attribute
    {
        return new Attribute(
            get: fn() => $this->trackingBadgeData(),
        );
    }

    public function trackingBadgeData()
    {
        $html = '';
        if ($this->tracking_status == Status::YES) {
            $html = '<span class="badge badge--success">' . trans('Enabled') . '</span>';
        } else {
            $html = '<span class="badge badge--warning">' . trans('Disabled') . '</span>';
        }
        return $html;
    }

    public function emailStatusBadge(): Attribute
    {
        return new Attribute(
            get: fn() => $this->emailBadgeData(),
        );
    }

    public function emailBadgeData()
    {
        $html = '';
        if ($this->ev == Status::YES) {
            $html = '<span class="badge badge--success fs-10">' . trans('Verified') . '</span>';
        } else {
            $html = '<span class="badge badge--base fs-10">' . trans('Unverified') . '</span>';
        }
        return $html;
    }

    public function roleText(): Attribute
    {
        return Attribute::get(function () {
            if ($this->role == Status::ORGANIZER) {
                return trans('Organizer');
            } elseif ($this->role == Status::MANAGER) {
                return trans('Manager');
            } else {
                return trans('Staff');
            }
        });
    }

    public function roleBadge(): Attribute
    {
        return new Attribute(
            get: fn() => $this->roleData(),
        );
    }

    public function roleData()
    {
        $html = '';
        if ($this->role == Status::ORGANIZER) {
            $html = '<span class="badge badge--success ">Organizer</span>';
        } elseif ($this->role == Status::MANAGER) {
            $html = '<span class="badge badge--primary ">Manager</span>';
        } else {
            $html = '<span class="badge badge--info ">Staff</span>';
        }
        return $html;
    }

    public function sendEmailVerificationLink()
    {
        if ($this->ver_code_send_at && $this->ver_code_send_at->addMinutes(2)->gt(now())) {
            $targetTime = $this->ver_code_send_at->addMinutes(2)->timestamp;
            $delay = max(0, $targetTime - time());

            return [
                'status' => false,
                'delay' => $delay
            ];
        }

        $this->ver_code = verificationCode(6);
        $this->ver_code_send_at = Carbon::now();
        $this->save();

        $link = URL::temporarySignedRoute(
            'user.verify.email.link',
            now()->addMinutes(30),
            ['id' => $this->id, 'code' => $this->ver_code]
        );

        notify($this, 'EVER_LINK', ['link' => $link], ['email']);

        return [
            'status' => true,
            'link' => $link
        ];
    }

    public function getRole()
    {
        $roleName = null;
        if ($this->isOrganizer()) {
            $roleName = 'Organizer';
        }
        if ($this->isManager()) {
            $roleName = 'Manager';
        }
        if ($this->isStaff()) {
            $roleName = 'Staff';
        }
        return $roleName;
    }

    public function screenshots()
    {
        return $this->hasMany(Screenshot::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return asset('assets/images/avatar.png');
        }

        try {
            [$storageId, $path] = explode('|', $this->image, 2);
        } catch (\Exception $e) {
            return asset('assets/images/avatar.png');
        }

        $storage = FileStorage::find($storageId);

        if (!$storage) {
            return asset('assets/images/avatar.png');
        }

        return rtrim($storage->base_path, '/') . '/' . ltrim($path, '/');
    }

    public function getAdminSocketToken()
    {
        $this->tokens()->where('name', 'socket-admin')->delete();

        return $this->createToken('socket-admin', ['socket'])->plainTextToken;
    }


    public function scopeMe($query)
    {
        $user = auth()->user();

        if (!$user || !$user->isStaff()) {
            return $query;
        }

        return $query->where('id', $user->id);
    }
}
