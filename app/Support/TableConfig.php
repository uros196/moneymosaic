<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Helper for table-related configuration (pagination, etc.).
 */
final class TableConfig
{
    /**
     * Get the paging configuration for a given table key.
     */
    public static function paging(?string $table = null): array
    {
        $table ??= 'defaults';

        $tableConfig = config("tables.$table.per_page") ?? config('tables.defaults.per_page');

        $options = array_values(array_unique(array_map('intval', $tableConfig['options'])));
        sort($options);

        return array_merge($tableConfig, [
            'options' => $options,
        ]);
    }

    /**
     * Get the per-page configuration for a given table key.
     */
    public static function defaultPerPage(?string $table = null): int
    {
        return data_get(self::paging($table), 'default');
    }

    /**
     * Resolve the per-page value from the request ensuring it matches configured options.
     */
    public static function resolvePerPage(Request $request, ?string $table = null): int
    {
        $cfg = self::paging($table);
        $requested = $request->integer('perPage', $cfg['default']);

        return in_array($requested, $cfg['options'], true) ? $requested : $cfg['default'];
    }

    /**
     * Get the complete paging configuration data including resolved per-page value from the request.
     */
    public static function pagingData(Request $request, ?string $table = null): array
    {
        return array_merge(self::paging($table), [
            'perPage' => self::resolvePerPage($request, $table),
        ]);
    }
}
