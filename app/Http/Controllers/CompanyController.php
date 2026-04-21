<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(): Response
    {
        $this->authorize('manage commission notes', Company::class);

        return Inertia::render('Companies/Index', [
            'companies' => Company::with('branches')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage commission notes', Company::class);

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:100'],
        ]);

        Company::create($data);

        return back()->with('success', 'Company created.');
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $this->authorize('manage commission notes', Company::class);

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:100'],
        ]);

        $company->update($data);

        return back()->with('success', 'Company updated.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->authorize('manage commission notes', Company::class);

        $company->delete();

        return back()->with('success', 'Company deleted.');
    }

    // ── Branch sub-resources ────────────────────────────────────────

    public function storeBranch(Request $request, Company $company): RedirectResponse
    {
        $this->authorize('manage commission notes', Company::class);

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $company->branches()->create($data);

        return back()->with('success', 'Branch created.');
    }

    public function updateBranch(Request $request, Company $company, Branch $branch): RedirectResponse
    {
        $this->authorize('manage commission notes', Company::class);

        abort_if($branch->company_id !== $company->id, 404);

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $branch->update($data);

        return back()->with('success', 'Branch updated.');
    }

    public function destroyBranch(Company $company, Branch $branch): RedirectResponse
    {
        $this->authorize('manage commission notes', Company::class);

        abort_if($branch->company_id !== $company->id, 404);

        $branch->delete();

        return back()->with('success', 'Branch deleted.');
    }
}
