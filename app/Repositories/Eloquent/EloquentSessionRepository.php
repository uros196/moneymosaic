<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\SessionRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Repository for interacting with session records using the configured model.
 */
class EloquentSessionRepository implements SessionRepository
{
    /**
     * @param  string  $model  FQN of the session model; defaults to config('session.model').
     */
    public function __construct(protected string $model = '')
    {
        $this->model = $model ?: (string) config('session.model');
    }

    /**
     * Base query builder for the configured session model.
     *
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function query(): Builder
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $cls */
        $cls = $this->model;

        return $cls::query();
    }

    /**
     * List sessions for the given user with current-session flag.
     *
     * @param  User  $user  The user whose sessions will be listed.
     * @param  string  $currentId  The current session ID to mark as is_current.
     * @return Collection<int, array{id:string, ip_address:?string, user_agent:?string, last_activity:int, is_current:bool}>
     */
    public function listForUser(User $user, string $currentId): Collection
    {
        return $this->query()
            ->select(['id', 'ip_address', 'user_agent', 'last_activity'])
            ->where('user_id', $user->getKey())
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($row) use ($currentId) {
                return [
                    'id' => (string) $row->id,
                    'ip_address' => $row->ip_address,
                    'user_agent' => $row->user_agent,
                    'last_activity' => (int) $row->last_activity,
                    'is_current' => (string) $row->id === $currentId,
                ];
            });
    }

    /**
     * Delete a session by ID for the given user.
     *
     * @param  string  $id  Session identifier.
     * @param  User  $user  Owner of the session.
     * @return int Number of deleted rows.
     */
    public function deleteByIdForUser(string $id, User $user): int
    {
        return $this->query()
            ->where('id', $id)
            ->where('user_id', $user->getKey())
            ->delete();
    }

    /**
     * Delete all sessions for the given user except the provided session ID.
     *
     * @param  User  $user  User whose sessions should be deleted.
     * @param  string  $exceptId  Keep this session ID intact.
     * @return int Number of deleted rows.
     */
    public function deleteOthersForUserExcept(User $user, string $exceptId): int
    {
        return $this->query()
            ->where('user_id', $user->getKey())
            ->where('id', '!=', $exceptId)
            ->delete();
    }

    /**
     * Delete all sessions for the given user.
     *
     * @param  User  $user  User whose sessions should be deleted.
     * @return int Number of deleted rows.
     */
    public function deleteAllForUser(User $user): int
    {
        return $this->query()
            ->where('user_id', $user->getKey())
            ->delete();
    }

    /**
     * Update session metadata such as user, IP, and user agent.
     *
     * @param  string  $sessionId  The session ID to update.
     * @param  User|null  $user  The authenticated user (nullable for guest sessions).
     * @param  string  $ip  IP address associated with the request.
     * @param  string  $userAgent  User agent string.
     * @return int Number of affected rows.
     */
    public function updateMetadata(string $sessionId, ?User $user, string $ip, string $userAgent): int
    {
        return $this->query()
            ->where('id', $sessionId)
            ->update([
                'user_id' => $user?->getKey(),
                'ip_address' => $ip,
                'user_agent' => $userAgent,
            ]);
    }
}
