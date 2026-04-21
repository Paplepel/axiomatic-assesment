<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import { useCommissionNoteStore } from '@/stores/commissionNoteStore'
import type { Branch, CommissionNote, Company, Employee, PageProps } from '@/types'

// ── Props ──────────────────────────────────────────────────
const props = defineProps<PageProps<{
    companies: Company[]
    selectedCompany: Company
    selectedBranch: Branch
    notes: CommissionNote[]
    employees: Employee[]
    canManage: boolean
}>>()

// ── Pinia store ────────────────────────────────────────────
const store = useCommissionNoteStore()

// ── Context selectors ──────────────────────────────────────
function navigateTo(companyId: number, branchId: number) {
    router.get(route('commission-notes.index'), { company_id: companyId, branch_id: branchId })
}

function onCompanyChange(e: Event) {
    const companyId = Number((e.target as HTMLSelectElement).value)
    const company = props.companies.find(c => c.id === companyId)
    if (company?.branches?.length) {
        navigateTo(companyId, company.branches[0].id)
    }
}

function onBranchChange(e: Event) {
    navigateTo(props.selectedCompany.id, Number((e.target as HTMLSelectElement).value))
}

// ── Create form ────────────────────────────────────────────
const createForm = useForm({
    company_id: props.selectedCompany.id,
    branch_id: props.selectedBranch.id,
    employee_id: props.employees[0]?.id ?? 0,
    amount: '',
    notes: '',
    payment_date: new Date().toISOString().slice(0, 10),
})

watch(
    () => [props.selectedCompany.id, props.selectedBranch.id],
    ([cid, bid]) => {
        createForm.company_id = cid
        createForm.branch_id = bid
        createForm.employee_id = props.employees[0]?.id ?? 0
    },
)

function submitCreate() {
    createForm.post(route('commission-notes.store'), {
        onSuccess: () => {
            createForm.reset('amount', 'notes')
            store.closeAddPanel()
        },
    })
}

// ── Edit form ──────────────────────────────────────────────
const editForm = useForm({
    employee_id: 0,
    amount: '',
    notes: '',
    payment_date: '',
})

watch(
    () => store.selectedNote,
    (note) => {
        if (note) {
            editForm.employee_id = note.employee_id
            editForm.amount = note.amount
            editForm.notes = note.notes ?? ''
            editForm.payment_date = note.payment_date
        }
    },
)

function submitEdit(note: CommissionNote) {
    editForm.put(route('commission-notes.update', note.id), {
        onSuccess: () => store.clearSelection(),
    })
}

// ── Delete ─────────────────────────────────────────────────
function deleteNote(note: CommissionNote) {
    if (!confirm('Delete this commission note?')) return
    router.delete(route('commission-notes.destroy', note.id))
}

// ── Helpers ────────────────────────────────────────────────
const formattedAmount = (amount: string | number) =>
    Number(amount).toLocaleString('en-ZA', { style: 'currency', currency: 'ZAR' })

const canEditNote = (note: CommissionNote) =>
    props.canManage || note.created_by === props.auth.user.id
</script>

