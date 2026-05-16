<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Store;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, HasApiTokens, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function organizationUsers()
    {
        return $this->hasMany(OrganizationUser::class);
    }

    public function storeUsers()
    {
        return $this->hasMany(StoreUser::class);
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_users')
            ->using(OrganizationUser::class)
            ->withPivot(['id', 'role', 'is_active'])
            ->withTimestamps();
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_users')
            ->using(StoreUser::class)
            ->withPivot(['id', 'is_active'])
            ->withTimestamps();
    }

    public function accessibleStores()
    {
        $ownedOrganizationIds = $this->organizationUsers()
            ->where('role', 'owner')
            ->pluck('organization_id');

        if ($ownedOrganizationIds->isEmpty()) {
            return $this->stores()->get();
        }

        return Store::whereIn('organization_id', $ownedOrganizationIds)
            ->orWhereHas('users', fn ($query) => $query->where('users.id', $this->id))
            ->distinct()
            ->get();
    }
}
