<?php

namespace App\Policies;

use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class BookmarkPolicy
{
    use HandlesAuthorization;
    
    public function create(User $user): bool
    {
        return $user->id !== null;
    }

    public function viewList(User $user, User $profileUser): bool
    {
        // Users can only view their own bookmarks
        return $user->id === $profileUser->id;
    }

    
}
