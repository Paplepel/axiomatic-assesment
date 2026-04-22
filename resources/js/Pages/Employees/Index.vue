<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import type { Branch, Company, Employee, PageProps } from '@/types'

interface CompanyWithBranchEmployees extends Company {
    branches: (Branch & { employees: Employee[] })[]
}

const props = defineProps<PageProps<{
    companies: CompanyWithBranchEmployees[]
}>>()

// ── Add employee form ───────────────────────────────────────
const addOpen = ref(false)
const addForm = useForm({
    company_id: '' as number | '',
    branch_id: '' as number | '',
    name: '',
    email: '',
})

const addBranches = computed<Branch[]>(() => {
    if (!addForm.company_id) return []
    return props.companies.find(c => c.id === Number(addForm.company_id))?.branches ?? []
})

function openAdd() {
    addForm.reset()
    addOpen.value = true
}

function submitAdd() {
    addForm.post(route('employees.store'), {
        onSuccess: () => { addForm.reset(); addOpen.value = false },
    })
}

// ── Edit employee form ──────────────────────────────────────
const editingEmployee = ref<Employee | null>(null)
const editForm = useForm({
    company_id: '' as number | '',
    branch_id: '' as number | '',
    name: '',
    email: '',
})

const editBranches = computed<Branch[]>(() => {
    if (!editForm.company_id) return []
    return props.companies.find(c => c.id === Number(editForm.company_id))?.branches ?? []
})

function openEdit(employee: Employee) {
    editingEmployee.value = employee
    editForm.company_id = employee.company_id
    editForm.branch_id = employee.branch_id
    editForm.name = employee.name
    editForm.email = employee.email ?? ''
}

function submitEdit(employee: Employee) {
    editForm.put(route('employees.update', employee.id), {
        onSuccess: () => { editingEmployee.value = null },
    })
}

function deleteEmployee(employee: Employee) {
    if (!confirm(`Delete employee "${employee.name}"?`)) return
    router.delete(route('employees.destroy', employee.id))
}

// ── Helpers ─────────────────────────────────────────────────
const totalEmployees = computed(() =>
    props.companies.reduce((sum, c) => sum + c.branches.reduce((s, b) => s + b.employees.length, 0), 0)
)
</script>

