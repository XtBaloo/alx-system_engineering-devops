<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Payment;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use App\Notifications\AnnouncementPosted;
use App\Notifications\PaymentReceived;
use App\Notifications\ResultPublished;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NotificationDispatcher
{
    public function resultPublished(Result $result): void
    {
        $result->loadMissing('student.user', 'student.guardians.user');

        $this->send($this->studentAndGuardianUsers($result->student), new ResultPublished($result));
    }

    public function paymentReceived(Payment $payment): void
    {
        $payment->loadMissing('studentFee.student.user', 'studentFee.student.guardians.user');

        $this->send($this->studentAndGuardianUsers($payment->studentFee->student), new PaymentReceived($payment));
    }

    public function announcementPublished(Announcement $announcement): void
    {
        $this->send($this->audienceUsers($announcement), new AnnouncementPosted($announcement));
    }

    protected function studentAndGuardianUsers(?Student $student): Collection
    {
        if (! $student) {
            return collect();
        }

        $users = collect([$student->user])
            ->merge($student->guardians->pluck('user'))
            ->filter();

        return $users->unique('id');
    }

    protected function audienceUsers(Announcement $announcement): Collection
    {
        return match ($announcement->target) {
            'everyone' => User::where('is_active', true)->get(),
            'teachers' => User::role('teacher')->where('is_active', true)->get(),
            'students' => User::role('student')->where('is_active', true)->get(),
            'parents' => User::role('parent')->where('is_active', true)->get(),
            'class' => $this->classAudienceUsers($announcement->school_class_id),
            default => collect(),
        };
    }

    protected function classAudienceUsers(?int $schoolClassId): Collection
    {
        if (! $schoolClassId) {
            return collect();
        }

        $students = Student::with('user', 'guardians.user')
            ->whereHas('currentClassArm', fn ($q) => $q->where('school_class_id', $schoolClassId))
            ->get();

        return $students->reduce(
            fn (Collection $users, Student $student) => $users->merge($this->studentAndGuardianUsers($student)),
            collect()
        )->unique('id');
    }

    protected function send(Collection $users, Notification $notification): void
    {
        if ($users->isEmpty()) {
            return;
        }

        NotificationFacade::send($users, $notification);
    }
}
