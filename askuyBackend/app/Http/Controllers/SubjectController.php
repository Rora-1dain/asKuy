<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Subject::query();

        if ($request->filled('semester')) {
            $query->where('semester', (int) $request->semester);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%");
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $subjects = $query->orderBy('semester')->orderBy('name')->paginate($perPage);

        return SubjectResource::collection($subjects);
    }

    public function store(StoreSubjectRequest $request): JsonResponse
    {
        $subject = Subject::create($request->validated());

        return response()->json([
            'message' => 'Mata pelajaran berhasil ditambahkan.',
            'data' => new SubjectResource($subject),
        ], 201);
    }

    public function show(Subject $subject): JsonResponse
    {
        return response()->json([
            'data' => new SubjectResource($subject),
        ]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): JsonResponse
    {
        $subject->update($request->validated());

        return response()->json([
            'message' => 'Mata pelajaran berhasil diperbarui.',
            'data' => new SubjectResource($subject),
        ]);
    }

    public function destroy(Subject $subject): JsonResponse
    {
        $subject->delete();

        return response()->json([
            'message' => 'Mata pelajaran berhasil dihapus.',
        ]);
    }
}