<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Representation of a persisted session row when using the database driver.
 *
 * Uses a string primary key, no timestamps, and resolves the table name from
 * configuration (session.table) to mirror Laravel's database session storage.
 */
class Session extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Dynamically resolve the table name from configuration (falls back to default naming).
     *
     * @return string The session table name as defined by config('session.table') or the model's default.
     */
    public function getTable(): string
    {
        return (string) config('session.table', parent::getTable());
    }
}
