<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ListFilters
{
    public static function perPage(Request $request, int $default = 25): int
    {
        $perPage = (int) $request->get('per_page', $default);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : $default;
    }

    public static function paginate($query, Request $request, int $defaultPerPage = 25): LengthAwarePaginator
    {
        return $query->paginate(self::perPage($request, $defaultPerPage))->withQueryString();
    }
}
