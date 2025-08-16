<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Badge;
use App\Models\Book;
use App\Models\ChallengeBook;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\Forum;
use App\Models\Mention;
use App\Models\Notification;
use App\Models\ReadingChallenge;
use App\Models\Thread;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserChallengeBook;
use App\Models\UserFollow;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Safely create admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@libiverse.com'],
            [
                'member_id' => 'BCL-ADMIN-001',
                'uuid' => Str::uuid(),
                'username' => 'admin_user',
                'password' => Hash::make('password'),
                'role' => UserRole::ADMIN->value,
                'date_of_birth' => '1980-01-01',
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );

        // Safely create moderator user if not exists
        $moderator = User::firstOrCreate(
            ['email' => 'moderator@libiverse.com'],
            [
                'member_id' => 'BCL-MOD-001',
                'uuid' => Str::uuid(),
                'username' => 'moderator_user',
                'password' => Hash::make('password'),
                'role' => UserRole::MODERATOR->value,
                'date_of_birth' => '1990-05-15',
            ]
        );

        // Create regular users (18 total = 20 users with admin/moderator)
        $users = User::factory(18)->create();
        $allUsers = $users->concat([$admin, $moderator]);

        // Create profiles for all users
        $allUsers->each(function ($user) {
            UserProfile::firstOrCreate(
                ['user_id' => $user->id],
                UserProfile::factory()->raw(['user_id' => $user->id])
            );
        });

        // Create books (most by regular users, some by moderator)
        $books = Book::factory(50)->create([
            'added_by' => fn() => rand(0, 4) === 0 ? $moderator->id : $users->random()->id
        ]);

        // Create forums (all by moderator)
        $forumCategories = ['Students', 'Professionals', 'EFL', 'BookClubs', 'Events', 'Studxents', 'Profesxsionals', 'ExFL', 'BooxkClubs', 'Exvents'];
        $forums = collect();

        foreach ($forumCategories as $category) {
            $forums->push(Forum::firstOrCreate(
                ['name' => $category . ' Forum'],
                [
                    'slug' => Str::slug($category),
                    'description' => fake()->sentence,
                    'category' => $category,
                    'is_public' => true,
                    'created_by' => $moderator->id,
                    'book_id' => $books->random()->id
                ]
            ));
        }


        // Create threads (in all forums by all users)
        $threads = Thread::factory(100)->create([
            'forum_id' => fn() => $forums->random()->id,
            'user_id' => fn() => $allUsers->random()->id,
            'book_id' => fn() => $books->random()->id
        ]);

        // Create events (all by moderator)
        $events = Event::factory(15)->create([
            'created_by' => $moderator->id,
            'forum_id' => $forums->where('category', 'Events')->first()->id
        ]);

        // Create event RSVPs
        foreach ($events as $event) {
            $attendees = $allUsers->random(rand(5, 15));

            foreach ($attendees as $user) {
                EventRsvp::firstOrCreate(
                    ['user_id' => $user->id, 'event_id' => $event->id],
                    [
                        'attendance_type' => rand(0, 1) ? 'In-person' : 'Virtual',
                        'status' => ['going', 'interested', 'not_going'][rand(0, 2)]
                    ]
                );
            }
        }

        // Create badges
        $badges = Badge::factory(20)->create();

        // Create reading challenges (all by moderator)
        $challenges = ReadingChallenge::factory(5)->create([
            'created_by' => $moderator->id,
            'badge_id' => fn() => $badges->random()->id
        ]);

        // Add books to challenges
        foreach ($challenges as $challenge) {
            $challengeBooks = $books->random(rand(5, 10));

            foreach ($challengeBooks as $book) {
                ChallengeBook::firstOrCreate(
                    ['challenge_id' => $challenge->id, 'book_id' => $book->id],
                    ['added_by' => $moderator->id]
                );
            }

            // Add participants to challenges
            $participants = $allUsers->random(rand(10, 15));

            foreach ($participants as $user) {
                $userBooks = $challengeBooks->random(rand(1, 3));

                foreach ($userBooks as $book) {
                    $statuses = ['planned', 'reading', 'completed'];
                    $status = $statuses[array_rand($statuses)];

                    $startedAt = null;
                    $completedAt = null;
                    $userRating = null;
                    $review = null;

                    if ($status !== 'planned') {
                        $startedAt = now()->subDays(rand(1, 30));
                    }

                    if ($status === 'completed') {
                        $completedAt = now();
                        $userRating = rand(1, 5);
                        $review = fake()->paragraph();
                    }

                    UserChallengeBook::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'challenge_id' => $challenge->id,
                            'book_id' => $book->id
                        ],
                        [
                            'status' => $status,
                            'started_at' => $startedAt,
                            'completed_at' => $completedAt,
                            'user_rating' => $userRating,
                            'review' => $review
                        ]
                    );
                }
            }
        }

        // Assign badges to users
        foreach ($allUsers as $user) {
            $userBadges = $badges->random(rand(1, 3));

            foreach ($userBadges as $badge) {
                UserBadge::firstOrCreate(
                    ['user_id' => $user->id, 'badge_id' => $badge->id],
                    [
                        'earned_at' => now()->subDays(rand(1, 100)),
                        'challenge_id' => rand(0, 1) ? $challenges->random()->id : null
                    ]
                );
            }
        }

        // Create user follows
        foreach ($allUsers as $user) {
            $followCount = rand(3, 8);
            $toFollow = $allUsers->where('id', '!=', $user->id)->random($followCount);

            foreach ($toFollow as $followee) {
                UserFollow::firstOrCreate(
                    ['follower_id' => $user->id, 'followee_id' => $followee->id]
                );
            }
        }

        // Create notifications
        foreach ($allUsers as $user) {
            Notification::factory(rand(3, 7))->create(['user_id' => $user->id]);
        }

        // Create mentions
        foreach ($allUsers as $user) {
            Mention::factory(rand(2, 5))->create(['user_id' => $user->id]);
        }

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('👤 Admin user: admin@libiverse.com / password');
        $this->command->info('👤 Moderator user: moderator@libiverse.com / password');
        $this->command->info('👥 Regular users: ' . $users->count() . ' created (password: password)');
    }
}
