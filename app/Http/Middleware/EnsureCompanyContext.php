<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Company;

class EnsureCompanyContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $companyCode = $request->route('company_code');

        if (!$companyCode) {
            return response('Company not found', 404);
        }

        $company = Company::findByCode($companyCode);

        if (!$company) {
            return response('Company not found', 404);
        }

        // SuperAdmin can access any company
        // Admin and Counter can only access their assigned company
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin() && $user->company_id && $user->company_id !== $company->id) {
            abort(403, 'Unauthorized access to this company.');
        }

        // Store company in request and session
        $request->merge(['_company' => $company]);
        session(['company' => $company]);

        return $next($request);
    }
}

