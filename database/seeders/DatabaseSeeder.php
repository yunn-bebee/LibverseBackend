<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Badge;
use App\Models\Book;
use App\Models\ChallengeBook;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\Forum;
use App\Models\Media;
use App\Models\Notification;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostReport;
use App\Models\PostSave;
use App\Models\ReadingChallenge;
use App\Models\Thread;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserChallengeBook;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Create necessary public storage directories
     */
    private function createPublicDirectories()
    {
        $directories = [
            'badges',
            'profiles',
            'media/images',
            'media/videos',
            'media/documents',
            'books/covers',
            'events',
            'preloaded/books',
            'preloaded/profiles',
            'preloaded/badges',
            'preloaded/events',
            'preloaded/media/images',
            'preloaded/media/documents',
        ];

        foreach ($directories as $directory) {
            $path = public_path("storage/{$directory}");
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
                $this->command->info("Created directory: storage/{$directory}");
            }
        }
    }

    /**
     * Copy preloaded images to their respective directories
     */
    private function setupPreloadedImages()
    {
        $types = [
            'books' => ['extension' => 'jpg', 'target' => 'books/covers', 'count' => 10],
            'profiles' => ['extension' => 'jpg', 'target' => 'profiles', 'count' => 10],
            'badges' => ['extension' => 'png', 'target' => 'badges', 'count' => 5],
            'events' => ['extension' => 'jpg', 'target' => 'events', 'count' => 10],
            'media/images' => ['extension' => 'jpg', 'target' => 'media/images', 'count' => 10],
        ];

        foreach ($types as $type => $config) {
            for ($i = 1; $i <= $config['count']; $i++) {
                $source = public_path("storage/preloaded/{$type}/{$i}.{$config['extension']}");
                $destination = public_path("storage/{$config['target']}/{$i}.{$config['extension']}");

                if (File::exists($source) && !File::exists($destination)) {
                    File::copy($source, $destination);
                    $this->command->info("Copied {$type} image: {$i}.{$config['extension']}");
                }
            }
        }

        // Copy document files
        for ($i = 1; $i <= 5; $i++) {
            $source = public_path("storage/preloaded/media/documents/{$i}.pdf");
            $destination = public_path("storage/media/documents/{$i}.pdf");

            if (File::exists($source) && !File::exists($destination)) {
                File::copy($source, $destination);
                $this->command->info("Copied document: {$i}.pdf");
            }
        }
    }

    /**
     * Get random image path for different types
     */
    private function getRandomImage($type)
    {
        $counts = [
            'books' => 10,
            'profiles' => 10,
            'badges' => 5,
            'events' => 10,
            'media' => 10
        ];

        $number = rand(1, $counts[$type] ?? 5);

        if ($type === 'books') {
            return "books/covers/{$number}.jpg";
        }

        if ($type === 'media') {
            return "media/images/{$number}.jpg";
        }

        if ($type === 'badges') {
            return "badges/{$number}.png";
        }

        return "{$type}/{$number}.jpg";
    }

    public function run()
    {
        // Create necessary directories first
        $this->createPublicDirectories();

        // Copy preloaded images
        $this->setupPreloadedImages();

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@libiverse.com'],
            [
                'member_id' => 'LIB-ADMIN-001',
                'uuid' => Str::uuid(),
                'username' => 'libiverse_admin',
                'password' => Hash::make('password'),
                'role' => UserRole::ADMIN->value,
                'date_of_birth' => '1985-01-01',
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );

        // Create moderator user
        $moderator = User::firstOrCreate(
            ['email' => 'moderator@libiverse.com'],
            [
                'member_id' => 'LIB-MOD-001',
                'uuid' => Str::uuid(),
                'username' => 'libiverse_moderator',
                'password' => Hash::make('password'),
                'role' => UserRole::MODERATOR->value,
                'date_of_birth' => '1990-06-15',
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );

        // Create your specific member user
        $specificMember = User::firstOrCreate(
            ['email' => 'yunn.beebee@gmail.com'],
            [
                'member_id' => 'AYA232323',
                'uuid' => Str::uuid(),
                'username' => 'libiverse_member',
                'password' => Hash::make('password'),
                'role' => UserRole::MEMBER->value,
                'date_of_birth' => '1995-03-20',
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );

        // Create additional regular members (17 more to make 20 total users including admin, moderator, and specific member)
        $additionalMembers = User::factory(17)->create([
            'role' => UserRole::MEMBER->value,
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);

        // All users collection
        $allUsers = collect([$admin, $moderator, $specificMember])->concat($additionalMembers);

        // Create profiles for all users with real images
        $allUsers->each(function ($user) {
            UserProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'bio' => fake()->paragraph(),
                    'profile_picture' => $this->getRandomImage('profiles'),
                    'website' => fake()->url(),
                    'location' => fake()->city(),
                    'last_active' => now(),
                ]
            );
        });

        // Create Libiverse-themed books (50 books, added by random users including moderator) with real cover images
        $books = Book::factory(50)->create([
            'added_by' => fn() => rand(0, 4) === 0 ? $moderator->id : $allUsers->random()->id,
            'title' => fn() => fake()->randomElement([
                'The Great Gatsby', 'To Kill a Mockingbird', '1984', 'Pride and Prejudice', 'The Catcher in the Rye',
                'Harry Potter and the Philosopher\'s Stone', 'The Hobbit', 'Fahrenheit 451', 'Jane Eyre', 'Moby Dick',
                'War and Peace', 'The Odyssey', 'Crime and Punishment', 'The Brothers Karamazov', 'Anna Karenina',
                'Les Misérables', 'Don Quixote', 'The Divine Comedy', 'One Hundred Years of Solitude', 'The Alchemist',
                'Sapiens: A Brief History of Humankind', 'Educated', 'Becoming', 'The Power of Habit', 'Atomic Habits',
                'Thinking, Fast and Slow', 'The 7 Habits of Highly Effective People', 'How to Win Friends and Influence People',
                'Man\'s Search for Meaning', 'The Subtle Art of Not Giving a F*ck',
            ]),
            'author' => fn() => fake()->name(),
            'genres' => json_encode(fake()->randomElements(['Fiction', 'Non-Fiction', 'Sci-Fi', 'Fantasy', 'Mystery', 'Romance', 'History'], rand(1, 3))),
            'cover_image' => $this->getRandomImage('books'),
        ]);

        // Create Libiverse-themed forums (20 forums, categories relevant to library discussions, created by moderator)
        $forumCategories = [
            'Book Discussions', 'Reading Challenges', 'Author Spotlights', 'Genre Explorations', 'Library Events',
            'British Literature', 'World Classics', 'Young Adult', 'Children\'s Books', 'Non-Fiction Corner',
            'Poetry and Prose', 'Sci-Fi and Fantasy', 'Mystery and Thriller', 'Romance Readers', 'Historical Fiction',
            'Self-Help and Development', 'Science and Technology', 'Writing Workshops', 'Book Reviews', 'Member Recommendations'
        ];
        $forums = collect();
        foreach ($forumCategories as $category) {
            $forums->push(Forum::firstOrCreate(
                ['name' => $category],
                [
                    'slug' => Str::slug($category),
                    'description' => "A forum for discussing {$category} in the British Council Library community.",
                    'category' => $category,
                    'is_public' => rand(0, 1) ? true : false,
                    'created_by' => $moderator->id,
                    'book_id' => $books->random()->id,
                ]
            ));
        }

        // Create threads (200 threads across forums, created by random users)
        $threads = Thread::factory(200)->create([
            'forum_id' => fn() => $forums->random()->id,
            'user_id' => fn() => $allUsers->random()->id,
            'book_id' => fn() => $books->random()->id,
            'title' => fn() => fake()->sentence(5),
            'content' => fake()->paragraphs(3, true),
            'post_type' => fake()->randomElement(['discussion', 'question', 'announcement']),
        ]);

        // Create posts (5-15 top-level posts per thread, with 2-5 replies for half of them)
        foreach ($threads as $thread) {
            $postCount = rand(5, 15);
            $posts = Post::factory($postCount)->create([
                'thread_id' => $thread->id,
                'user_id' => fn() => $allUsers->random()->id,
                'book_id' => $books->random()->id,
                'content' => fake()->paragraph(),
                'is_flagged' => rand(0, 9) === 0, // 10% flagged
            ]);

            // Add replies to half the threads
            if (rand(0, 1)) {
                $replyCount = rand(2, 5);
                Post::factory($replyCount)->create([
                    'thread_id' => $thread->id,
                    'user_id' => fn() => $allUsers->random()->id,
                    'book_id' => $books->random()->id,
                    'parent_post_id' => fn() => $posts->random()->id,
                    'content' => fake()->paragraph(),
                    'is_flagged' => rand(0, 9) === 0,
                ]);
            }
        }

        // Create comments (for posts, 0-3 per post)
        $allPosts = Post::all();
        foreach ($allPosts->random(100) as $post) { // Add comments to 100 random posts
           Post::factory(rand(0, 3))->create([
                'parent_post_id' => $post->id,
                'thread_id' => $post->thread_id,
                'book_id' => $post->book_id,
                'user_id' => $allUsers->random()->id,
                'content' => fake()->paragraph(),
            ]);
        }

        // Create media (for random posts, 0-2 per post) with real images
        foreach ($allPosts->random(50) as $post) {
            $mediaCount = rand(0, 2);
            for ($i = 0; $i < $mediaCount; $i++) {
                $fileType = fake()->randomElement(['image', 'video', 'document']);

                if ($fileType === 'image') {
                    $filePath = $this->getRandomImage('media');
                } elseif ($fileType === 'document') {
                    $filePath = 'media/documents/' . rand(1, 5) . '.pdf';
                } else {
                    $filePath = 'media/videos/sample.mp4'; // Keep original for videos
                }

                Media::factory()->create([
                    'post_id' => $post->id,
                    'user_id' => $post->user_id,
                    'file_type' => $fileType,
                    'file_url' => $filePath,
                    'thumbnail_url' =>$filePath
                ]);
            }
        }

        // Create post likes and saves (random likes/saves for posts)
        foreach ($allPosts as $post) {
            $likers = $allUsers->random(rand(0, 10));
            foreach ($likers as $user) {
                PostLike::firstOrCreate(['user_id' => $user->id, 'post_id' => $post->id]);
            }

            $savers = $allUsers->random(rand(0, 5));
            foreach ($savers as $user) {
                PostSave::firstOrCreate(['user_id' => $user->id, 'post_id' => $post->id]);
            }
        }

        // Create post reports (for flagged posts)
        $flaggedPosts = Post::where('is_flagged', true)->get();
        foreach ($flaggedPosts as $post) {
            PostReport::factory(rand(1, 3))->create([
                'post_id' => $post->id,
                'user_id' => $allUsers->random()->id,
                'reviewed_by' => rand(0, 1) ? $moderator->id : null,
            ]);
        }

        // Create events (15 library events, created by moderator) with real images
        $events = Event::factory(15)->create([
            'created_by' => $moderator->id,
            'forum_id' => $forums->random()->id,
            'title' => fn() => fake()->randomElement([
                'Book Reading Session', 'Author Meetup', 'Library Workshop', 'Poetry Night', 'Sci-Fi Discussion',
            ]),
            'cover_image' => $this->getRandomImage('events'),
        ]);

        // Create RSVPs for events
        foreach ($events as $event) {
            $attendees = $allUsers->random(rand(5, 15));
            foreach ($attendees as $user) {
                EventRsvp::firstOrCreate(
                    ['user_id' => $user->id, 'event_id' => $event->id],
                    [
                        'attendance_type' => fake()->randomElement(['physical', 'virtual', 'hybrid']),
                        'status' => fake()->randomElement(['going', 'interested', 'not_going']),
                    ]
                );
            }
        }

        // Create badges (20 library-themed badges) with real images
        $badges = Badge::factory(20)->create([
            'name' => fn() => fake()->randomElement([
                'Bookworm', 'Challenge Master', 'Forum Contributor', 'Event Enthusiast', 'Review Guru',
            ]),
            'icon_url' => $this->getRandomImage('badges'),
        ]);

        // Create reading challenges (5 challenges, created by moderator)
        $challenges = ReadingChallenge::factory(5)->create([
            'created_by' => $moderator->id,
            'badge_id' => fn() => $badges->random()->id,
            'name' => fn() => fake()->randomElement([
                'Summer Reading Challenge', 'British Classics Marathon', 'Sci-Fi Exploration', 'Non-Fiction November', 'Poetry Month',
            ]),
        ]);

        // Add books to challenges and user progress
        foreach ($challenges as $challenge) {
            $challengeBooks = $books->random(rand(5, 10));
            foreach ($challengeBooks as $book) {
                ChallengeBook::firstOrCreate(
                    ['reading_challenge_id' => $challenge->id, 'book_id' => $book->id],
                    ['added_by' => $moderator->id]
                );
            }

            // Add participants and their book progress
            $participants = $allUsers->random(rand(10, 15));
            foreach ($participants as $user) {
                $userBooks = $challengeBooks->random(rand(1, $challengeBooks->count()));
                foreach ($userBooks as $book) {
                    UserChallengeBook::firstOrCreate(
                        ['user_id' => $user->id, 'challenge_id' => $challenge->id, 'book_id' => $book->id],
                        [
                            'status' => fake()->randomElement(['planned', 'reading', 'completed']),
                            'started_at' => now()->subDays(rand(1, 30)),
                            'completed_at' => rand(0, 1) ? now() : null,
                            'user_rating' => rand(1, 5),
                            'review' => fake()->paragraph(),
                        ]
                    );
                }
            }
        }

        // Assign badges to users
        foreach ($allUsers as $user) {
            $userBadges = $badges->random(rand(1, 5));
            foreach ($userBadges as $badge) {
                UserBadge::firstOrCreate(
                    ['user_id' => $user->id, 'badge_id' => $badge->id],
                    [
                        'earned_at' => now()->subDays(rand(1, 90)),
                        'challenge_id' => rand(0, 1) ? $challenges->random()->id : null,
                    ]
                );
            }
        }

        // Create user follows (each user follows 3-8 others)
        foreach ($allUsers as $user) {
            $toFollow = $allUsers->where('id', '!=', $user->id)->random(rand(3, 8));
            foreach ($toFollow as $followee) {
                $user->following()->attach($followee->id);
            }
        }

        // Create notifications (3-7 per user, Libiverse-themed)
        foreach ($allUsers as $user) {
            Notification::factory(rand(3, 7))->create([
                'user_id' => $user->id,
                'type' => fake()->randomElement(['like', 'comment', 'rsvp', 'mention', 'challenge_update']),
            ]);
        }

        $this->command->info('✅ Libiverse database seeded successfully with real images!');
        $this->command->info('👤 Admin: admin@libiverse.com / password');
        $this->command->info('👤 Moderator: moderator@libiverse.com / password');
        $this->command->info('👤 Specific Member: yunn.beebee@gmail.com / password');
        $this->command->info('👥 Additional Members: 17 created (password: password)');
        $this->command->info('📚 All images now use real preloaded images from storage/preloaded/');
    }
}
