<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Services\CotDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ObservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Observation::with(['observer', 'observee.user']);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('grade_level', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->observation_type) {
            $query->where('observation_type', $request->observation_type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->stage) {
            $query->where('stage', $request->stage);
        }

        $observations = $query->latest('observation_date')->paginate(15)->withQueryString();

        $stats = [
            'total' => Observation::count(),
            'in_progress' => Observation::whereIn('status', ['scheduled', 'in_progress'])->count(),
            'completed' => Observation::where('status', 'completed')->count(),
            'cancelled' => Observation::where('status', 'cancelled')->count(),
        ];

        return view('admin.observations.index', compact('observations', 'stats'));
    }

    public function show(Observation $observation)
    {
        $observation->load([
            'observer',
            'observee.user',
            'cancelledBy',
            'preObservationPlanning',
            'preConference',
            'postConference',
            'cotRatings',
        ]);

        return view('admin.observations.show', compact('observation'));
    }

    /**
     * Download the completed COT document (DOCX) for an observation.
     *
     * Generates it on demand when it does not exist yet, reusing the exact
     * document the supervisor workflow produces.
     */
    public function downloadCotDocument(Observation $observation)
    {
        $service = app(CotDocumentService::class);

        $errors = $service->canGenerate($observation);
        if ($errors) {
            return redirect()->back()->with('error', 'Cannot download the COT document: '.implode(' ', $errors));
        }

        $path = $service->documentPath($observation);

        if (!$path) {
            try {
                $path = $service->generateDocument($observation);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Admin COT document generation failed', ['observation_id' => $observation->id, 'error' => $e->getMessage()]);

                return redirect()->back()->with('error', 'Failed to generate the COT document. Please try again.');
            }
        }

        return Storage::disk(CotDocumentService::DISK)->download($path, basename($path));
    }
}