<template>
    <Head title="Commission Notes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-brand-navy">
                Commission Notes
            </h2>
            <p class="mt-1 text-sm text-gray-500">{{ selectedCompany.name }} — {{ selectedBranch.name }}</p>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Context selectors -->
                <div class="flex flex-wrap gap-4 rounded-lg bg-white p-4 shadow">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-gray-600">Company</label>
                        <select
                            class="rounded border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500"
                            :value="selectedCompany.id"
                            @change="onCompanyChange"
                        >
                            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-gray-600">Branch</label>
                        <select
                            class="rounded border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500"
                            :value="selectedBranch.id"
                            @change="onBranchChange"
                        >
                            <option
                                v-for="b in selectedCompany.branches"
                                :key="b.id"
                                :value="b.id"
                            >{{ b.name }}</option>
                        </select>
                    </div>

                    <div v-if="canManage" class="ml-auto flex items-end">
                        <button
                            class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500"
                            @click="store.openAddPanel()"
                        >
                            + Add Note
                        </button>
                    </div>
                </div>

                <!-- Add note panel -->
                <div
                    v-if="store.addPanelOpen && canManage"
                    class="rounded-lg bg-white p-6 shadow"
                >
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">New Commission Note</h3>
                    <form @submit.prevent="submitCreate" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Employee</label>
                            <select v-model="createForm.employee_id" class="w-full rounded border-gray-300 text-sm shadow-sm">
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                            </select>
                            <p v-if="createForm.errors.employee_id" class="mt-1 text-xs text-red-600">{{ createForm.errors.employee_id }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Amount (ZAR)</label>
                            <input
                                v-model="createForm.amount"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full rounded border-gray-300 text-sm shadow-sm"
                                placeholder="0.00"
                            />
                            <p v-if="createForm.errors.amount" class="mt-1 text-xs text-red-600">{{ createForm.errors.amount }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Payment Date</label>
                            <input
                                v-model="createForm.payment_date"
                                type="date"
                                class="w-full rounded border-gray-300 text-sm shadow-sm"
                            />
                            <p v-if="createForm.errors.payment_date" class="mt-1 text-xs text-red-600">{{ createForm.errors.payment_date }}</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Notes</label>
                            <textarea
                                v-model="createForm.notes"
                                rows="3"
                                class="w-full rounded border-gray-300 text-sm shadow-sm"
                                placeholder="Optional notes…"
                            />
                        </div>

                        <div class="sm:col-span-2 flex gap-3">
                            <button
                                type="submit"
                                :disabled="createForm.processing"
                                class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800 disabled:opacity-50"
                            >Save</button>
                            <button
                                type="button"
                                class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                @click="store.closeAddPanel()"
                            >Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Notes table -->
                <div class="rounded-lg bg-white shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-brand-navy">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Payment Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Notes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Created By</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-if="notes.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">
                                    No commission notes for this branch yet.
                                </td>
                            </tr>

                            <template v-for="note in notes" :key="note.id">
                                <!-- View row -->
                                <tr v-if="store.selectedNote?.id !== note.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ note.employee?.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ formattedAmount(note.amount) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ note.payment_date }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ note.notes ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ note.author?.name }}</td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <template v-if="canEditNote(note)">
                                            <button
                                                class="mr-3 text-red-700 hover:text-red-900 font-medium"
                                                @click="store.selectNote(note)"
                                            >Edit</button>
                                            <button
                                                class="text-gray-500 hover:text-red-700 font-medium"
                                                @click="deleteNote(note)"
                                            >Delete</button>
                                        </template>
                                    </td>
                                </tr>

                                <!-- Inline edit row -->
                                <tr v-else class="bg-red-50">
                                    <td colspan="6" class="px-6 py-4">
                                        <form @submit.prevent="submitEdit(note)" class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-gray-600">Employee</label>
                                                <select v-model="editForm.employee_id" class="w-full rounded border-gray-300 text-sm shadow-sm">
                                                    <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-gray-600">Amount</label>
                                                <input v-model="editForm.amount" type="number" step="0.01" min="0" class="w-full rounded border-gray-300 text-sm shadow-sm" />
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-gray-600">Payment Date</label>
                                                <input v-model="editForm.payment_date" type="date" class="w-full rounded border-gray-300 text-sm shadow-sm" />
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-gray-600">Notes</label>
                                                <input v-model="editForm.notes" type="text" class="w-full rounded border-gray-300 text-sm shadow-sm" />
                                            </div>
                                            <div class="sm:col-span-4 flex gap-3">
                                                <button type="submit" :disabled="editForm.processing" class="rounded bg-red-700 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-800 disabled:opacity-50">Update</button>
                                                <button type="button" class="rounded border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="store.clearSelection()">Cancel</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
