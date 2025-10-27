<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\UserNotify;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, UserNotify;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'ver_code',
        'balance',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'ver_code_send_at'  => 'datetime',
    ];

    public function loginLogs()
    {
        return $this->hasMany(UserLogin::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->orderBy('id', 'desc');
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class)->where('status', '!=', Status::PAYMENT_INITIATE);
    }

    public function tickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function pickedTickets()
    {
        return $this->hasMany(PickedTicket::class);
    }

    public function winningTickets()
    {
        return $this->hasMany(Winner::class);
    }

    public function fullname(): Attribute
    {
        return new Attribute(
            get: fn() => $this->firstname . ' ' . $this->lastname,
        );
    }

    public function mobileNumber(): Attribute
    {
        return new Attribute(
            get: fn() => $this->dial_code . $this->mobile,
        );
    }
    // SCOPES
    public function scopeActive($query)
    {
        return $query->where('status', Status::USER_ACTIVE)->where('ev', Status::VERIFIED)->where('sv', Status::VERIFIED);
    }

    public function scopeBanned($query)
    {
        return $query->where('status', Status::USER_BAN);
    }

    public function scopeEmailUnverified($query)
    {
        return $query->where('ev', Status::UNVERIFIED);
    }

    public function scopeMobileUnverified($query)
    {
        return $query->where('sv', Status::UNVERIFIED);
    }

    public function scopeEmailVerified($query)
    {
        return $query->where('ev', Status::VERIFIED);
    }

    public function scopeMobileVerified($query)
    {
        return $query->where('sv', Status::VERIFIED);
    }

    public function scopeWithBalance($query)
    {
        return $query->where('balance', '>', 0);
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'user_tasks')
            ->withPivot(['status', 'proof', 'verified_at'])
            ->withTimestamps();
    }

    /**
     * Tambah poin ke user + log transaksi.
     */
    public function addPoints(int $amount, string $description = '')
    {
        $this->increment('points', $amount);
        $this->pointTransactions()->create([
            'type' => 'earn',
            'amount' => $amount,
            'description' => $description,
        ]);
    }

    /**
     * Kurangi poin + log transaksi.
     */
    public function spendPoints(int $amount, string $description = '')
    {
        if ($this->points < $amount) {
            throw new \Exception('Poin tidak cukup');
        }

        $this->decrement('points', $amount);
        $this->pointTransactions()->create([
            'type' => 'spend',
            'amount' => $amount,
            'description' => $description,
        ]);
    }
}
