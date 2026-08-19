<?php

namespace App\Providers;

use App\Models\AgencyInvitation;
use App\Models\OwnerDocument;
use App\Models\User;
use App\Policies\DocumentPolicy;
use App\Policies\InvitationPolicy;
use App\Policies\MemberPolicy;
use App\Policies\RolePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => MemberPolicy::class,
        Role::class => RolePolicy::class,
        AgencyInvitation::class => InvitationPolicy::class,
        OwnerDocument::class => DocumentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Vérification supplémentaire
        \Log::info('AuthServiceProvider booted', [
            'policies' => array_keys($this->policies),
        ]);
    }
}