<template>
    <Head title="Employees" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-brand-navy">Employees</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ totalEmployees }} employee{{ totalEmployees !== 1 ? 's' : '' }} across all branches</p>
                </div>
                <button
                    class="rounded bg-brand-red px-4 py-2 text-sm font-medium text-white hover:bg-red-800"
                    @click="openAdd"
                >
                    + Add Employee
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Add Employee form -->
                <div v-if="addOpen" class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-semibold text-brand-navy">Add Employee to Branch</h3>
                    <form @submit.prevent="submitAdd" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Company</label>
                            <select
                                v-model="addForm.company_id"
                                class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-brand-red focus:ring-brand-red"
                                required
                                @change="addForm.branch_id = ''"
                            >
                                <option value="" disabled>Select company…</option>
                                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <p v-if="addForm.errors.company_id" class="mt-1 text-xs text-red-600">{{ addForm.errors.company_id }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Branch</label>
                            <select
                                v-model="addForm.branch_id"
                                class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-brand-red focus:ring-brand-red"
                                required
                                :disabled="!addForm.company_id"
                            >
                                <option value="" disabled>Select branch…</option>
                                <option v-for="b in addBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                            <p v-if="addForm.errors.branch_id" class="mt-1 text-xs text-red-600">{{ addForm.errors.branch_id }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Full Name</label>
                            <input
                                v-model="addForm.name"
                                type="text"
                                class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-brand-red focus:ring-brand-red"
                                placeholder="e.g. Jane Smith"
                                required
                            />
                            <p v-if="addForm.errors.name" class="mt-1 text-xs text-red-600">{{ addForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                            <input
                                v-model="addForm.email"
                                type="email"
                                class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-brand-red focus:ring-brand-red"
                                placeholder="Optional"
                            />
                            <p v-if="addForm.errors.email" class="mt-1 text-xs text-red-600">{{ addForm.errors.email }}</p>
                        </div>
                        <div class="sm:col-span-2 flex gap-3">
                            <button type="submit" :disabled="addForm.processing" class="rounded bg-brand-red px-4 py-2 text-sm font-medium text-white hover:bg-red-800 disabled:opacity-50">Save</button>
                            <button type="button" class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="addOpen = false; addForm.reset()">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- No data state -->
                <div v-if="totalEmployees === 0 && !addOpen" class="rounded-lg bg-white p-8 text-center text-sm text-gray-400 shadow">
                    No employees yet. Click "Add Employee" to get started.
                </div>

                <!-- Companies → Branches → Employees -->
                <template v-for="company in companies" :key="company.id">
                    <template v-for="branch in company.branches" :key="branch.id">
                        <div v-if="branch.employees.length > 0 || editingEmployee?.branch_id === branch.id" class="rounded-lg bg-white shadow overflow-hidden">
                            <!-- Branch header -->
                            <div class="flex items-center gap-3 border-l-4 border-brand-red bg-gray-50 px-6 py-3">
                                <div>
                                    <span class="font-semibold text-brand-navy">{{ branch.name }}</span>
                                    <span class="mx-2 text-gray-400">·</span>
                                    <span class="text-sm text-gray-500">{{ company.name }}</span>
                                </div>
                            </div>

                            <!-- Employee table -->
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-brand-navy">
                                    <tr>
                                        <th class="px-6 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Name</th>
                                        <th class="px-6 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Email</th>
                                        <th class="px-6 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <template v-for="emp in branch.employees" :key="emp.id">
                                        <!-- View row -->
                                        <tr v-if="editingEmployee?.id !== emp.id" class="hover:bg-gray-50">
                                            <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ emp.name }}</td>
                                            <td class="px-6 py-3 text-sm text-gray-500">{{ emp.email ?? '—' }}</td>
                                            <td class="px-6 py-3 text-right text-sm">
                                                <button class="mr-3 text-brand-navy hover:text-brand-red font-medium" @click="openEdit(emp)">Edit</button>
                                                <button class="text-gray-400 hover:text-red-700 font-medium" @click="deleteEmployee(emp)">Delete</button>
                                            </td>
                                        </tr>

                                        <!-- Inline edit row -->
                                        <tr v-else class="bg-red-50">
                                            <td colspan="3" class="px-6 py-4">
                                                <form @submit.prevent="submitEdit(emp)" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                    <div>
                                                        <label class="mb-1 block text-xs font-medium text-gray-600">Company</label>
                                                        <select
                                                            v-model="editForm.company_id"
                                                            class="w-full rounded border-gray-300 text-sm shadow-sm"
                                                            @change="editForm.branch_id = ''"
                                                        >
                                                            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="mb-1 block text-xs font-medium text-gray-600">Branch</label>
                                                        <select
                                                            v-model="editForm.branch_id"
                                                            class="w-full rounded border-gray-300 text-sm shadow-sm"
                                                            :disabled="!editForm.company_id"
                                                        >
                                                            <option v-for="b in editBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="mb-1 block text-xs font-medium text-gray-600">Full Name</label>
                                                        <input v-model="editForm.name" type="text" class="w-full rounded border-gray-300 text-sm shadow-sm" required />
                                                    </div>
                                                    <div>
                                                        <label class="mb-1 block text-xs font-medium text-gray-600">Email</label>
                                                        <input v-model="editForm.email" type="email" class="w-full rounded border-gray-300 text-sm shadow-sm" />
                                                    </div>
                                                    <div class="sm:col-span-2 flex gap-3">
                                                        <button type="submit" :disabled="editForm.processing" class="rounded bg-brand-red px-3 py-1.5 text-sm font-medium text-white hover:bg-red-800 disabled:opacity-50">Save</button>
                                                        <button type="button" class="rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50" @click="editingEmployee = null">Cancel</button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </template>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
