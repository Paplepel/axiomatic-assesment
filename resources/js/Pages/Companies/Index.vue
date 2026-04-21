<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import type { Branch, Company, PageProps } from '@/types'

const props = defineProps<PageProps<{
    companies: Company[]
}>>()

// ── Company forms ───────────────────────────────────────────
const addCompanyOpen = ref(false)
const editingCompany = ref<Company | null>(null)

const createCompanyForm = useForm({ name: '', registration_number: '' })
const editCompanyForm = useForm({ name: '', registration_number: '' })

function openEditCompany(company: Company) {
    editingCompany.value = company
    editCompanyForm.name = company.name
    editCompanyForm.registration_number = company.registration_number ?? ''
}

function submitCreateCompany() {
    createCompanyForm.post(route('companies.store'), {
        onSuccess: () => { createCompanyForm.reset(); addCompanyOpen.value = false },
    })
}

function submitUpdateCompany(company: Company) {
    editCompanyForm.put(route('companies.update', company.id), {
        onSuccess: () => { editingCompany.value = null },
    })
}

function deleteCompany(company: Company) {
    if (!confirm(`Delete company "${company.name}"? This cannot be undone.`)) return
    router.delete(route('companies.destroy', company.id))
}

// ── Branch forms ────────────────────────────────────────────
const addBranchFor = ref<number | null>(null)
const editingBranch = ref<{ companyId: number; branch: Branch } | null>(null)

const createBranchForm = useForm({ name: '', address: '' })
const editBranchForm = useForm({ name: '', address: '' })

function openAddBranch(companyId: number) {
    addBranchFor.value = companyId
    createBranchForm.reset()
}

function openEditBranch(companyId: number, branch: Branch) {
    editingBranch.value = { companyId, branch }
    editBranchForm.name = branch.name
    editBranchForm.address = branch.address ?? ''
}

function submitCreateBranch(company: Company) {
    createBranchForm.post(route('companies.branches.store', company.id), {
        onSuccess: () => { createBranchForm.reset(); addBranchFor.value = null },
    })
}

function submitUpdateBranch(companyId: number, branch: Branch) {
    editBranchForm.put(route('companies.branches.update', { company: companyId, branch: branch.id }), {
        onSuccess: () => { editingBranch.value = null },
    })
}

function deleteBranch(companyId: number, branch: Branch) {
    if (!confirm(`Delete branch "${branch.name}"?`)) return
    router.delete(route('companies.branches.destroy', { company: companyId, branch: branch.id }))
}
</script>

