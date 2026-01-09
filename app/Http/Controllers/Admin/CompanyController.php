<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function index()
    {
        // Only SuperAdmin can access this
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $companies = Company::with('setting')->orderBy('created_at', 'desc')->get();
        return view('admin.companies.index', compact('companies'));
    }

    public function create()
    {
        // Only SuperAdmin can access this
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        // Only SuperAdmin can access this
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'company_code' => 'required|string|unique:companies,company_code|max:50|alpha_dash',
            'company_name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['company_code'] = strtoupper($validated['company_code']);
        $validated['is_active'] = $request->has('is_active');

        $company = Company::create($validated);

        // Create default company settings
        CompanySetting::create([
            'company_id' => $company->id,
            'code' => $company->company_code,
            'company_name' => $company->company_name,
            'primary_color' => '#4F46E5',
            'secondary_color' => '#10B981',
            'accent_color' => '#F59E0B',
            'text_color' => '#1F2937',
            'queue_number_digits' => 4,
            'is_active' => true,
        ]);

        return redirect()->route('superadmin.companies.index')
            ->with('success', 'Company created successfully.');
    }

    public function edit($company)
    {
        // Only SuperAdmin can access this
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $company = Company::findOrFail($company);
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, $company)
    {
        // Only SuperAdmin can access this
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $company = Company::findOrFail($company);

        $validated = $request->validate([
            'company_code' => 'required|string|max:50|alpha_dash|unique:companies,company_code,' . $company->id,
            'company_name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['company_code'] = strtoupper($validated['company_code']);
        $validated['is_active'] = $request->has('is_active');

        $company->update($validated);

        // Update company settings if exists
        if ($company->setting) {
            $company->setting->update([
                'code' => $validated['company_code'],
                'company_name' => $validated['company_name'],
            ]);
        }

        return redirect()->route('superadmin.companies.index')
            ->with('success', 'Company updated successfully.');
    }

    public function destroy($company)
    {
        // Only SuperAdmin can access this
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $company = Company::findOrFail($company);

        // Prevent deletion if company has users
        if ($company->users()->count() > 0) {
            return back()->with('error', 'Cannot delete company with existing users. Please reassign or delete users first.');
        }

        $company->delete();

        return redirect()->route('superadmin.companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}
