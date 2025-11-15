<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Return a successful JSON response.
     *
     * @param mixed $data The data to be returned in the response.
     * @param string $message The success message.
     * @param int $status The HTTP status code.
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = null, $message = 'Success', $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    /**
     * Return an error JSON response.
     *
     * @param mixed $data The data to be returned in the response.
     * @param string $message The error message.
     * @param int $status The HTTP status code.
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($data = null, $message = 'Operation failed!', $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    /**
     * Generates a JSON response with paginated data.
     *
     * Transforms the paginated items using the provided resource class and
     * returns the transformed data along with pagination information.
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator The paginator instance containing the items.
     * @param string $resourceClass The resource class used to transform the paginated items.
     * @param string $message Optional message to be included in the response.
     * @param int $status HTTP status code.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function paginated($paginator, $resourceClass = null, $message = 'Success', $status = 200): \Illuminate\Http\JsonResponse
    {
        $transformedItems = is_null($resourceClass) ? $paginator->items() : $resourceClass::collection($paginator->items());

        return response()->json([
            'success' => true,
            'data' => $transformedItems,
            'message' => $message,
            'pagination' => [
                'total' => $paginator->total(),
                'count' => $paginator->count(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ],
        ], $status);
    }
}