<template>
    <Head title="Companies" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-brand-navy">Companies</h2>
                    <p class="mt-1 text-sm text-gray-500">Manage companies and their branches</p>
                </div>
                <button
                    class="rounded bg-brand-red px-4 py-2 text-sm font-medium text-white hover:bg-red-800"
                    @click="addCompanyOpen = true"
                >
                    + Add Company
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Add Company form -->
                <div v-if="addCompanyOpen" class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-semibold text-brand-navy">New Company</h3>
                    <form @submit.prevent="submitCreateCompany" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Company Name</label>
                            <input
                                v-model="createCompanyForm.name"
                                type="text"
                                class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-brand-red focus:ring-brand-red"
                                placeholder="e.g. Spar Group"
                                required
                            />
                            <p v-if="createCompanyForm.errors.name" class="mt-1 text-xs text-red-600">{{ createCompanyForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Registration Number</label>
                            <input
                                v-model="createCompanyForm.registration_number"
                                type="text"
                                class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-brand-red focus:ring-brand-red"
                                placeholder="Optional"
                            />
                        </div>
                        <div class="sm:col-span-2 flex gap-3">
                            <button type="submit" :disabled="createCompanyForm.processing" class="rounded bg-brand-red px-4 py-2 text-sm font-medium text-white hover:bg-red-800 disabled:opacity-50">Save</button>
                            <button type="button" class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="addCompanyOpen = false; createCompanyForm.reset()">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Companies list -->
                <div v-if="companies.length === 0" class="rounded-lg bg-white p-8 text-center text-sm text-gray-400 shadow">
                    No companies yet. Click "Add Company" to get started.
                </div>

                <div v-for="company in companies" :key="company.id" class="rounded-lg bg-white shadow overflow-hidden">
                    <!-- Company header -->
                    <div class="flex items-center justify-between border-l-4 border-brand-red bg-gray-50 px-6 py-4">
                        <!-- View mode -->
                        <div v-if="editingCompany?.id !== company.id">
                            <p class="font-semibold text-brand-navy">{{ company.name }}</p>
                            <p v-if="company.registration_number" class="text-xs text-gray-500">Reg: {{ company.registration_number }}</p>
                        </div>
                        <!-- Edit mode -->
                        <form v-else @submit.prevent="submitUpdateCompany(company)" class="flex flex-wrap gap-3 items-end">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Name</label>
                                <input v-model="editCompanyForm.name" type="text" class="rounded border-gray-300 text-sm shadow-sm w-56" required />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Reg Number</label>
                                <input v-model="editCompanyForm.registration_number" type="text" class="rounded border-gray-300 text-sm shadow-sm w-40" />
                            </div>
                            <button type="submit" :disabled="editCompanyForm.processing" class="rounded bg-brand-red px-3 py-1.5 text-sm font-medium text-white hover:bg-red-800 disabled:opacity-50">Save</button>
                            <button type="button" class="rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50" @click="editingCompany = null">Cancel</button>
                        </form>

                        <div class="flex gap-3 text-sm">
                            <button class="text-brand-navy hover:text-brand-red font-medium" @click="openEditCompany(company)">Edit</button>
                            <button class="text-gray-400 hover:text-red-700 font-medium" @click="deleteCompany(company)">Delete</button>
                        </div>
                    </div>

                    <!-- Branches table -->
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-brand-navy">
                            <tr>
                                <th class="px-6 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Branch</th>
                                <th class="px-6 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Address</th>
                                <th class="px-6 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr v-if="company.branches.length === 0">
                                <td colspan="3" class="px-6 py-4 text-sm text-gray-400 italic">No branches yet.</td>
                            </tr>

                            <template v-for="branch in company.branches" :key="branch.id">
                                <!-- View row -->
                                <tr v-if="editingBranch?.branch.id !== branch.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ branch.name }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-500">{{ branch.address ?? '—' }}</td>
                                    <td class="px-6 py-3 text-right text-sm">
                                        <button class="mr-3 text-brand-navy hover:text-brand-red font-medium" @click="openEditBranch(company.id, branch)">Edit</button>
                                        <button class="text-gray-400 hover:text-red-700 font-medium" @click="deleteBranch(company.id, branch)">Delete</button>
                                    </td>
                                </tr>

                                <!-- Inline edit row -->
                                <tr v-else class="bg-red-50">
                                    <td colspan="3" class="px-6 py-3">
                                        <form @submit.prevent="submitUpdateBranch(company.id, branch)" class="flex flex-wrap gap-3 items-end">
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-gray-600">Branch Name</label>
                                                <input v-model="editBranchForm.name" type="text" class="rounded border-gray-300 text-sm shadow-sm w-48" required />
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-gray-600">Address</label>
                                                <input v-model="editBranchForm.address" type="text" class="rounded border-gray-300 text-sm shadow-sm w-64" />
                                            </div>
                                            <button type="submit" :disabled="editBranchForm.processing" class="rounded bg-brand-red px-3 py-1.5 text-sm font-medium text-white hover:bg-red-800 disabled:opacity-50">Save</button>
                                            <button type="button" class="rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50" @click="editingBranch = null">Cancel</button>
                                        </form>
                                    </td>
                                </tr>
                            </template>

                            <!-- Add branch form row -->
                            <tr v-if="addBranchFor === company.id" class="bg-gray-50">
                                <td colspan="3" class="px-6 py-3">
                                    <form @submit.prevent="submitCreateBranch(company)" class="flex flex-wrap gap-3 items-end">
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-gray-600">Branch Name</label>
                                            <input v-model="createBranchForm.name" type="text" class="rounded border-gray-300 text-sm shadow-sm w-48" placeholder="e.g. Cape Town" required />
                                            <p v-if="createBranchForm.errors.name" class="mt-1 text-xs text-red-600">{{ createBranchForm.errors.name }}</p>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-gray-600">Address</label>
                                            <input v-model="createBranchForm.address" type="text" class="rounded border-gray-300 text-sm shadow-sm w-64" placeholder="Optional" />
                                        </div>
                                        <button type="submit" :disabled="createBranchForm.processing" class="rounded bg-brand-red px-3 py-1.5 text-sm font-medium text-white hover:bg-red-800 disabled:opacity-50">Add</button>
                                        <button type="button" class="rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50" @click="addBranchFor = null">Cancel</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Add branch trigger row -->
                            <tr v-else>
                                <td colspan="3" class="px-6 py-2">
                                    <button class="text-sm text-brand-blue hover:text-brand-navy font-medium" @click="openAddBranch(company.id)">
                                        + Add Branch
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
