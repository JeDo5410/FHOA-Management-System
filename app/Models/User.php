<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    // Role constants
    public const ROLE_ADMIN = 1;
    public const ROLE_EDITOR = 2;
    public const ROLE_VIEWER = 3;

    protected $fillable = [
        'fullname',
        'username',
        'password',
        'role',
        'is_active',
        'designation',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Role checking methods
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isEditor()
    {
        return $this->role === self::ROLE_EDITOR;
    }

    public function isViewer()
    {
        return $this->role === self::ROLE_VIEWER;
    }

    public function hasAccess($roles)
    {
        return in_array($this->role, (array) $roles);
    }
}