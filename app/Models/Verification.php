<?php

namespace App\Models;

class Verification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone', 'phone_code', 'phone_created_at', 'phone_code_generated_at', 'phone_verified_at',
        'email', 'email_code', 'email_created_at', 'email_code_generated_at', 'email_verified_at',
        'lead_id', 'customer_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'phone_created_at' => 'datetime',
        'phone_code_generated_at' => 'datetime',
        'phone_verified_at' => 'datetime',

        'email_created_at' => 'datetime',
        'email_code_generated_at' => 'datetime',
        'email_verified_at' => 'datetime',

        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
