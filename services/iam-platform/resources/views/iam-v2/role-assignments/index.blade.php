@extends('iam-v2.layouts.administration')

@section('title', 'Contextual Role Assignments')

@section(
    'subtitle',
    'Review roles scoped by company, business unit and system.'
)

@section('content')
<div
    x-data="roleAssignmentPage({
        endpoint: @js($assignmentEndpoint),
        storeEndpoint: @js($assignmentStoreEndpoint),
        catalogueEndpoint: @js($assignmentCatalogueEndpoint),
        activeContextsEndpoint: @js($activeContextsEndpoint),
        activeContextUpdateEndpoint: @js($activeContextUpdateEndpoint),
        csrfToken: @js(csrf_token())
    })"
    x-init="initialize()"
>
    <section class="iam-card p-3 mb-3">
        <div
            class="d-flex flex-column flex-lg-row align-items-lg-end gap-3"
        >
            <div class="flex-grow-1">
                <label
                    for="active-context-selector"
                    class="form-label small fw-semibold"
                >
                    Active Context
                </label>

                <select
                    id="active-context-selector"
                    class="form-select"
                    x-model="selectedContextKey"
                    :disabled="
                        contextLoading
                        || contextSwitching
                        || activeContexts.length === 0
                    "
                >
                    <option value="">
                        Select an authorized context
                    </option>

                    <template
                        x-for="context in activeContexts"
                        :key="contextKey(context)"
                    >
                        <option
                            :value="contextKey(context)"
                            x-text="
                                context.company_name
                                + ' / '
                                + context.business_unit_name
                                + ' / '
                                + context.system_name
                            "
                        ></option>
                    </template>
                </select>

                <div
                    class="small text-secondary mt-2"
                    x-show="!contextLoading"
                >
                    Only contexts assigned to your IAM identity
                    are available.
                </div>
            </div>

            <button
                type="button"
                class="btn btn-outline-primary"
                :disabled="
                    contextLoading
                    || contextSwitching
                    || !selectedContextKey
                    || selectedContextKey === activeContextKey
                "
                @click="activateSelectedContext()"
            >
                <span
                    x-show="contextSwitching"
                    class="spinner-border spinner-border-sm me-1"
                ></span>

                <i
                    x-show="!contextSwitching"
                    class="bi bi-arrow-repeat me-1"
                ></i>

                Activate Context
            </button>
        </div>

        <div
            x-cloak
            x-show="contextError"
            class="alert alert-danger mt-3 mb-0"
            x-text="contextError"
        ></div>
    </section>

    <div
        class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3"
    >
        <div>
            <h2 class="h5 fw-bold mb-1">
                Assignment Administration
            </h2>

            <div class="small text-secondary">
                Review and create contextual role assignments.
            </div>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            :disabled="catalogueLoading"
            @click="openCreateModal()"
        >
            <span
                x-show="catalogueLoading"
                class="spinner-border spinner-border-sm me-1"
            ></span>

            <i
                x-show="!catalogueLoading"
                class="bi bi-person-plus me-1"
            ></i>

            Assign Role
        </button>
    </div>

    <section class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="iam-card p-3 h-100">
                <div class="small fw-semibold text-secondary">
                    Matching Assignments
                </div>

                <div
                    class="fs-3 fw-bold mt-1"
                    x-text="meta.total"
                ></div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="iam-card p-3 h-100">
                <div class="small fw-semibold text-secondary">
                    Active on Page
                </div>

                <div
                    class="fs-3 fw-bold mt-1 text-success"
                    x-text="activeOnPage"
                ></div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="iam-card p-3 h-100">
                <div class="small fw-semibold text-secondary">
                    Revoked on Page
                </div>

                <div
                    class="fs-3 fw-bold mt-1 text-danger"
                    x-text="revokedOnPage"
                ></div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="iam-card p-3 h-100">
                <div class="small fw-semibold text-secondary">
                    Current Page Results
                </div>

                <div
                    class="fs-3 fw-bold mt-1"
                    x-text="assignments.length"
                ></div>
            </div>
        </div>
    </section>

    <section class="iam-card p-3 mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-6 col-xl-2">
                <label
                    for="assignment-identity"
                    class="form-label small fw-semibold"
                >
                    Identity ID
                </label>

                <input
                    id="assignment-identity"
                    type="number"
                    min="1"
                    class="form-control"
                    x-model="filters.auth_identity_id"
                >
            </div>

            <div class="col-md-6 col-xl-2">
                <label
                    for="assignment-role"
                    class="form-label small fw-semibold"
                >
                    Role
                </label>

                <input
                    id="assignment-role"
                    type="text"
                    class="form-control"
                    placeholder="supervisor"
                    x-model="filters.role"
                >
            </div>

            <div class="col-md-6 col-xl-2">
                <label
                    for="assignment-company"
                    class="form-label small fw-semibold"
                >
                    Company ID
                </label>

                <input
                    id="assignment-company"
                    type="number"
                    min="1"
                    class="form-control"
                    x-model="filters.company_id"
                >
            </div>

            <div class="col-md-6 col-xl-2">
                <label
                    for="assignment-bu"
                    class="form-label small fw-semibold"
                >
                    Business Unit ID
                </label>

                <input
                    id="assignment-bu"
                    type="number"
                    min="1"
                    class="form-control"
                    x-model="filters.business_unit_id"
                >
            </div>

            <div class="col-md-6 col-xl-2">
                <label
                    for="assignment-system"
                    class="form-label small fw-semibold"
                >
                    System ID
                </label>

                <input
                    id="assignment-system"
                    type="number"
                    min="1"
                    class="form-control"
                    x-model="filters.system_id"
                >
            </div>

            <div class="col-md-6 col-xl-2">
                <label
                    for="assignment-status"
                    class="form-label small fw-semibold"
                >
                    Status
                </label>

                <select
                    id="assignment-status"
                    class="form-select"
                    x-model="filters.status"
                >
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="revoked">Revoked</option>
                </select>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    :disabled="loading"
                    @click="clearFilters()"
                >
                    <i class="bi bi-x-circle me-1"></i>
                    Clear
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    :disabled="loading"
                    @click="search()"
                >
                    <span
                        x-show="loading"
                        class="spinner-border spinner-border-sm me-1"
                    ></span>

                    <i
                        x-show="!loading"
                        class="bi bi-search me-1"
                    ></i>

                    Search
                </button>
            </div>
        </div>
    </section>

    <div
        x-cloak
        x-show="errorMessage"
        class="alert alert-danger"
        role="alert"
    >
        <span x-text="errorMessage"></span>
    </div>

    <div
        x-cloak
        x-show="successMessage"
        class="alert alert-success"
        role="status"
    >
        <i class="bi bi-check-circle me-1"></i>
        <span x-text="successMessage"></span>
    </div>

    <section class="iam-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Identity</th>
                        <th>Role</th>
                        <th>Company</th>
                        <th>Business Unit</th>
                        <th>System</th>
                        <th>Status</th>
                        <th>Validity</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td
                                colspan="9"
                                class="text-center py-5 text-secondary"
                            >
                                <div
                                    class="spinner-border spinner-border-sm me-2"
                                ></div>

                                Loading assignments...
                            </td>
                        </tr>
                    </template>

                    <template
                        x-if="!loading && assignments.length === 0"
                    >
                        <tr>
                            <td
                                colspan="9"
                                class="text-center py-5 text-secondary"
                            >
                                No contextual role assignments found.
                            </td>
                        </tr>
                    </template>

                    <template
                        x-for="assignment in assignments"
                        :key="assignment.id"
                    >
                        <tr>
                            <td
                                class="fw-semibold"
                                x-text="assignment.id"
                            ></td>

                            <td
                                x-text="assignment.auth_identity_id"
                            ></td>

                            <td>
                                <span
                                    class="badge text-bg-primary"
                                    x-text="assignment.role_name"
                                ></span>
                            </td>

                            <td>
                                <div
                                    class="fw-semibold"
                                    x-text="
                                        assignment.company_name
                                            || 'Global'
                                    "
                                ></div>

                                <div
                                    class="small text-secondary"
                                    x-show="assignment.company_id"
                                    x-text="
                                        'ID '
                                        + assignment.company_id
                                    "
                                ></div>
                            </td>

                            <td>
                                <div
                                    class="fw-semibold"
                                    x-text="
                                        assignment.business_unit_name
                                            || 'All business units'
                                    "
                                ></div>

                                <div
                                    class="small text-secondary"
                                    x-show="assignment.business_unit_id"
                                    x-text="
                                        'ID '
                                        + assignment.business_unit_id
                                    "
                                ></div>
                            </td>

                            <td
                                x-text="
                                    assignment.system_name
                                        || 'All systems'
                                "
                            ></td>

                            <td>
                                <span
                                    class="badge"
                                    :class="
                                        assignment.status === 'active'
                                            ? 'text-bg-success'
                                            : 'text-bg-danger'
                                    "
                                    x-text="assignment.status"
                                ></span>
                            </td>

                            <td class="small">
                                <div>
                                    <strong>From:</strong>
                                    <span
                                        x-text="
                                            formatDate(
                                                assignment.valid_from
                                            )
                                        "
                                    ></span>
                                </div>

                                <div>
                                    <strong>Until:</strong>
                                    <span
                                        x-text="
                                            formatDate(
                                                assignment.valid_until
                                            )
                                        "
                                    ></span>
                                </div>
                            </td>

                            <td class="text-end">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    @click="openDetails(assignment)"
                                >
                                    <i class="bi bi-eye me-1"></i>
                                    Details
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <footer
            class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 border-top p-3"
        >
            <div class="small text-secondary">
                Page
                <span x-text="meta.current_page"></span>
                of
                <span x-text="meta.last_page"></span>
            </div>

            <div class="d-flex gap-2">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    :disabled="
                        loading
                        || meta.current_page <= 1
                    "
                    @click="goToPage(meta.current_page - 1)"
                >
                    Previous
                </button>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    :disabled="
                        loading
                        || meta.current_page >= meta.last_page
                    "
                    @click="goToPage(meta.current_page + 1)"
                >
                    Next
                </button>
            </div>
        </footer>
    </section>

    <template x-if="createModalOpen">
        <div>
            @include(
                'iam-v2.role-assignments.partials.create-modal'
            )
        </div>
    </template>

    <template x-if="selectedAssignment !== null">
        <div>
    <div
        class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3"
        style="
            z-index: 2000;
            background: rgba(15, 23, 42, .55);
        "
        @keydown.escape.window="closeDetails()"
        @click.self="closeDetails()"
    >
        <section
            class="iam-card w-100"
            style="max-width: 720px;"
        >
            <header
                class="d-flex align-items-center justify-content-between border-bottom p-3"
            >
                <div>
                    <h2 class="h5 fw-bold mb-1">
                        Assignment Details
                    </h2>

                    <div
                        class="small text-secondary"
                        x-text="
                            selectedAssignment
                                ? 'Assignment #'
                                    + selectedAssignment.id
                                : ''
                        "
                    ></div>
                </div>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    @click="closeDetails()"
                >
                    <i class="bi bi-x-lg"></i>
                </button>
            </header>

            <div
                class="p-3"
                x-show="selectedAssignment"
            >
                <div class="row g-3">
                    <template
                        x-for="detail in details"
                        :key="detail.label"
                    >
                        <div class="col-md-6">
                            <div
                                class="small fw-semibold text-secondary"
                                x-text="detail.label"
                            ></div>

                            <div
                                class="fw-semibold mt-1 text-break"
                                x-text="detail.value"
                            ></div>
                        </div>
                    </template>
                </div>
            </div>
        </section>
    </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
    function roleAssignmentPage(config) {
        return {
            endpoint: config.endpoint,
            storeEndpoint: config.storeEndpoint,
            catalogueEndpoint: config.catalogueEndpoint,
            activeContextsEndpoint:
                config.activeContextsEndpoint,
            activeContextUpdateEndpoint:
                config.activeContextUpdateEndpoint,
            csrfToken: config.csrfToken,

            contextLoading: false,
            contextSwitching: false,
            contextError: '',
            activeContexts: [],
            selectedContextKey: '',
            activeContextKey: '',

            loading: false,
            catalogueLoading: false,
            submitting: false,

            errorMessage: '',
            successMessage: '',
            formError: '',

            assignments: [],
            selectedAssignment: null,
            createModalOpen: false,

            catalogue: {
                identities: [],
                roles: [],
                companies: [],
                business_units: [],
                systems: [],
            },

            form: {
                auth_identity_id: '',
                role: '',
                company_id: '',
                business_unit_id: '',
                system_id: '',
                valid_from: '',
                valid_until: '',
            },

            filters: {
                auth_identity_id: '',
                role: '',
                company_id: '',
                business_unit_id: '',
                system_id: '',
                status: '',
            },

            meta: {
                current_page: 1,
                per_page: 25,
                total: 0,
                last_page: 1,
            },

            get filteredBusinessUnits() {
                const companyId = Number(
                    this.form.company_id
                );

                if (!companyId) {
                    return [];
                }

                return this.catalogue.business_units.filter(
                    businessUnit =>
                        Number(businessUnit.company_id)
                            === companyId
                );
            },

            get canSubmit() {
                return Boolean(
                    this.form.auth_identity_id
                    && this.form.role
                );
            },

            get activeOnPage() {
                return this.assignments.filter(
                    assignment =>
                        assignment.status === 'active'
                ).length;
            },

            get revokedOnPage() {
                return this.assignments.filter(
                    assignment =>
                        assignment.status === 'revoked'
                ).length;
            },

            get details() {
                if (!this.selectedAssignment) {
                    return [];
                }

                const item = this.selectedAssignment;

                return [
                    {
                        label: 'Identity ID',
                        value: item.auth_identity_id,
                    },
                    {
                        label: 'Role',
                        value: item.role_name,
                    },
                    {
                        label: 'Company',
                        value:
                            item.company_name
                            || 'Global',
                    },
                    {
                        label: 'Business Unit',
                        value:
                            item.business_unit_name
                            || 'All business units',
                    },
                    {
                        label: 'System',
                        value:
                            item.system_name
                            || 'All systems',
                    },
                    {
                        label: 'Status',
                        value: item.status,
                    },
                    {
                        label: 'Valid From',
                        value: this.formatDate(
                            item.valid_from
                        ),
                    },
                    {
                        label: 'Valid Until',
                        value: this.formatDate(
                            item.valid_until
                        ),
                    },
                    {
                        label: 'Created At',
                        value: this.formatDate(
                            item.created_at
                        ),
                    },
                    {
                        label: 'Updated At',
                        value: this.formatDate(
                            item.updated_at
                        ),
                    },
                ];
            },

            contextKey(context) {
                return [
                    context.company_id,
                    context.business_unit_id,
                    context.system_id
                ].join(':');
            },

            contextFromKey(key) {
                return this.activeContexts.find(
                    context =>
                        this.contextKey(context) === key
                ) ?? null;
            },

            async loadActiveContexts() {
                this.contextLoading = true;
                this.contextError = '';

                try {
                    const response = await fetch(
                        this.activeContextsEndpoint,
                        {
                            headers: {
                                Accept: 'application/json'
                            }
                        }
                    );

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            payload.message
                            ?? 'Unable to load active contexts.'
                        );
                    }

                    this.activeContexts =
                        Array.isArray(payload.data)
                            ? payload.data
                            : [];

                    const active = payload.active ?? {};

                    if (
                        active.company_id
                        && active.business_unit_id
                        && active.system_id
                    ) {
                        this.activeContextKey = [
                            active.company_id,
                            active.business_unit_id,
                            active.system_id
                        ].join(':');

                        this.selectedContextKey =
                            this.activeContextKey;
                    }
                } catch (error) {
                    this.contextError =
                        error.message
                        ?? 'Unable to load active contexts.';
                } finally {
                    this.contextLoading = false;
                }
            },

            async activateSelectedContext() {
                const context = this.contextFromKey(
                    this.selectedContextKey
                );

                if (!context) {
                    this.contextError =
                        'Select an authorized IAM context.';
                    return;
                }

                this.contextSwitching = true;
                this.contextError = '';

                try {
                    const response = await fetch(
                        this.activeContextUpdateEndpoint,
                        {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'Content-Type':
                                    'application/json',
                                'X-CSRF-TOKEN':
                                    this.csrfToken
                            },
                            body: JSON.stringify({
                                company_id:
                                    context.company_id,
                                business_unit_id:
                                    context.business_unit_id,
                                system_id:
                                    context.system_id
                            })
                        }
                    );

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            payload.error
                            ?? payload.message
                            ?? 'Unable to activate context.'
                        );
                    }

                    this.activeContextKey =
                        this.selectedContextKey;

                    window.location.reload();
                } catch (error) {
                    this.contextError =
                        error.message
                        ?? 'Unable to activate context.';
                } finally {
                    this.contextSwitching = false;
                }
            },

            async initialize() {
                await this.loadActiveContexts();
                await Promise.all([
                    this.load(),
                    this.loadCatalogue(),
                ]);
            },

            async loadCatalogue() {
                this.catalogueLoading = true;
                this.formError = '';

                try {
                    const response = await fetch(
                        this.catalogueEndpoint,
                        {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        }
                    );

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            payload.error
                            || payload.message
                            || 'Unable to load assignment options.'
                        );
                    }

                    this.catalogue = {
                        identities:
                            Array.isArray(payload.identities)
                                ? payload.identities
                                : [],
                        roles:
                            Array.isArray(payload.roles)
                                ? payload.roles
                                : [],
                        companies:
                            Array.isArray(payload.companies)
                                ? payload.companies
                                : [],
                        business_units:
                            Array.isArray(payload.business_units)
                                ? payload.business_units
                                : [],
                        systems:
                            Array.isArray(payload.systems)
                                ? payload.systems
                                : [],
                    };
                } catch (error) {
                    this.formError =
                        error instanceof Error
                            ? error.message
                            : 'Unable to load assignment options.';
                } finally {
                    this.catalogueLoading = false;
                }
            },

            openCreateModal() {
                this.formError = '';
                this.successMessage = '';
                this.createModalOpen = true;
            },

            closeCreateModal() {
                if (this.submitting) {
                    return;
                }

                this.createModalOpen = false;
                this.formError = '';
                this.resetForm();
            },

            resetForm() {
                this.form = {
                    auth_identity_id: '',
                    role: '',
                    company_id: '',
                    business_unit_id: '',
                    system_id: '',
                    valid_from: '',
                    valid_until: '',
                };
            },

            async submitAssignment() {
                if (!this.canSubmit || this.submitting) {
                    return;
                }

                this.submitting = true;
                this.formError = '';
                this.successMessage = '';

                const payload = {};

                Object.entries(this.form).forEach(
                    ([key, value]) => {
                        const normalized =
                            String(value ?? '').trim();

                        if (normalized !== '') {
                            payload[key] = normalized;
                        }
                    }
                );

                try {
                    const response = await fetch(
                        this.storeEndpoint,
                        {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'Content-Type':
                                    'application/json',
                                'X-CSRF-TOKEN':
                                    this.csrfToken,
                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(payload),
                        }
                    );

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            result.error
                            || result.message
                            || 'Unable to save role assignment.'
                        );
                    }

                    this.createModalOpen = false;
                    this.resetForm();

                    this.successMessage =
                        result.message
                        || 'Role assignment saved successfully.';

                    await this.load(1);
                } catch (error) {
                    this.formError =
                        error instanceof Error
                            ? error.message
                            : 'Unable to save role assignment.';
                } finally {
                    this.submitting = false;
                }
            },

            async load(page = 1) {
                this.loading = true;
                this.errorMessage = '';

                const query = new URLSearchParams();

                Object.entries(this.filters).forEach(
                    ([key, value]) => {
                        const normalized =
                            String(value ?? '').trim();

                        if (normalized !== '') {
                            query.set(key, normalized);
                        }
                    }
                );

                query.set('page', String(page));
                query.set(
                    'per_page',
                    String(this.meta.per_page)
                );

                try {
                    const response = await fetch(
                        `${this.endpoint}?${query.toString()}`,
                        {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        }
                    );

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            payload.error
                            || payload.message
                            || 'Unable to load assignments.'
                        );
                    }

                    this.assignments =
                        Array.isArray(payload.data)
                            ? payload.data
                            : [];

                    this.meta = {
                        ...this.meta,
                        ...payload.meta,
                    };
                } catch (error) {
                    this.assignments = [];
                    this.meta.total = 0;

                    this.errorMessage =
                        error instanceof Error
                            ? error.message
                            : 'Unable to load assignments.';
                } finally {
                    this.loading = false;
                }
            },

            search() {
                this.load(1);
            },

            clearFilters() {
                this.filters = {
                    auth_identity_id: '',
                    role: '',
                    company_id: '',
                    business_unit_id: '',
                    system_id: '',
                    status: '',
                };

                this.load(1);
            },

            goToPage(page) {
                if (
                    page < 1
                    || page > this.meta.last_page
                ) {
                    return;
                }

                this.load(page);
            },

            openDetails(assignment) {
                this.selectedAssignment = assignment;
            },

            closeDetails() {
                this.selectedAssignment = null;
            },

            formatDate(value) {
                if (!value) {
                    return 'No limit';
                }

                const date = new Date(value);

                if (Number.isNaN(date.getTime())) {
                    return value;
                }

                return date.toLocaleString();
            },
        };
    }
</script>
@endpush
