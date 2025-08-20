<?php

namespace Modules\Post\App\Contracts;

use App\Models\Post;
use App\Models\Thread;
use Illuminate\Pagination\LengthAwarePaginator;

interface PostServiceInterface
{
    /**
     * Create a new post in a thread.
     *
     * @param Thread $thread The thread to which the post belongs.
     * @param array $data Post data (content, parent_post_id, book_id).
     * @return Post The created post.
     */
    public function create(Thread $thread, array $data): Post;

    /**
     * Fetch posts for a thread with pagination and optional filters.
     *
     * @param Thread $thread The thread to fetch posts from.
     * @param array $filters Optional filters (search, is_flagged).
     * @param int $perPage Number of posts per page.
     * @return LengthAwarePaginator Paginated posts.
     */
    public function getByThread(Thread $thread, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Fetch a post by its UUID.
     *
     * @param string $id The UUID of the post.
     * @return Post|null The post if found, null otherwise.
     */
    public function find(string $id): ?Post;

    /**
     * Update a post.
     *
     * @param Post $post The post to update.
     * @param array $data Updated post data.
     * @return Post The updated post.
     */
    public function update(Post $post, array $data): Post;

    /**
     * Delete a post.
     *
     * @param Post $post The post to delete.
     * @return bool True if deleted successfully.
     */
    public function delete(Post $post): bool;

    /**
     * Toggle like status for a post.
     *
     * @param Post $post The post to like/unlike.
     * @param bool $like True to like, false to unlike.
     * @return bool True if action succeeded.
     */
    public function toggleLike(Post $post, bool $like): bool;

    /**
     * Toggle save status for a post.
     *
     * @param Post $post The post to save/unsave.
     * @param bool $save True to save, false to unsave.
     * @return bool True if action succeeded.
     */
    public function toggleSave(Post $post, bool $save): bool;

    /**
     * Create a comment (nested reply) on a post.
     *
     * @param Post $post The parent post.
     * @param array $data Comment data (content, etc.).
     * @return Post The created comment post.
     */
    public function createComment(Post $post, array $data): Post;

    /**
     * Toggle flag status for a post.
     *
     * @param Post $post The post to flag/unflag.
     * @return bool True if action succeeded.
     */
    public function toggleFlag(Post $post): bool;

    /**
     * Upload media for a post.
     *
     * @param Post $post The post to attach media to.
     * @param array $data Media data (file, caption, thumbnail_url).
     * @return array The created media data.
     */
    public function uploadMedia(Post $post, array $data): array;
}
