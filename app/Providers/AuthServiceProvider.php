<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Bookmark;
use App\Models\Story;
use App\Models\User;
use App\Policies\BookmarkPolicy;
use App\Policies\StoryPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Bookmark::class => BookmarkPolicy::class,
        Story::class => StoryPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
