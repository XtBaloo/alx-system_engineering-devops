<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::role('super-admin')->first() ?? User::first();

        $announcements = [
            [
                'title' => 'Resumption Date for First Term',
                'message' => "All students are to resume on Monday, 8th September 2026. Please ensure all fees are paid before resumption.",
                'target' => 'everyone',
            ],
            [
                'title' => 'Staff Meeting',
                'message' => 'There will be a mandatory staff meeting on Friday at 2:00 PM in the staff room.',
                'target' => 'teachers',
            ],
            [
                'title' => 'Inter-House Sports',
                'message' => 'This term\'s inter-house sports competition holds on the last Friday of the term. All students should come with their house colours.',
                'target' => 'students',
            ],
            [
                'title' => 'PTA Meeting Notice',
                'message' => 'The termly Parent-Teacher Association meeting will hold on Saturday, 10:00 AM in the school hall.',
                'target' => 'parents',
            ],
        ];

        foreach ($announcements as $a) {
            Announcement::updateOrCreate(['title' => $a['title']], $a + [
                'author_id' => $author->id,
                'status' => 'published',
                'published_at' => now()->subDays(2),
            ]);
        }
    }
}
