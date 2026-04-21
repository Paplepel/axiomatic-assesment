<?php

namespace App\Services;

use App\Models\CommissionNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Auth\Access\AuthorizationException;

class CommissionNoteService
{
    /**
     * List all commission notes scoped to a company and branch.
     */
    public function list(int $companyId, int $branchId): Collection
    {
        return CommissionNote::with(['employee', 'author', 'branch', 'company'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->latest()
            ->get();
    }

    /**
     * Create a new commission note.
     */
    public function create(User $author, array $data): CommissionNote
    {
        return CommissionNote::create([
            'company_id'   => $data['company_id'],
            'branch_id'    => $data['branch_id'],
            'employee_id'  => $data['employee_id'],
            'created_by'   => $author->id,
            'amount'       => $data['amount'],
            'notes'        => $data['notes'] ?? null,
            'payment_date' => $data['payment_date'],
        ]);
    }

    /**
     * Update an existing commission note.
     *
     * Business rule: only the original author may edit unless the user
     * holds the 'manage commission notes' permission.
     *
     * @throws AuthorizationException
     */
    public function update(User $user, CommissionNote $note, array $data): CommissionNote
    {
        if ($note->created_by !== $user->id && ! $user->can('manage commission notes')) {
            throw new AuthorizationException('You are not authorised to edit this note.');
        }

        $note->update([
            'employee_id'  => $data['employee_id'],
            'amount'       => $data['amount'],
            'notes'        => $data['notes'] ?? null,
            'payment_date' => $data['payment_date'],
        ]);

        return $note->fresh();
    }

    /**
     * Delete a commission note.
     *
     * Business rule: only the original author may delete unless the user
     * holds the 'manage commission notes' permission.
     *
     * @throws AuthorizationException
     */
    public function delete(User $user, CommissionNote $note): void
    {
        if ($note->created_by !== $user->id && ! $user->can('manage commission notes')) {
            throw new AuthorizationException('You are not authorised to delete this note.');
        }

        $note->delete();
    }
}
