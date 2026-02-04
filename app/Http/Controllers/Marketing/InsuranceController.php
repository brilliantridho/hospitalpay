<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Insurance;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    /**
     * Display a listing of insurances
     */
    public function index()
    {
        $insurances = Insurance::where('is_active', true)
            ->orderBy('discount_percentage', 'desc')
            ->get();
        
        return view('marketing.insurances.index', compact('insurances'));
    }

    /**
     * Show details of a specific insurance
     */
    public function show(Insurance $insurance)
    {
        $insurance->load('vouchers');
        return view('marketing.insurances.show', compact('insurance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Insurance $insurance)
    {
        return view('marketing.insurances.edit', compact('insurance'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Insurance $insurance)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'terms' => 'nullable|string',
            'coverage_limit' => 'nullable|numeric|min:0',
        ]);

        $insurance->update($validated);

        return redirect()->route('marketing.insurances.index')
            ->with('success', 'Data asuransi berhasil diupdate.');
    }
}
