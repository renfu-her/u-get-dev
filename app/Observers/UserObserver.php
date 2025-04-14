<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Store;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // 為新用戶創建一個默認商店
        Store::create([
            'user_id' => $user->id,
            'name' => $user->name . '的商店',
            'slug' => Str::slug($user->name . '-store'),
            'description' => '歡迎來到' . $user->name . '的商店！',
            'is_active' => true,
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}