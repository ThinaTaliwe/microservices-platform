@extends('iam-v2.layouts.administration')

@section('title', 'Contextual Role Assignments')

@section(
    'subtitle',
    'Review roles scoped by company, business unit and system.'
)

@section('content')
<div
    x-data="roleAssignmentPage({
        endpoint: @js($assignmentEndpoint)
    })"
    x-init="load()"
>
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

    <div
        x-cloak
        x-show="selectedAssignment !== null"
        x-transition.opacity
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
@endsection

@push('scripts')
<script>
    function roleAssignmentPage(config) {
        return {
            endpoint: config.endpoint,

            loading: false,
            errorMessage: '',

            assignments: [],
            selectedAssignment: null,

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
