<?php

namespace App\Models\Core;

use App\Models\MainModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use App\Models\Wms\Warehouse;
use Spatie\Permission\Traits\HasRoles;

class User extends MainModel implements AuthenticatableContract,AuthorizableContract,CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $table = "core_users";
    protected $primaryKey = 'id';
    protected $guarded = ['id','uuid'];


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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function setPasswordAttribute($value) {
        $this->attributes['password'] = Hash::make($value);
    }

    public function enableUserTwoFactor($userID, $secretKey) {
        //user model must be explicitly called prior calling the enable function
        if (!$this->find($userID)) return 'User ID specified not valid!';
        if (!$secretKey) return 'Secret key not specified';

        $this->userUse2FactorAuthentication = 1;
        $this->user2FactorAuthenticationPrivateKey = $secretKey;
        return $this->save();
    }

    public function disableUserTwoFactor($userID) {
        //user model must be explicitly called prior calling the enable function
        if (!$this->find($userID)) return 'User ID specified not valid!';
        $this->userUse2FactorAuthentication = 0;
        $this->user2FactorAuthenticationPrivateKey = '';
        return $this->save();
    }

     # Below methods were Set of Flags in users model
    # When parameter is set to true, the attribute will be modified to value representing true
    # otherwise return the current attribute value
    public function isTwoFactorEnabled(bool $setValueToTrue = false) {
        $this->userUse2FactorAuthentication = $setValueToTrue ? 1 : $this->userUse2FactorAuthentication ;
        return $this->userUse2FactorAuthentication == 1;
    }

    public function braches() {
        return $this->hasOne(CoreBranch::class, 'id', 'branch_id');
    }

    /**
     * Get the role that belongs to the user
     */
    public function role() {
        return $this->belongsTo(Role::class, 'member_role', 'id');
    }

    /**
     * Get the branch that belongs to the user
     */
    public function branch() {
        return $this->belongsTo(CoreBranch::class, 'branch_id', 'id');
    }


}
