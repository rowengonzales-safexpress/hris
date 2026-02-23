<?php

namespace App\Http\Controllers;

use App\Models\CoreTransactionCode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CoreTransactionCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CoreTransactionCode::query();

        // Filter by transaction_name if provided
        if ($request->has('transaction_name')) {
            $query->where('transaction_name', $request->transaction_name);
        }

        // Filter by identitycode if provided
        if ($request->has('identitycode')) {
            $query->where('identitycode', $request->identitycode);
        }

        // Filter by active status
        if ($request->has('active_only') && $request->active_only) {
            $query->active();
        }

        $transactionCodes = $query->orderBy('sortorder')->get();

        return response()->json([
            'success' => true,
            'data' => $transactionCodes
        ]);
    }

    /**
     * Get FRMS transaction types for dropdown
     */
    public function getFrmsTypes(): JsonResponse
    {
        $frmsTypes = CoreTransactionCode::bytransaction_name('FRMS-Transtype')
            ->active()
            ->orderBy('sortorder')
            ->get(['id', 'description']);

        return response()->json([
            'success' => true,
            'data' => $frmsTypes
        ]);
    }
    public function getFrmsFrequincy(): JsonResponse
    {
        $frmsTypes = CoreTransactionCode::bytransaction_name('FRMS-Frequency')
            ->active()
            ->orderBy('sortorder')
            ->get(['id', 'description','identitycode','sortorder','isactive']);

        return response()->json([
            'success' => true,
            'data' => $frmsTypes
        ]);
    }
    public function getFrmsVat(): JsonResponse
    {
        $frmsTypes = CoreTransactionCode::bytransaction_name('FRMS-Vat')
            ->active()
            ->orderBy('sortorder')
            ->get(['id','identitycode','description', 'trans_value','sortorder','isactive']);

        return response()->json([
            'success' => true,
            'data' => $frmsTypes
        ]);
    }

       public function getFrmsAccountCode(): JsonResponse
    {
        $frmsTypes = CoreTransactionCode::bytransaction_name('FRMS-Account-Code')
            ->active()
            ->orderBy('sortorder')
            ->get(['id', 'description', 'identitycode','sortorder','isactive']);

        return response()->json([
            'success' => true,
            'data' => $frmsTypes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_name' => 'required|string|max:255',
            'transaction_key' => 'required|string|max:255',
            'identitycode' => 'required|string|max:255',
            'trans_value' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'sortorder' => 'integer|min:0',
            'isactive' => 'boolean',
            'createdby' => 'nullable|string|max:255'
        ]);

        $transactionCode = CoreTransactionCode::create($validated);

        return redirect()->back()->with('success', 'Transaction code created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $model = CoreTransactionCode::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $model
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'transaction_name' => 'sometimes|required|string|max:255',
            'transaction_key' => 'sometimes|required|string|max:255',
            'identitycode' => 'sometimes|nullable|string|max:255',
            'trans_value' => 'sometimes|nullable|numeric|min:0',
            'description' => 'sometimes|nullable|string',
            'sortorder' => 'sometimes|integer|min:0',
            'isactive' => 'sometimes|boolean',
            'updatedby' => 'sometimes|nullable|string|max:255'
        ]);
        $model = CoreTransactionCode::findOrFail($id);
        $model->fill($validated)->save();

        return redirect()->back()->with('success', 'Transaction code updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $model = CoreTransactionCode::findOrFail($id);
        $model->delete();

        return redirect()->back()->with('success', 'Transaction code deleted successfully');
    }
}
