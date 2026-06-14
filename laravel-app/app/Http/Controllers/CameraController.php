<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCameraRequest;
use App\Http\Requests\UpdateCameraRequest;
use App\Models\Camera;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CameraController extends Controller
{
    public function index(Request $request): View
    {
        $cameras = $request->user()
            ->cameras()
            ->withCount('videos')
            ->latest()
            ->get();

        return view('cameras.index', [
            'cameras' => $cameras,
        ]);
    }

    public function create(): View
    {
        return view('cameras.create');
    }

    public function store(StoreCameraRequest $request): RedirectResponse
    {
        $request->user()->cameras()->create($request->validated());

        return redirect()
            ->route('cameras.index')
            ->with('status', 'Camera created successfully.');
    }

    public function show(Camera $camera): View
    {
        $this->authorize('view', $camera);

        $camera->load(['videos' => fn ($query) => $query->latest()]);

        return view('cameras.show', [
            'camera' => $camera,
        ]);
    }

    public function edit(Camera $camera): View
    {
        $this->authorize('update', $camera);

        return view('cameras.edit', [
            'camera' => $camera,
        ]);
    }

    public function update(UpdateCameraRequest $request, Camera $camera): RedirectResponse
    {
        $this->authorize('update', $camera);

        $camera->update($request->validated());

        return redirect()
            ->route('cameras.index')
            ->with('status', 'Camera updated successfully.');
    }

    public function destroy(Camera $camera): RedirectResponse
    {
        $this->authorize('delete', $camera);

        $camera->delete();

        return redirect()
            ->route('cameras.index')
            ->with('status', 'Camera deleted successfully.');
    }
}
