<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Document;
use App\Models\Result;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Teacher;
use App\Models\User;
use App\Policies\AnnouncementPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\ResultPolicy;
use App\Policies\StudentFeePolicy;
use App\Policies\StudentPolicy;
use App\Policies\TeacherPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Student::class => StudentPolicy::class,
        Teacher::class => TeacherPolicy::class,
        Result::class => ResultPolicy::class,
        StudentFee::class => StudentFeePolicy::class,
        Announcement::class => AnnouncementPolicy::class,
        Document::class => DocumentPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function (User $user, string $ability) {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
