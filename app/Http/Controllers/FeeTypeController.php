<?php

namespace App\Http\Controllers;

use App\Models\FeeType;
use App\Http\Requests\StoreFeeTypeRequest;
use App\Http\Requests\UpdateFeeTypeRequest;
use Illuminate\Http\Request;

class FeeTypeController extends Controller
{
    public function index()
    {
        $feeTypes = FeeType::orderBy('name')->paginate(20);
        return view('fee-types.index', compact('feeTypes'));
    }

    public function create()
    {
        return view('fee-types.create');
    }

    public function store(StoreFeeTypeRequest $request)
    {
        $validated = $request->validated();

        FeeType::create($validated);
        return redirect()->route('fee-types.index')->with('success', 'Fee type created successfully.');
    }

    public function edit(FeeType $feeType)
    {
        return view('fee-types.edit', compact('feeType'));
    }

    public function update(UpdateFeeTypeRequest $request, FeeType $feeType)
    {
        $validated = $request->validated();

        $feeType->update($validated);
        return redirect()->route('fee-types.index')->with('success', 'Fee type updated successfully.');
    }

    public function destroy(FeeType $feeType)
    {
        $feeType->delete();
        return redirect()->route('fee-types.index')->with('success', 'Fee type deleted successfully.');
    }
}
