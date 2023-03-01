<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    protected string $guard_name = 'sanctum';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
        'date_of_appointment',
        'branch_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    protected  $attributes = [
        'referee_1' => 'altara',
        'referee_2' => 'altara',
        'referee_1_phone_no' => 'altara',
        'referee_2_phone_no' => 'altara',
        'category' => 'contract',
        'highest_qualification' => 'vendor',
        'hr_id' => 1,
        'date_of_birth' => '2023-01-01',
        'status' => 'altara',
        'nationality' => 'nigerian',
        'next_of_kin_name' => 'altara',
        'next_of_kin_phone_no' => 'altara',
        'portal_access' => 1,
     ];

    /**
     * @throws \Exception
     */
    public static function getNextModelId()
    {
        $self = new static();
        if (!$self->getIncrementing()) {
            throw new \Exception(sprintf('Model (%s) is not auto-incremented', static::class));
        }
        $tableName = $self->getTable();
        $id = DB::select("SHOW TABLE STATUS LIKE '{$tableName}'");
        return $id[0]->Auto_increment;
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'owner_id', 'id');
    }
}
