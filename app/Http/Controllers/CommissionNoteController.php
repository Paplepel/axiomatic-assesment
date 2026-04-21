<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommissionNoteRequest;
use App\Http\Requests\UpdateCommissionNoteRequest;
use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\Company;
use App\Services\CommissionNoteService;
use Illuminate\Auth\Access\AuthorizationException;
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
        try {
            $this->service->update($request->user(), $commissionNote, $request->validated());
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }

        return back()->with('success', 'Commission note updated.');
    }

    public function destroy(Request $request, CommissionNote $commissionNote): RedirectResponse
    {
        try {
            $this->service->delete($request->user(), $commissionNote);
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }

        return back()->with('success', 'Commission note deleted.');
    }
}
