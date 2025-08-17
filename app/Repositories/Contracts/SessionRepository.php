<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Contract for managing user session records.
 *
 * Provides methods to list sessions, delete specific or multiple sessions,
 * and update metadata such as IP and user agent.
 */
interface SessionRepository
{
    /**
     * List sessions for the given user with a flag for the current session.
     *
     * @param  User  $user  The user whose sessions will be listed.
     * @param  string  $currentId  The current session ID to mark as is_current.
     * @return Collection<int, array{id:string, ip_address:?string, user_agent:?string, last_activity:int, is_current:bool}>
     */
    public function listForUser(User $user, string $currentId): Collection;

    /**
     * Delete a session by ID for the given user.
     *
     * @param  string  $id  Session identifier.
     * @param  User  $user  Owner of the session.
     * @return int Number of deleted rows.
     */
    public function deleteByIdForUser(string $id, User $user): int;

    /**
     * Delete all sessions for the given user except the provided session ID.
     *
     * @param  User  $user  User whose sessions should be deleted.
     * @param  string  $exceptId  Keep this session ID intact.
     * @return int Number of deleted rows.
     */
    public function deleteOthersForUserExcept(User $user, string $exceptId): int;

    /**
     * Delete all sessions for the given user.
     *
     * @param  User  $user  User whose sessions should be deleted.
     * @return int Number of deleted rows.
     */
    public function deleteAllForUser(User $user): int;

    /**
     * Update session metadata such as user, IP, and user agent.
     *
     * @param  string  $sessionId  The session ID to update.
     * @param  User|null  $user  The authenticated user (nullable for guest sessions).
     * @param  string  $ip  IP address associated with the request.
     * @param  string  $userAgent  User agent string.
     * @return int Number of affected rows.
     */
    public function updateMetadata(string $sessionId, ?User $user, string $ip, string $userAgent): int;
}
