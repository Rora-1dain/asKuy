<?php

namespace App\Http\Controllers;

use App\Http\Resources\AiWeaknessMappingResource;
use App\Models\AiWeaknessMapping;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AiWeaknessMappingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = AiWeaknessMapping::with('subject')
            ->where('user_id', $request->user()->id);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', (int) $request->subject_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $weaknesses = $query->orderBy('mastery_percentage', 'asc')->paginate($perPage);

        return AiWeaknessMappingResource::collection($weaknesses);
    }
}