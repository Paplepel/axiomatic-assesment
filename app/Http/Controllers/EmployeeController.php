<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Employee::class);

        return Inertia::render('Employees/Index', [
            'companies' => Company::with(['branches.employees'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        $data = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'branch_id'  => ['required', 'integer', 'exists:branches,id'],
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['nullable', 'email', 'max:255'],
        ]);

        // Confirm branch belongs to the given company
        $branch = Branch::findOrFail($data['branch_id']);
        abort_if($branch->company_id !== (int) $data['company_id'], 422, 'Branch does not belong to the selected company.');

        Employee::create([...$data, 'created_by' => $request->user()->id]);

        return back()->with('success', 'Employee added.');
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $data = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'branch_id'  => ['required', 'integer', 'exists:branches,id'],
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['nullable', 'email', 'max:255'],
        ]);

        $branch = Branch::findOrFail($data['branch_id']);
        abort_if($branch->company_id !== (int) $data['company_id'], 422, 'Branch does not belong to the selected company.');

        $employee->update($data);

        return back()->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return back()->with('success', 'Employee deleted.');
    }
}
