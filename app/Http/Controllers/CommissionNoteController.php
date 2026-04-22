<?php

/**
 * ============================================================
 *  Axiomatic Consultants — Technical Assessment
 *  Done by Adriaan van Niekerk
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommissionNoteRequest;
use App\Http\Requests\UpdateCommissionNoteRequest;
use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\Company;
use App\Services\CommissionNoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommissionNoteController extends Controller
{
    public function __construct(private readonly CommissionNoteService $service) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CommissionNote::class);

        $companies = Company::with('branches')->get();
        $companyId = (int) $request->query('company_id', $companies->first()?->id ?? 0);
        $company   = Company::with('branches')->findOrFail($companyId);

        $branchId = (int) $request->query('branch_id', $company->branches->first()?->id ?? 0);
        $branch   = Branch::where('company_id', $companyId)->findOrFail($branchId);

        $notes = $this->service->list($companyId, $branchId);

        // Employees scoped to the selected branch for the create/edit form
        $employees = $branch->employees()->select('id', 'name')->get();

        return Inertia::render('CommissionNotes/Index', [
            'companies'       => $companies,
            'selectedCompany' => $company,
            'selectedBranch'  => $branch,
            'notes'           => $notes,
            'employees'       => $employees,
            'canManage'       => $request->user()->can('manage commission notes'),
        ]);
    }

    public function store(StoreCommissionNoteRequest $request): RedirectResponse
    {
        $this->service->create($request->user(), $request->validated());

        return back()->with('success', 'Commission note created.');
    }

    public function update(UpdateCommissionNoteRequest $request, CommissionNote $commissionNote): RedirectResponse
    {
        // Authorization already enforced by UpdateCommissionNoteRequest (via policy).
        // Service re-checks the rule as the application-layer source of truth.
        $this->service->update($request->user(), $commissionNote, $request->validated());

        return back()->with('success', 'Commission note updated.');
    }

    public function destroy(Request $request, CommissionNote $commissionNote): RedirectResponse
    {
        // Policy enforces: only author or manager may delete.
        // Service re-checks the same rule as the application-layer source of truth.
        $this->authorize('delete', $commissionNote);

        $this->service->delete($request->user(), $commissionNote);

        return back()->with('success', 'Commission note deleted.');
    }
}
