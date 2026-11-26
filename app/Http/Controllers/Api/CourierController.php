<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourierRequest;
use App\Http\Requests\UpdateCourierRequest;
use App\Http\Resources\CourierResource;
use App\Models\Courier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourierController extends Controller
{
    /**
     * Display a paginated listing of couriers with search, level filter, and sorting.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        [$sortColumn, $sortDirection] = $this->resolveSort($request);
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        $couriers = Courier::query()
            ->search($request->query('search'))
            ->filterLevels($request->query('level'))
            ->orderBy($sortColumn, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return CourierResource::collection($couriers);
    }

    /**
     * Store a newly created courier in storage.
     */
    public function store(StoreCourierRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['registered_at'] ??= now();
        $data['is_active'] ??= true;

        $courier = Courier::create($data);

        return (new CourierResource($courier))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified courier.
     */
    public function show(Courier $courier): CourierResource
    {
        return new CourierResource($courier);
    }

    /**
     * Update the specified courier in storage.
     */
    public function update(UpdateCourierRequest $request, Courier $courier): CourierResource
    {
        $courier->update($request->validated());

        return new CourierResource($courier->fresh());
    }

    /**
     * Remove the specified courier from storage.
     */
    public function destroy(Courier $courier): JsonResponse
    {
        $courier->delete();

        return response()->json([
            'message' => 'Courier deleted successfully.',
        ]);
    }

    /**
     * Resolve sort column and direction; defaults to name ascending.
     *
     * @return array{0: string, 1: string}
     */
    private function resolveSort(Request $request): array
    {
        if ($request->filled('sort_by')) {
            $column = $request->query('sort_by') === 'registered_at' ? 'registered_at' : 'name';
            $direction = strtolower((string) $request->query('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

            return [$column, $direction];
        }

        if ($request->filled('sort')) {
            $rawSort = (string) $request->query('sort');
            $isPrefixedDesc = str_starts_with($rawSort, '-');
            $field = ltrim($rawSort, '-');

            $column = $field === 'registered_at' ? 'registered_at' : 'name';
            $direction = ($isPrefixedDesc || strtolower((string) $request->query('sort_dir', 'asc')) === 'desc')
                ? 'desc'
                : 'asc';

            return [$column, $direction];
        }

        return ['name', 'asc'];
    }
}
