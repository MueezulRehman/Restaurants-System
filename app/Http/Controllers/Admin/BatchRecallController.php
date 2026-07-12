<?php

namespace App\Http\Controllers\Admin;

use App\Models\BatchRecall;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BatchRecallController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $restaurant = $user->restaurant;

        $recalls = BatchRecall::where('restaurant_id', $restaurant->id)
            ->with('medicine', 'batch', 'issuedBy')
            ->orderBy('recall_date', 'desc')
            ->paginate(15);

        return view('admin.medical.batch-recalls.index', [
            'recalls' => $recalls,
            'restaurant' => $restaurant,
        ]);
    }

    public function create()
    {
        $restaurant = auth()->user()->restaurant;
        $medicines = Medicine::where('restaurant_id', $restaurant->id)
            ->with('batches')
            ->orderBy('name')
            ->get();

        return view('admin.medical.batch-recalls.form', [
            'recall' => new BatchRecall(),
            'restaurant' => $restaurant,
            'medicines' => $medicines,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $restaurant = $user->restaurant;

        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'medicine_batch_id' => 'required|exists:medicine_batches,id',
            'recall_number' => 'required|string|unique:batch_recalls,recall_number',
            'reason' => 'required|in:expiry,quality,contamination,regulatory,damage,other',
            'description' => 'required|string',
            'recall_date' => 'required|date',
            'quantity_recalled' => 'required|integer|min:0',
            'status' => 'required|in:issued,in_progress,completed,cancelled',
            'action_taken' => 'nullable|string',
        ]);

        $validated['restaurant_id'] = $restaurant->id;
        $validated['issued_by'] = $user->id;

        BatchRecall::create($validated);

        return redirect()->route('manager.batch-recalls.index')->with('success', 'Batch recall issued successfully');
    }

    public function show(BatchRecall $batchRecall)
    {
        $this->authorize('view', $batchRecall);

        return view('admin.medical.batch-recalls.show', [
            'recall' => $batchRecall,
        ]);
    }

    public function destroy(BatchRecall $batchRecall)
    {
        $this->authorize('delete', $batchRecall);
        $batchRecall->delete();

        return redirect()->route('manager.batch-recalls.index')->with('success', 'Batch recall deleted');
    }
}
