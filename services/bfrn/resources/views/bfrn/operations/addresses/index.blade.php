@extends('bfrn.layouts.operations')

@section('title', 'Addresses')

@section('content')
<div x-data="bfrnAddressesPage()" x-init="loadAddresses()">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Addresses</h1>
            <div class="text-muted small">
                Addresses linked to the selected business unit only.
            </div>
        </div>

        <button type="button" class="btn btn-primary" @click="openCreate()">
            <i class="bi bi-plus-circle me-1"></i>Create Address
        </button>
    </div>

    <div x-show="error" class="alert alert-danger" x-cloak>
        <i class="bi bi-exclamation-triangle me-1"></i>
        <span x-text="error"></span>
    </div>

    <div x-show="success" class="alert alert-success" x-cloak>
        <i class="bi bi-check-circle me-1"></i>
        <span x-text="success"></span>
    </div>

    <div class="ops-card p-3 p-md-4 mb-3">
        <label class="form-label fw-semibold">Search Addresses</label>
        <input type="search"
               class="form-control"
               placeholder="Search by name, street, suburb, city, ZIP, or district"
               x-model.debounce.200ms="search">
    </div>

    <div class="ops-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Suburb</th>
                        <th>ZIP</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-hourglass-split me-1"></i>Loading addresses...
                            </td>
                        </tr>
                    </template>

                    <template x-for="address in filteredAddresses()" :key="address.id">
                        <tr>
                            <td>
                                <div class="fw-semibold" x-text="address.name || ('Address #' + address.id)"></div>
                                <div class="text-muted small">ID: <span x-text="address.id"></span></div>
                            </td>
                            <td class="small" x-text="addressLine(address)"></td>
                            <td class="small" x-text="address.suburb || '-'"></td>
                            <td class="small" x-text="address.ZIP || address.zip || '-'"></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" @click="openEdit(address.id)">
                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                </button>
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && filteredAddresses().length === 0">
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No addresses found for this business unit.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <template x-if="modalOpen">
        <div class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
             style="display:flex; background:rgba(15,23,42,.45); z-index:1070;"
             @click.self="closeModal()">
            <div class="bg-white rounded-4 shadow-lg w-100 mx-3" style="max-width:760px; max-height:92vh; overflow:auto;">
                <div class="d-flex justify-content-between align-items-start border-bottom p-4">
                    <div>
                        <h2 class="h5 fw-bold mb-1" x-text="mode === 'edit' ? 'Edit Address' : 'Create Address'"></h2>
                        <div class="text-muted small">Current BU: {{ session('active_bu_name', 'No BU selected') }}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="closeModal()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form x-ref="addressForm" @submit.prevent="submitAddress()" class="p-4">
                    <div x-show="formError" class="alert alert-danger" x-cloak>
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <span x-text="formError"></span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Address Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">PO Box</label>
                            <input type="text" name="p_o_box" class="form-control" maxlength="100">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Line 1</label>
                            <input type="text" name="line1" class="form-control" maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Line 2</label>
                            <input type="text" name="line2" class="form-control" maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Line 3</label>
                            <input type="text" name="line3" class="form-control" maxlength="100">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Suburb</label>
                            <input type="text" name="suburb" class="form-control" maxlength="100">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">ZIP</label>
                            <input type="text" name="zip" class="form-control" maxlength="20">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">District</label>
                            <input type="text" name="district" class="form-control" maxlength="100">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top mt-4 pt-4">
                        <button type="button" class="btn btn-outline-secondary" @click="closeModal()" :disabled="saving">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            <span x-show="!saving">
                                <i class="bi bi-save me-1"></i>Save Address
                            </span>
                            <span x-show="saving">
                                <i class="bi bi-hourglass-split me-1"></i>Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<script>
function bfrnAddressesPage() {
    return {
        addresses: [],
        search: '',
        loading: false,
        saving: false,
        error: '',
        success: '',
        formError: '',
        modalOpen: false,
        mode: 'create',
        editingId: null,

        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        },

        async loadAddresses() {
            this.loading = true;
            this.error = '';

            try {
                const response = await fetch('/bfrn/api/addresses', {
                    headers: { 'Accept': 'application/json' }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || data.detail || 'Unable to load addresses.');
                }

                this.addresses = data.results || data || [];
            } catch (error) {
                this.error = error.message || 'Unable to load addresses.';
                this.addresses = [];
            } finally {
                this.loading = false;
            }
        },

        filteredAddresses() {
            const term = this.search.toLowerCase().trim();

            if (!term) return this.addresses;

            return this.addresses.filter((address) => [
                address.name,
                address.line1,
                address.line2,
                address.line3,
                address.suburb,
                address.ZIP,
                address.zip,
                address.district
            ].filter(Boolean).join(' ').toLowerCase().includes(term));
        },

        addressLine(address) {
            return [
                address.line1,
                address.line2,
                address.line3
            ].filter(Boolean).join(', ') || '-';
        },

        resetForm() {
            this.$nextTick(() => {
                const form = this.$refs.addressForm;
                if (form) form.reset();
            });
        },

        openCreate() {
            this.mode = 'create';
            this.editingId = null;
            this.formError = '';
            this.modalOpen = true;
            this.resetForm();
        },

        async openEdit(id) {
            this.mode = 'edit';
            this.editingId = id;
            this.formError = '';
            this.modalOpen = true;

            try {
                const response = await fetch(`/bfrn/api/addresses/${id}`, {
                    headers: { 'Accept': 'application/json' }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || data.detail || 'Unable to load address.');
                }

                this.$nextTick(() => {
                    const form = this.$refs.addressForm;
                    if (!form) return;

                    Object.entries(data).forEach(([key, value]) => {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input && value !== null) input.value = value;
                    });
                });
            } catch (error) {
                this.formError = error.message || 'Unable to load address.';
            }
        },

        closeModal() {
            this.modalOpen = false;
            this.formError = '';
            this.saving = false;
        },

        async submitAddress() {
            this.saving = true;
            this.formError = '';
            this.success = '';

            try {
                const form = this.$refs.addressForm;
                const formData = new FormData(form);
                formData.set('adress_type', '1');

                const payload = Object.fromEntries(formData.entries());
                const isEdit = this.mode === 'edit';

                const response = await fetch(isEdit ? `/bfrn/api/addresses/${this.editingId}` : '/bfrn/api/addresses', {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        ...(isEdit ? { 'Content-Type': 'application/json' } : {})
                    },
                    body: isEdit ? JSON.stringify(payload) : formData
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || result.error || 'Unable to save address.');
                }

                this.success = isEdit ? 'Address updated successfully.' : 'Address created successfully.';
                this.modalOpen = false;
                await this.loadAddresses();
            } catch (error) {
                this.formError = error.message || 'Unable to save address.';
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>
@endsection
