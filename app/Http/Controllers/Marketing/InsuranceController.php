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
}
