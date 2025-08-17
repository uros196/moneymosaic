<?php

namespace App\Http\Middleware;

use App\Repositories\Contracts\SessionRepository as SessionRepositoryContract;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that updates session-related metadata on each request.
 *
 * Responsibilities:
 * - Refreshes last interaction timestamp used for inactivity checks.
 * - When using the database session driver, stores IP and user agent via repository.
 */
class UpdateSessionMetadata
{
    /**
     * @param  SessionRepositoryContract  $sessions  Repository for persisting session metadata.
     */
    public function __construct(public SessionRepositoryContract $sessions)
    {
        //
    }

    /**
     * Update session metadata and refresh last interaction timestamp.
     *
     * @param  Request  $request  Current HTTP request.
     * @param  Closure(Request):Response  $next  Next middleware/controller.
     * @return Response Response after metadata update.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (config('session.driver') === 'database') {
            $sessionId = $request->session()->getId();

            if ($sessionId) {
                $this->sessions->updateMetadata(
                    $sessionId,
                    $request->user(),
                    (string) $request->ip(),
                    (string) $request->userAgent()
                );
            }
        }

        return $response;
    }
}
