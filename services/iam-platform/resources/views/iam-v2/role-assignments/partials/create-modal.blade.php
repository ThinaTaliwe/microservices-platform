<div
    class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3"
    style="
        z-index: 2100;
        background: rgba(15, 23, 42, .60);
    "
    @keydown.escape.window="closeCreateModal()"
    @click.self="closeCreateModal()"
>
    <section
        class="iam-card w-100"
        style="
            max-width: 820px;
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
        "
        role="dialog"
        aria-modal="true"
        aria-labelledby="create-assignment-title"
    >
        <header
            class="d-flex align-items-center justify-content-between border-bottom p-3"
        >
            <div>
                <h2
                    id="create-assignment-title"
                    class="h5 fw-bold mb-1"
                >
                    Assign Contextual Role
                </h2>

                <div class="small text-secondary">
                    Select an identity, role and the required access scope.
                </div>
            </div>

            <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                :disabled="submitting"
                @click="closeCreateModal()"
                aria-label="Close"
            >
                <i class="bi bi-x-lg"></i>
            </button>
        </header>

        <form
            class="p-3"
            @submit.prevent="submitAssignment()"
        >
            <div
                x-cloak
                x-show="formError"
                class="alert alert-danger"
                role="alert"
            >
                <span x-text="formError"></span>
            </div>

            <div
                x-cloak
                x-show="catalogueLoading"
                class="text-center py-5 text-secondary"
            >
                <span
                    class="spinner-border spinner-border-sm me-2"
                ></span>

                Loading assignment options...
            </div>

            <div
                x-show="!catalogueLoading"
                class="row g-3"
            >
                <div class="col-12">
                    <label
                        for="create-auth-identity"
                        class="form-label fw-semibold"
                    >
                        Identity
                    </label>

                    <select
                        id="create-auth-identity"
                        class="form-select"
                        x-model="form.auth_identity_id"
                        required
                    >
                        <option value="">
                            Select an identity
                        </option>

                        <template
                            x-for="identity in catalogue.identities"
                            :key="identity.id"
                        >
                            <option
                                :value="identity.id"
                                x-text="identity.label"
                            ></option>
                        </template>
                    </select>
                </div>

                <div class="col-md-6">
                    <label
                        for="create-role"
                        class="form-label fw-semibold"
                    >
                        Role
                    </label>

                    <select
                        id="create-role"
                        class="form-select"
                        x-model="form.role"
                        required
                    >
                        <option value="">
                            Select a role
                        </option>

                        <template
                            x-for="role in catalogue.roles"
                            :key="role.id"
                        >
                            <option
                                :value="role.name"
                                x-text="role.label"
                            ></option>
                        </template>
                    </select>
                </div>

                <div class="col-md-6">
                    <label
                        for="create-system"
                        class="form-label fw-semibold"
                    >
                        System
                    </label>

                    <select
                        id="create-system"
                        class="form-select"
                        x-model="form.system_id"
                    >
                        <option value="">
                            All systems
                        </option>

                        <template
                            x-for="system in catalogue.systems"
                            :key="system.id"
                        >
                            <option
                                :value="system.id"
                                x-text="system.name"
                            ></option>
                        </template>
                    </select>
                </div>

                <div class="col-md-6">
                    <label
                        for="create-company"
                        class="form-label fw-semibold"
                    >
                        Company
                    </label>

                    <select
                        id="create-company"
                        class="form-select"
                        x-model="form.company_id"
                        @change="form.business_unit_id = ''"
                    >
                        <option value="">
                            Global scope
                        </option>

                        <template
                            x-for="company in catalogue.companies"
                            :key="company.id"
                        >
                            <option
                                :value="company.id"
                                x-text="company.name"
                            ></option>
                        </template>
                    </select>
                </div>

                <div class="col-md-6">
                    <label
                        for="create-business-unit"
                        class="form-label fw-semibold"
                    >
                        Business Unit
                    </label>

                    <select
                        id="create-business-unit"
                        class="form-select"
                        x-model="form.business_unit_id"
                        :disabled="!form.company_id"
                    >
                        <option value="">
                            All business units
                        </option>

                        <template
                            x-for="businessUnit in filteredBusinessUnits"
                            :key="businessUnit.id"
                        >
                            <option
                                :value="businessUnit.id"
                                x-text="businessUnit.name"
                            ></option>
                        </template>
                    </select>

                    <div class="form-text">
                        Select a company before choosing a business unit.
                    </div>
                </div>

                <div class="col-md-6">
                    <label
                        for="create-valid-from"
                        class="form-label fw-semibold"
                    >
                        Valid From
                    </label>

                    <input
                        id="create-valid-from"
                        type="datetime-local"
                        class="form-control"
                        x-model="form.valid_from"
                    >
                </div>

                <div class="col-md-6">
                    <label
                        for="create-valid-until"
                        class="form-label fw-semibold"
                    >
                        Valid Until
                    </label>

                    <input
                        id="create-valid-until"
                        type="datetime-local"
                        class="form-control"
                        x-model="form.valid_until"
                    >
                </div>
            </div>

            <footer
                class="d-flex justify-content-end gap-2 border-top mt-4 pt-3"
            >
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    :disabled="submitting"
                    @click="closeCreateModal()"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn btn-primary"
                    :disabled="
                        submitting
                        || catalogueLoading
                        || !canSubmit
                    "
                >
                    <span
                        x-show="submitting"
                        class="spinner-border spinner-border-sm me-1"
                    ></span>

                    <i
                        x-show="!submitting"
                        class="bi bi-person-plus me-1"
                    ></i>

                    <span
                        x-text="
                            submitting
                                ? 'Assigning...'
                                : 'Assign Role'
                        "
                    ></span>
                </button>
            </footer>
        </form>
    </section>
</div>
