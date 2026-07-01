<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BFRN Operations')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display:none !important; }

        body {
            background:#f8fafc;
            color:#0f172a;
            font-family:system-ui,-apple-system,"Segoe UI",sans-serif;
        }

        .ops-shell {
            min-height:100vh;
            display:grid;
            grid-template-columns:260px 1fr;
            transition:grid-template-columns .2s ease;
        }

        .ops-shell.sidebar-collapsed {
            grid-template-columns:78px 1fr;
        }

        .ops-sidebar {
            background:#fff;
            border-right:1px solid #e2e8f0;
            padding:1rem;
            position:sticky;
            top:0;
            height:100vh;
            overflow-y:auto;
        }

        .ops-main {
            padding:1.5rem;
            min-width:0;
        }

        .ops-logo-wrap {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:.75rem;
            margin-bottom:1.5rem;
        }

        .ops-logo {
            display:flex;
            align-items:center;
            gap:.6rem;
            font-weight:800;
            color:#2563eb;
            white-space:nowrap;
        }

        .ops-logo-icon {
            width:36px;
            height:36px;
            border-radius:.75rem;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            background:#eef2ff;
            color:#2563eb;
        }

        .ops-toggle {
            border:1px solid #e2e8f0;
            background:#fff;
            color:#334155;
            border-radius:.65rem;
            width:36px;
            height:36px;
        }

        .ops-nav-section {
            margin-top:1rem;
            margin-bottom:.4rem;
            color:#94a3b8;
            font-size:.72rem;
            text-transform:uppercase;
            letter-spacing:.08em;
            font-weight:700;
        }

        .ops-nav-link {
            display:flex;
            gap:.75rem;
            align-items:center;
            padding:.65rem .8rem;
            border-radius:.6rem;
            color:#64748b;
            text-decoration:none;
            margin-bottom:.25rem;
            white-space:nowrap;
        }

        .ops-nav-link i {
            font-size:1.05rem;
            min-width:22px;
            text-align:center;
        }

        .ops-nav-link:hover,
        .ops-nav-link.active {
            background:#eef2ff;
            color:#2563eb;
        }

        .sidebar-collapsed .ops-logo-text,
        .sidebar-collapsed .ops-nav-label,
        .sidebar-collapsed .ops-nav-section {
            display:none;
        }

        .sidebar-collapsed .ops-logo-wrap {
            justify-content:center;
            flex-direction:column;
        }

        .sidebar-collapsed .ops-nav-link {
            justify-content:center;
            padding:.75rem;
        }

        .ops-topbar {
            background:#fff;
            border:1px solid #e2e8f0;
            border-radius:.9rem;
            padding:.85rem 1rem;
            margin-bottom:1rem;
            box-shadow:0 1px 3px rgba(15,23,42,.08);
        }

        .ops-card {
            background:#fff;
            border:1px solid #e2e8f0;
            border-radius:.9rem;
            box-shadow:0 1px 3px rgba(15,23,42,.08);
        }

        @media (max-width:992px) {
            .ops-shell,
            .ops-shell.sidebar-collapsed {
                grid-template-columns:1fr;
            }

            .ops-sidebar {
                position:fixed;
                z-index:1200;
                width:260px;
                transform:translateX(-100%);
                transition:transform .2s ease;
            }

            .ops-shell.mobile-open .ops-sidebar {
                transform:translateX(0);
            }

            .ops-main {
                padding:1rem;
            }
        }


        .sidebar-collapsed .ops-user-card {
            display:none;
        }

        .ops-collapse-label {
            font-size:.75rem;
            font-weight:600;
        }

        .sidebar-collapsed .ops-collapse-label {
            display:none;
        }

        .ops-sidebar-toggle-wrap {
            display:flex;
            justify-content:flex-end;
            margin-bottom:1rem;
        }

        .sidebar-collapsed .ops-sidebar-toggle-wrap {
            justify-content:center;
        }

        .pac-container {
            z-index: 20000 !important;
        }
    </style>

    @stack('styles')

<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.min.css">

</head>
<body>
<div
    x-data="{
        sidebarCollapsed: localStorage.getItem('bfrn_sidebar_collapsed') === '1',
        mobileSidebarOpen: false,
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('bfrn_sidebar_collapsed', this.sidebarCollapsed ? '1' : '0');
        }
    }"
    class="ops-shell"
    :class="{
        'sidebar-collapsed': sidebarCollapsed,
        'mobile-open': mobileSidebarOpen
    }"
>
    <aside class="ops-sidebar">
        @php
            $canViewDashboard = \App\Support\BfrnPermission::can('dash', 'read');
            $canViewShipments = \App\Support\BfrnPermission::can('ship', 'read');
            $canViewApiHealth = \App\Support\BfrnPermission::can('apihealth', 'read');
            $canViewUserAdmin = \App\Support\BfrnPermission::can('useradmin', 'read');
        @endphp
        <div class="ops-sidebar-toggle-wrap">
            <button type="button" class="ops-toggle" @click="toggleSidebar()" title="Toggle sidebar">
                <i class="bi" :class="sidebarCollapsed ? 'bi-chevron-double-right' : 'bi-chevron-double-left'"></i>
            </button>
        </div>

        @auth
            <div class="ops-user-card p-3 border rounded-3 bg-light mb-3">
                <div class="small text-muted mb-1">Signed in as</div>
                <div class="fw-semibold small text-truncate">{{ auth()->user()->name }}</div>

                <div class="small text-muted mt-2 mb-1">Active Business</div>
                <div class="fw-semibold small text-truncate">
                    {{ session('active_bu_name', 'No BU selected') }}
                </div>

                <a href="/bfrn/select-business-unit?switch=1" class="btn btn-sm btn-outline-primary w-100 mt-3">
                    <i class="bi bi-arrow-repeat me-1"></i>Switch Business
                </a>

                <form method="POST" action="/bfrn/logout" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </button>
                </form>
            </div>
        @endauth

        <div class="ops-nav-section">Management</div>

        @if($canViewDashboard)
            <a href="/bfrn/operations/dashboard"
               class="ops-nav-link {{ request()->routeIs('bfrn.operations.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span class="ops-nav-label">Dashboard</span>
            </a>
        @endif

        @if($canViewShipments)
            <a href="/bfrn/operations/flows"
               class="ops-nav-link {{ request()->routeIs('bfrn.operations.flows.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i>
                <span class="ops-nav-label">Shipments</span>
            </a>

            <a href="/bfrn/operations/addresses"
               class="ops-nav-link {{ request()->routeIs('bfrn.operations.addresses.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i>
                <span class="ops-nav-label">Addresses</span>
            </a>

        @endif

        @if($canViewApiHealth)
            <a href="#"
               class="ops-nav-link {{ request()->routeIs('bfrn.api-health.*') || request()->routeIs('bfrn.api-health.page') ? 'active' : '' }}">
                <i class="bi bi-activity"></i>
                <span class="ops-nav-label">API Health</span>
            </a>
        @endif

        @if($canViewUserAdmin)
            <div class="ops-nav-section">Administration</div>

            <a href="/bfrn/administration/users"
               class="ops-nav-link {{ request()->routeIs('bfrn.administration.users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span class="ops-nav-label">Users</span>
            </a>

            <a href="/bfrn/administration/audits"
               class="ops-nav-link {{ request()->routeIs('bfrn.administration.audits.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i>
                <span class="ops-nav-label">Audit Log</span>
            </a>

        @endif

    </aside>

    <main class="ops-main">
        <div class="ops-topbar justify-content-between align-items-center">
            {{-- <div class="mx-auto"> --}}
            <div>
                <h3 class="fw-bold text-center">@yield('title', 'BFRN Operations')</h3>
            </div>

            <button type="button" class="btn btn-outline-primary d-lg-none" @click="mobileSidebarOpen = true">
                <i class="bi bi-list"></i>
            </button>
        </div>
        @yield('content')
    </main>
</div>


<script>
    function searchableSelect(config) {
        return {
            endpoint: config.endpoint,
            valueField: config.valueField || 'id',
            labelField: config.labelField || 'name',
            placeholder: config.placeholder || 'Search and select...',
            options: [],

            csrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            },

            search: '',
            statusFilter: '',
            selectedValue: '',
            open: false,
            loading: false,
            error: null,

            async loadOptions() {
                this.loading = true;
                this.error = null;

                try {
                    const response = await fetch(this.endpoint, {
                        headers: { 'Accept': 'application/json' }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || data.detail || 'Unable to load options.');
                    }

                    this.options = data.results || data || [];
                } catch (error) {
                    this.error = error.message || 'Unable to load options.';
                    this.options = [];
                } finally {
                    this.loading = false;
                }
            },

            filteredOptions() {
                const term = this.search.toLowerCase().trim();

                if (!term) {
                    return this.options.slice(0, 20);
                }

                return this.options.filter(option => {
                    const label = String(option[this.labelField] || '').toLowerCase();
                    const id = String(option[this.valueField] || '').toLowerCase();
                    return label.includes(term) || id.includes(term);
                }).slice(0, 20);
            },

            selectOption(option) {
                this.selectedValue = option[this.valueField];
                this.search = option[this.labelField] || ('Record #' + option[this.valueField]);
                this.open = false;
            }
        }
    }
</script>


<script>
    function operationsFlowsPage() {
        return {

            csrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            },

            search: '',
            statusFilter: '',
            modalOpen: false,
            creatingFlow: false,
            createError: '',
            createSuccess: '',
            selected: null,
            loadingDetails: false,
            detailError: null,
            parentStack: [],
            documents: [],
            documentsLoading: false,
            documentsError: '',
            documentsSuccess: '',
            uploadingDocument: false,
            relationships: [],
            allRelationships: [],
            relationshipTree: null,
            expandedNodes: {},
            shipmentNameMap: {},
            shipmentMap: {},
            addressNameMap: {},
            addressMap: {},
            buNameMap: {},
            shipmentTypeNameMap: {},
            transportModeNameMap: {},
            relationshipsLoading: false,
            expandedShipmentChildren: {},
            relationshipsError: '',
            childCreateModalOpen: false,
            creatingChildFlow: false,
            childCreateError: '',
            childCreateSuccess: '',
            addressCreateModalOpen: false,
            creatingAddress: false,
            addressCreateError: '',
            addressCreateSuccess: '',
            addressModalMode: 'create',
            editingAddressId: null,
            editingAddressOriginalBu: null,
            activeAddressBuId: '',
            addressForm: {
                bu_id: ''
            },
            editModalOpen: false,
            deleteModalOpen: false,
            deletingFlow: false,
            pendingDelete: null,
            updatingFlow: false,
            editError: '',
            editSuccess: '',
            editForm: {
                id: null,
                name: '',
                description: '',
                bu_id: '',
                shipment_type_id: '',
                mode_of_transport_id: ''
            },



            async loadEditLookupNames() {
                if (
                    Object.keys(this.buNameMap).length > 0 &&
                    Object.keys(this.shipmentTypeNameMap).length > 0 &&
                    Object.keys(this.transportModeNameMap).length > 0
                ) {
                    return;
                }

                const [buResponse, typeResponse, modeResponse] = await Promise.all([
                    fetch('/bfrn/api/bu', { headers: { 'Accept': 'application/json' } }),
                    fetch('/bfrn/api/shipments/shipment-types', { headers: { 'Accept': 'application/json' } }),
                    fetch('/bfrn/api/lookups/modes-of-transport', { headers: { 'Accept': 'application/json' } }),
                ]);

                const [buData, typeData, modeData] = await Promise.all([
                    buResponse.json(),
                    typeResponse.json(),
                    modeResponse.json(),
                ]);

                const buRows = buData.results || buData || [];
                const typeRows = typeData.results || typeData || [];
                const modeRows = modeData.results || modeData || [];

                this.buNameMap = buRows.reduce((map, row) => {
                    map[row.id] = row.bu_name || row.name || ('Business Unit #' + row.id);
                    return map;
                }, {});

                this.shipmentTypeNameMap = typeRows.reduce((map, row) => {
                    map[row.id] = row.name || row.description || ('Shipment Type #' + row.id);
                    return map;
                }, {});

                this.transportModeNameMap = modeRows.reduce((map, row) => {
                    map[row.id] = row.name || row.description || ('Transport Mode #' + row.id);
                    return map;
                }, {});
            },

            businessUnitName(id) {
                return id ? (this.buNameMap[id] || ('Business Unit #' + id)) : '-';
            },

            shipmentTypeName(id) {
                return id ? (this.shipmentTypeNameMap[id] || ('Shipment Type #' + id)) : '-';
            },

            transportModeName(id) {
                return id ? (this.transportModeNameMap[id] || ('Transport Mode #' + id)) : '-';
            },

            async openEditShipmentById(id) {
                try {
                    const response = await fetch(`/bfrn/api/shipments/${id}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || data.detail || 'Unable to load shipment.');
                    }

                    if (data.shipment_instruction) {
                        try {
                            const instructionResponse = await fetch(`/bfrn/api/shipments/shipment-instructions/${data.shipment_instruction}`, {
                                headers: { 'Accept': 'application/json' }
                            });

                            const instruction = await instructionResponse.json();

                            if (instructionResponse.ok) {
                                data.shipment_reference = instruction.instruction_reference || null;
                                data.from_address = instruction.from_address || null;
                                data.to_address = instruction.to_address || null;
                            }
                        } catch (e) {}
                    }

                    await this.loadAddressNameMap();
                    await this.loadEditLookupNames();
                    this.openEditFlow(data);
                } catch (error) {
                    alert(error.message || 'Unable to open shipment for editing.');
                }
            },

            async openEditFlow(shipment) {
                await this.loadEditLookupNames();

                this.editError = '';
                this.editSuccess = '';
                this.editForm = {
                    id: shipment.id,
                    name: shipment.name || '',
                    description: shipment.description || '',
                    bu_id: shipment.bu || '',
                    shipment_type_id: shipment.shipment_type || '',
                    mode_of_transport_id: shipment.mode_of_transport || '',
                    from_address_id: shipment.from_address || '',
                    to_address_id: shipment.to_address || ''
                };
                this.editModalOpen = true;
            },


            async openDeleteConfirm(shipment) {
                this.pendingDelete = shipment;
                this.deleteError = '';
                this.deleteBlocked = false;
                this.deleteChildCount = 0;
                this.deleteRelationshipId = null;
                this.deleteIsChild = false;

                try {
                    const response = await fetch('/bfrn/api/shipments/relationships/parent-child', {
                        headers: { 'Accept': 'application/json' }
                    });

                    const data = await response.json();
                    const rows = data.results || data || [];

                    const childLinks = rows.filter(rel =>
                        Number(rel.parent_shipment) === Number(shipment.id)
                    );

                    const parentLink = rows.find(rel =>
                        Number(rel.child_shipment) === Number(shipment.id)
                    );

                    if (childLinks.length > 0) {
                        this.deleteBlocked = true;
                        this.deleteChildCount = childLinks.length;
                        this.deleteError = `This shipment has ${childLinks.length} child shipment(s). Delete or unlink the child shipment(s) first.`;
                        this.deleteModalOpen = true;
                        return;
                    }

                    if (parentLink) {
                        this.deleteIsChild = true;
                        this.deleteRelationshipId = parentLink.id;
                        this.deleteError = 'This shipment is linked as a child shipment. If you continue, the app will unlink it from its parent first, then delete the shipment.';
                    }
                } catch (e) {
                    this.deleteBlocked = true;
                    this.deleteError = 'Unable to verify shipment relationships. Delete blocked for safety.';
                    this.deleteModalOpen = true;
                    return;
                }

                this.deleteModalOpen = true;
            },

            async confirmDeleteFlow() {
                if (this.deleteBlocked) {
                    return;
                }

                if (!this.pendingDelete?.id) {
                    return;
                }

                this.deletingFlow = true;

                try {
                    if (this.deleteIsChild && this.deleteRelationshipId) {
                        const unlinkResponse = await fetch(`/bfrn/api/shipments/relationships/parent-child/${this.deleteRelationshipId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken(),
                                'Accept': 'application/json'
                            }
                        });

                        if (!unlinkResponse.ok && unlinkResponse.status !== 204) {
                            let unlinkResult = {};
                            try { unlinkResult = await unlinkResponse.json(); } catch (e) {}
                            throw new Error(unlinkResult.message || unlinkResult.detail || 'Unable to unlink child shipment.');
                        }
                    }

                    const response = await fetch(`/bfrn/operations/flows/${this.pendingDelete.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken(),
                            'Accept': 'application/json'
                        }
                    });

                    let result = {};
                    try {
                        result = await response.json();
                    } catch (e) {}

                    if (!response.ok) {
                        throw new Error(result.error || result.message || 'Unable to delete flow.');
                    }

                    this.deleteModalOpen = false;

                    const deletedShipmentId = this.pendingDelete?.id;
                    this.pendingDelete = null;

                    if (this.selected?.id && Number(this.selected.id) !== Number(deletedShipmentId)) {
                        await this.loadRelationships(this.selected.id);
                        await this.loadDocuments(this.selected.id);
                    } else {
                        window.location.reload();
                    }
                } catch (error) {
                    alert(error.message || 'Unable to delete flow.');
                } finally {
                    this.deletingFlow = false;
                }
            },

            async submitEditFlow(form) {
                this.editError = '';
                this.editSuccess = '';
                this.updatingFlow = true;

                try {
                    const formData = new FormData(form);
                    formData.set('_token', this.csrfToken());

                    Object.entries(this.childPrefill || {}).forEach(([key, value]) => {
                        if (!formData.get(key) && value !== undefined && value !== null && value !== '') {
                            formData.set(key, value);
                        }
                    });

                    const changedBu = formData.get('edit_bu_id');
                    const changedShipmentType = formData.get('edit_shipment_type_id');
                    const changedMode = formData.get('edit_mode_of_transport_id');

                    formData.set('bu_id', changedBu || this.editForm.bu_id);
                    formData.set('shipment_type_id', changedShipmentType || this.editForm.shipment_type_id);
                    formData.set('mode_of_transport_id', changedMode || this.editForm.mode_of_transport_id);

                    const response = await fetch(`/bfrn/operations/flows/${this.editForm.id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken(),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.error || result.message || 'Unable to update flow.');
                    }

                    this.editSuccess = 'Flow updated successfully. Refreshing records...';

                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);

                } catch (error) {
                    this.editError = error.message || 'Unable to update flow.';
                } finally {
                    this.updatingFlow = false;
                }
            },




            openAddressCreateModal() {
                this.addressModalMode = 'create';
                this.editingAddressId = null;
                this.editingAddressOriginalBu = null;
                this.addressCreateError = '';
                this.addressCreateSuccess = '';

                const buInput = document.querySelector('input[name="bu_id"]');

                if (buInput && buInput.value) {
                    this.activeAddressBuId = buInput.value;
                    this.addressForm.bu_id = buInput.value;
                }

                if (!this.activeAddressBuId) {
                    this.addressCreateError = 'Please select a Business Unit first before creating an address.';
                }

                this.addressCreateModalOpen = true;

                console.log('BFRN Google address modal opened');
                setTimeout(() => window.bfrnSetupGoogleAddressSearch && window.bfrnSetupGoogleAddressSearch(), 500);
            },


            async openAddressEditModal(addressId) {
                if (!addressId) {
                    alert('No address selected to edit.');
                    return;
                }

                this.addressModalMode = 'edit';
                this.editingAddressId = addressId;
                this.addressCreateError = '';
                this.addressCreateSuccess = '';
                this.addressCreateModalOpen = true;

                try {
                    const response = await fetch(`/bfrn/api/addresses/${addressId}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || data.detail || 'Unable to load address.');
                    }

                    setTimeout(() => {
                        const form = this.$refs.createAddressForm;
                        if (!form) return;

                        Object.entries(data).forEach(([key, value]) => {
                            const input = form.querySelector(`[name="${key}"]`);
                            if (input && value !== null) {
                                input.value = value;
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                                input.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });

                        this.editingAddressOriginalBu = data.bu || null;
                        this.activeAddressBuId = data.bu || this.activeAddressBuId || '';
                    }, 100);

                } catch (error) {
                    this.addressCreateError = error.message || 'Unable to load address.';
                }
            },

            async submitCreateAddress(form) {
                this.addressCreateError = '';
                this.addressCreateSuccess = '';
                this.creatingAddress = true;

                try {
                    if (!this.activeAddressBuId) {
                        throw new Error('Please select a Business Unit first before creating an address.');
                    }

                    const formData = new FormData(form);
                    formData.set('_token', this.csrfToken());
                    formData.set('adress_type', '1');
                    formData.set(
                        'bu',
                        this.addressModalMode === 'edit'
                            ? (this.editingAddressOriginalBu || this.activeAddressBuId)
                            : this.activeAddressBuId
                    );

                    const url = this.addressModalMode === 'edit'
                        ? `/bfrn/api/addresses/${this.editingAddressId}`
                        : '/bfrn/api/addresses';

                    const isEditAddress = this.addressModalMode === 'edit';
                    const addressPayload = Object.fromEntries(formData.entries());

                    const response = await fetch(url, {
                        method: isEditAddress ? 'PUT' : 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken(),
                            'Accept': 'application/json',
                            ...(isEditAddress ? { 'Content-Type': 'application/json' } : {})
                        },
                        body: isEditAddress ? JSON.stringify(addressPayload) : formData
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.error || result.message || JSON.stringify(result) || 'Unable to create address.');
                    }

                    this.addressCreateSuccess = this.addressModalMode === 'edit'
                        ? 'Address updated successfully.'
                        : 'Address created successfully. You can now search it in the Origin/Destination dropdowns.';

                    window.dispatchEvent(new CustomEvent('bfrn-address-created', {
                        detail: result
                    }));

                    if (this.addressModalMode === 'edit') {
                        this.addressNameMap = {};
                        this.addressMap = {};
                        await this.loadAddressNameMap();

                        if (this.editModalOpen && this.editForm?.id) {
                            this.editForm.from_address_label = this.addressName(this.editForm.from_address_id);
                            this.editForm.to_address_label = this.addressName(this.editForm.to_address_id);
                        }
                    }

                    setTimeout(() => {
                        this.addressCreateModalOpen = false;
                    }, 1200);

                } catch (error) {
                    this.addressCreateError = error.message || 'Unable to create address.';
                } finally {
                    this.creatingAddress = false;
                }
            },

            openChildCreateModal() {
                this.childCreateError = '';
                this.childCreateSuccess = '';
                this.childCreateModalOpen = true;
            },



            async submitCreateChildFlow(form) {
                if (!this.selected?.id) {
                    this.childCreateError = 'No parent shipment selected.';
                    return;
                }

                this.childCreateError = '';
                this.childCreateSuccess = '';
                this.creatingChildFlow = true;

                try {
                    const formData = new FormData(form);
                    formData.set('_token', this.csrfToken());


                    const response = await fetch(`/bfrn/operations/flows/${this.selected.id}/children`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken(),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.error || result.message || 'Unable to create child shipment.');
                    }

                    this.childCreateSuccess = 'Child shipment created successfully. Refreshing relationships...';

                    setTimeout(async () => {
                        this.childCreateModalOpen = false;
                        await this.openShipment(this.selected.id);
                    }, 1000);

                } catch (error) {
                    this.childCreateError = error.message || 'Unable to create child shipment.';
                } finally {
                    this.creatingChildFlow = false;
                }
            },


            async loadAddressNameMap() {
                if (Object.keys(this.addressNameMap).length > 0) {
                    return;
                }

                const response = await fetch('/bfrn/api/addresses', {
                    headers: { 'Accept': 'application/json' }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || data.detail || 'Unable to load address names.');
                }

                const rows = data.results || data || [];

                this.addressNameMap = rows.reduce((map, address) => {
                    map[address.id] = address.name || ('Address #' + address.id);
                    return map;
                }, {});

                this.addressMap = rows.reduce((map, address) => {
                    map[address.id] = address;
                    return map;
                }, {});
            },


            addressDetail(id) {
                const address = this.addressMap[id];

                if (!address) {
                    return 'No address details available.';
                }

                return [
                    address.line1,
                    address.line2,
                    address.line3,
                    address.suburb,
                    address.ZIP || address.zip,
                    address.district
                ].filter(Boolean).join(', ') || 'No address details available.';
            },

            addressName(id) {
                return id ? (this.addressNameMap[id] || ('Address #' + id)) : '-';
            },

            async loadShipmentNameMap() {
                if (Object.keys(this.shipmentNameMap).length > 0) {
                    return;
                }

                const response = await fetch('/bfrn/api/shipments', {
                    headers: { 'Accept': 'application/json' }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || data.detail || 'Unable to load shipment names.');
                }

                const rows = data.results || data || [];

                this.shipmentNameMap = rows.reduce((map, shipment) => {
                    map[shipment.id] = shipment.name || ('Shipment #' + shipment.id);
                    return map;
                }, {});

                this.shipmentMap = rows.reduce((map, shipment) => {
                    map[shipment.id] = shipment;
                    return map;
                }, {});
            },

            shipmentName(id) {
                return this.shipmentNameMap[id] || ('Shipment #' + id);
            },



            childShipmentRecord(rel) {
                if (!rel || !rel.child_shipment) {
                    return {};
                }

                return this.shipmentMap[rel.child_shipment] || {};
            },

            childStatusFromRelationship(rel) {
                const shipment = this.childShipmentRecord(rel);

                return shipment.derived_status || 'Draft';
            },

            childDescriptionFromRelationship(rel) {
                const shipment = this.childShipmentRecord(rel);

                return shipment.description || rel.description || '-';
            },

            childStatusClassFromRelationship(rel) {
                return this.shipmentStatusClass(this.childStatusFromRelationship(rel));
            },

            buildRelationshipTree(rootId) {
                const buildNode = (id) => {
                    const children = this.allRelationships
                        .filter(row => Number(row.parent_shipment) === Number(id))
                        .map(row => buildNode(row.child_shipment));

                    return {
                        id: Number(id),
                        name: this.shipmentName(id),
                        children: children
                    };
                };

                return buildNode(rootId);
            },

            toggleTreeNode(id) {
                this.expandedNodes[id] = !this.expandedNodes[id];
            },

            isTreeNodeExpanded(id) {
                return this.expandedNodes[id] !== false;
            },



            async loadMainListRelationships() {
                try {
                    await this.loadShipmentNameMap();

                    const response = await fetch('/bfrn/api/shipments/relationships/parent-child', {
                        headers: { 'Accept': 'application/json' }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || data.detail || 'Unable to load shipment relationships.');
                    }

                    this.allRelationships = data.results || data || [];
                } catch (error) {
                    console.warn('Unable to load main-list child relationships.', error);
                    this.allRelationships = [];
                }
            },

            toggleShipmentChildren(shipmentId) {
                if (!shipmentId) {
                    return;
                }

                this.expandedShipmentChildren[shipmentId] = !this.expandedShipmentChildren[shipmentId];
            },

            isShipmentChildrenExpanded(shipmentId) {
                return !!this.expandedShipmentChildren[shipmentId];
            },

            childLinksForShipment(shipmentId) {
                const rows = this.allRelationships || [];

                return rows.filter(rel =>
                    Number(rel.parent_shipment) === Number(shipmentId)
                );
            },

            nestedChildLinksForShipment(shipmentId, level = 0, visited = new Set()) {
                if (!shipmentId || visited.has(Number(shipmentId))) {
                    return [];
                }

                visited.add(Number(shipmentId));

                return this.childLinksForShipment(shipmentId).flatMap(rel => {
                    const current = { ...rel, level };

                    return [
                        current,
                        ...this.nestedChildLinksForShipment(rel.child_shipment, level + 1, visited)
                    ];
                });
            },

            childCountForShipment(shipmentId) {
                return this.nestedChildLinksForShipment(shipmentId).length;
            },

            childNameFromRelationship(rel) {
                if (!rel) {
                    return 'Child shipment';
                }

                return rel.child_name ||
                    this.shipmentNameMap[rel.child_shipment] ||
                    ('Shipment #' + rel.child_shipment);
            },

            async loadRelationships(shipmentId) {
                this.relationshipsLoading = true;
                this.relationshipsError = '';
                this.relationships = [];

                try {
                    await this.loadShipmentNameMap();

                    const response = await fetch('/bfrn/api/shipments/relationships/parent-child', {
                        headers: { 'Accept': 'application/json' }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || data.detail || 'Unable to load relationships.');
                    }

                    const rows = data.results || data || [];

                    this.allRelationships = rows;
                    this.relationshipTree = this.buildRelationshipTree(shipmentId);

                    this.relationships = rows
                        .filter(row => {
                            return Number(row.parent_shipment) === Number(shipmentId)
                                || Number(row.child_shipment) === Number(shipmentId);
                        })
                        .map(row => {
                            return {
                                ...row,
                                parent_name: this.shipmentName(row.parent_shipment),
                                child_name: this.shipmentName(row.child_shipment),
                                direction: Number(row.parent_shipment) === Number(shipmentId)
                                    ? 'Parent shipment'
                                    : 'Child shipment'
                            };
                        });

                } catch (error) {
                    this.relationshipsError = error.message || 'Unable to load relationships.';
                    this.relationships = [];
                } finally {
                    this.relationshipsLoading = false;
                }
            },



            publicDocumentUrl(url) {
                if (!url) {
                    return '#';
                }

                let cleanUrl = String(url);

                cleanUrl = cleanUrl
                    .replace('http://siya-app:8000/siya/api', 'http://192.168.1.9:8080/siya/api')
                    .replace('http://siya-app:8000/api', 'http://192.168.1.9:8080/siya/api')
                    .replace('http://localhost:8000/siya/api', 'http://192.168.1.9:8080/siya/api')
                    .replace('http://localhost:8000/api', 'http://192.168.1.9:8080/siya/api')
                    .replace('http://127.0.0.1:8000/siya/api', 'http://192.168.1.9:8080/siya/api')
                    .replace('http://127.0.0.1:8000/api', 'http://192.168.1.9:8080/siya/api')
                    .replace('http://192.168.1.9:8080/siya/siya/api', 'http://192.168.1.9:8080/siya/api');

                return cleanUrl;
            },


            documentExtension(name) {
                return String(name || '').split('.').pop().toLowerCase();
            },

            isImageDocument(doc) {
                return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(this.documentExtension(doc?.name));
            },

            documentTypeLabel(doc) {
                const ext = this.documentExtension(doc?.name);

                return {
                    pdf: 'PDF Document',
                    doc: 'Word Document',
                    docx: 'Word Document',
                    xls: 'Excel Spreadsheet',
                    xlsx: 'Excel Spreadsheet',
                    csv: 'CSV File',
                    txt: 'Text File',
                    zip: 'ZIP Archive',
                    jpg: 'Image File',
                    jpeg: 'Image File',
                    png: 'Image File',
                    gif: 'Image File',
                    webp: 'Image File',
                }[ext] || 'Document';
            },

            documentIcon(doc) {
                const ext = this.documentExtension(doc?.name);

                return {
                    pdf: 'bi-file-earmark-pdf-fill text-danger',
                    doc: 'bi-file-earmark-word-fill text-primary',
                    docx: 'bi-file-earmark-word-fill text-primary',
                    xls: 'bi-file-earmark-excel-fill text-success',
                    xlsx: 'bi-file-earmark-excel-fill text-success',
                    csv: 'bi-file-earmark-spreadsheet-fill text-success',
                    txt: 'bi-file-earmark-text-fill text-secondary',
                    zip: 'bi-file-earmark-zip-fill text-warning',
                    jpg: 'bi-file-earmark-image-fill text-info',
                    jpeg: 'bi-file-earmark-image-fill text-info',
                    png: 'bi-file-earmark-image-fill text-info',
                    gif: 'bi-file-earmark-image-fill text-info',
                    webp: 'bi-file-earmark-image-fill text-info',
                }[ext] || 'bi-file-earmark-fill text-secondary';
            },

            formatFileSize(bytes) {
                const size = Number(bytes || 0);

                if (size >= 1024 * 1024) {
                    return (size / (1024 * 1024)).toFixed(1) + ' MB';
                }

                if (size >= 1024) {
                    return (size / 1024).toFixed(1) + ' KB';
                }

                return size + ' bytes';
            },

            async loadDocuments(shipmentId) {
                this.documentsLoading = true;
                this.documentsError = '';
                this.documentsSuccess = '';

                try {
                    const response = await fetch(`/bfrn/api/shipments/${shipmentId}/documents`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || data.detail || 'Unable to load documents.');
                    }

                    this.documents = data.documents || [];
                } catch (error) {
                    this.documentsError = error.message || 'Unable to load documents.';
                    this.documents = [];
                } finally {
                    this.documentsLoading = false;
                }
            },

            async uploadDocument(event) {
                const file = event.target.files[0];

                if (!file || !this.selected?.id) {
                    return;
                }

                this.uploadingDocument = true;
                this.documentsError = '';
                this.documentsSuccess = '';

                try {
                    const formData = new FormData();
                    formData.set('_token', this.csrfToken());
                    formData.append('file', file);

                    const response = await fetch(`/bfrn/api/shipments/${this.selected.id}/documents`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken(),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || data.detail || 'Unable to upload document.');
                    }

                    this.documentsSuccess = 'Document uploaded successfully.';
                    event.target.value = '';
                    await this.loadDocuments(this.selected.id);
                } catch (error) {
                    this.documentsError = error.message || 'Unable to upload document.';
                } finally {
                    this.uploadingDocument = false;
                }
            },

            async deleteDocument(filename) {
                if (!this.selected?.id || !filename) {
                    return;
                }

                if (!confirm('Delete this document?')) {
                    return;
                }

                this.documentsError = '';
                this.documentsSuccess = '';

                try {
                    const response = await fetch(`/bfrn/api/shipments/${this.selected.id}/documents?filename=${encodeURIComponent(filename)}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken(),
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok && response.status !== 204) {
                        let data = {};
                        try { data = await response.json(); } catch (e) {}
                        throw new Error(data.message || data.detail || 'Unable to delete document.');
                    }

                    this.documentsSuccess = 'Document deleted successfully.';
                    await this.loadDocuments(this.selected.id);
                } catch (error) {
                    this.documentsError = error.message || 'Unable to delete document.';
                }
            },





            previousOperationalStage(stage) {
                return {
                    loading: null,
                    movement: 'loading',
                    offloading: 'movement',
                    storage: 'offloading'
                }[stage] || null;
            },

            canStartStage(shipment, stage) {
                if (!shipment || !shipment[stage] || shipment[`${stage}_started`]) {
                    return false;
                }

                const previous = this.previousOperationalStage(stage);

                if (!previous) {
                    return true;
                }

                return !!shipment[`${previous}_ended`];
            },

            canEndStage(shipment, stage) {
                return shipment &&
                    shipment[stage] &&
                    shipment[`${stage}_started`] &&
                    !shipment[`${stage}_ended`];
            },

            nextStageRequirement(shipment, stage) {
                const previous = this.previousOperationalStage(stage);

                if (!previous) {
                    return '';
                }

                if (shipment && !shipment[`${previous}_ended`]) {
                    return `Finish ${previous} first`;
                }

                return '';
            },

            async updateShipmentStage(stage, action) {
                if (!this.selected?.id) {
                    return;
                }

                const label = action === 'start' ? 'start' : 'finish';

                if (!confirm(`Are you sure you want to ${label} ${stage}?`)) {
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.set('_token', this.csrfToken());
                    formData.set('stage', stage);
                    formData.set('action', action);
                    formData.set('_method', 'PATCH');

                    const response = await fetch(`/bfrn/operations/flows/${this.selected.id}/stage`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken(),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.error || result.message || 'Unable to update stage.');
                    }

                    await this.openShipment(this.selected.id);
                } catch (error) {
                    alert(error.message || 'Unable to update stage.');
                }
            },

            shipmentTimeline(shipment) {
                if (!shipment) {
                    return [];
                }

                return [
                    {
                        label: 'Shipment Created',
                        value: shipment.created_at,
                        icon: 'bi-plus-circle',
                    },
                    {
                        label: 'Loading Started',
                        value: shipment.loading_started,
                        icon: 'bi-box-arrow-in-down',
                    },
                    {
                        label: 'Loading Ended',
                        value: shipment.loading_ended,
                        icon: 'bi-check-circle',
                    },
                    {
                        label: 'Movement Started',
                        value: shipment.movement_started,
                        icon: 'bi-truck',
                    },
                    {
                        label: 'Movement Ended',
                        value: shipment.movement_ended,
                        icon: 'bi-check-circle',
                    },
                    {
                        label: 'Offloading Started',
                        value: shipment.offloading_started,
                        icon: 'bi-box-arrow-up',
                    },
                    {
                        label: 'Offloading Ended',
                        value: shipment.offloading_ended,
                        icon: 'bi-check-circle',
                    },
                    {
                        label: 'Storage Started',
                        value: shipment.storage_started,
                        icon: 'bi-archive',
                    },
                    {
                        label: 'Storage Ended',
                        value: shipment.storage_ended,
                        icon: 'bi-check-circle-fill',
                    },

                ];
            },

            formatTimelineDate(value) {
                if (!value) {
                    return 'Pending';
                }

                try {
                    return new Date(value).toLocaleString();
                } catch (e) {
                    return value;
                }
            },

            timelineItemClass(value) {
                return value
                    ? 'border-success bg-success-subtle'
                    : 'border-secondary bg-light';
            },

            timelineIconClass(value) {
                return value
                    ? 'text-success'
                    : 'text-muted';
            },

            stageStatus(shipment, stage) {
                if (!shipment) {
                    return 'Missing';
                }

                if (stage === 'instruction') {
                    return shipment.shipment_instruction ? 'Linked' : 'Missing';
                }

                if (!shipment[stage]) {
                    return 'Missing';
                }

                if (shipment[`${stage}_ended`]) {
                    return 'Ended';
                }

                if (shipment[`${stage}_started`]) {
                    return 'Started';
                }

                return 'Linked';
            },

            stageStatusClass(status) {
                return {
                    'Missing': 'text-bg-danger',
                    'Linked': 'text-bg-secondary',
                    'Started': 'text-bg-primary',
                    'Ended': 'text-bg-dark'
                }[status] || 'text-bg-light border text-dark';
            },

            shipmentStatus(shipment) {
                if (!shipment) {
                    return 'Draft';
                }

                if (shipment.storage_ended) {
                    return 'Completed';
                }

                if (shipment.storage_started) {
                    return 'Stored';
                }

                if (shipment.offloading_started) {
                    return 'Offloading';
                }

                if (shipment.movement_started) {
                    return 'In Transit';
                }

                if (shipment.loading_started) {
                    return 'Loading';
                }

                return 'Draft';
            },

            shipmentStatusClass(status) {
                return {
                    'Completed': 'text-bg-success',
                    'Stored': 'text-bg-info',
                    'Offloading': 'text-bg-warning',
                    'In Transit': 'text-bg-primary',
                    'Loading': 'text-bg-secondary',
                    'Draft': 'text-bg-light border text-dark'
                }[status] || 'text-bg-light border text-dark';
            },

            async submitCreateFlow(form) {
                this.createError = '';
                this.createSuccess = '';
                this.creatingFlow = true;

                try {
                    const formData = new FormData(form);
                    formData.set('_token', this.csrfToken());

                    const response = await fetch('/bfrn/operations/flows', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken(),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.error || result.message || 'Unable to create shipment.');
                    }

                    this.createSuccess = 'Shipment created successfully. Refreshing records...';

                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);

                } catch (error) {
                    this.createError = error.message || 'Unable to create shipment.';
                } finally {
                    this.creatingFlow = false;
                }
            },


            async goBackToParent() {
                const parent = this.parentStack.pop();

                if (!parent?.id) {
                    return;
                }

                await this.openShipment(parent.id);
            },


            async viewChildShipment(childId) {
                if (this.selected?.id) {
                    this.parentStack.push({
                        id: this.selected.id,
                        name: this.selected.name || ('Shipment #' + this.selected.id)
                    });
                }

                await this.openShipment(childId);
            },

            async goBackToParent() {
                const parent = this.parentStack.pop();

                if (!parent?.id) {
                    return;
                }

                await this.openShipment(parent.id);
            },

            async openShipment(id, fromParent = null) {
                if (fromParent) {
                    this.parentStack.push(fromParent);
                }

                this.loadingDetails = true;
                this.detailError = null;

                try {
                    const response = await fetch(`/bfrn/api/shipments/${id}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || data.detail || 'Unable to load shipment details.');
                    }

                    if (data.shipment_instruction) {
                        try {
                            const instructionResponse = await fetch(`/bfrn/api/shipments/shipment-instructions/${data.shipment_instruction}`, {
                                headers: { 'Accept': 'application/json' }
                            });

                            const instruction = await instructionResponse.json();

                            if (instructionResponse.ok) {
                                data.shipment_reference = instruction.instruction_reference || null;
                                data.from_address = instruction.from_address || null;
                                data.to_address = instruction.to_address || null;
                                data.from_warehouse = instruction.from_warehouse || null;
                                data.to_warehouse = instruction.to_warehouse || null;
                                data.from_location = instruction.from_location || null;
                                data.to_location = instruction.to_location || null;
                            }
                        } catch (e) {}
                    }

                    this.selected = data;
                    await this.loadAddressNameMap();
                    await this.loadDocuments(data.id);
                    await this.loadRelationships(data.id);
                } catch (error) {
                    this.detailError = error.message || 'Unable to load shipment details.';
                } finally {
                    this.loadingDetails = false;
                }
            }
        }
    }
</script>

@stack('scripts')








<script>
window.initBfrnGooglePlaces = function () {
    setTimeout(window.bfrnSetupGoogleAddressSearch, 300);
};

window.bfrnFillAddressFormFromGooglePlace = function (place) {
    const form = document.querySelector('[x-ref="createAddressForm"]');

    if (!form || !place || !place.address_components) return;

    const component = (type) => {
        const found = place.address_components.find(c => c.types.includes(type));
        return found ? found.long_name : '';
    };

    const streetNumber = component('street_number');
    const route = component('route');
    const placeName = place.name || '';
    const formattedAddress = place.formatted_address || '';

    const line1 = [streetNumber, route].filter(Boolean).join(' ') || placeName || formattedAddress;

    const setValue = (name, value) => {
        const input = form.querySelector(`[name="${name}"]`);
        if (input && value !== undefined) {
            input.value = value || '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    setValue('name', placeName || formattedAddress);
    setValue('line1', line1);
    setValue('line2', formattedAddress && formattedAddress !== line1 ? formattedAddress : '');
    setValue('line3', '');
    setValue('suburb', component('sublocality_level_1') || component('sublocality') || component('neighborhood'));
    setValue('city', component('locality') || component('postal_town'));
    setValue('district', component('administrative_area_level_2'));
    setValue('province', component('administrative_area_level_1'));
    setValue('country', component('country'));
    setValue('zip', component('postal_code'));
};

window.bfrnSetupGoogleAddressSearch = function () {
    const input = document.getElementById('bfrn-google-address-search');

    if (!input || !window.google || !google.maps || !google.maps.places) return;
    if (input.dataset.googleReady === '1') return;

    input.dataset.googleReady = '1';

    const autocomplete = new google.maps.places.Autocomplete(input, {
        fields: ['name', 'formatted_address', 'address_components'],
        types: ['geocode'],
          });

    autocomplete.addListener('place_changed', function () {
        window.bfrnFillAddressFormFromGooglePlace(autocomplete.getPlace());
    });

    console.log('BFRN Google address search ready');
};
</script>


@if(config('services.google_maps.api_key'))
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places&loading=async&callback=initBfrnGooglePlaces"
        async
        defer>
    </script>
@endif


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-bfrn-datatable').forEach(function (table) {
        new DataTable(table, {
            responsive: true,
            pageLength: 10,
            searchDelay: 350,
            order: [],
            language: {
                search: '',
                searchPlaceholder: 'Type to filter...',
                lengthMenu: 'Show _MENU_ records',
                emptyTable: 'No records found'
            }
        });
    });
});
</script>

    <x-bfrn.toast-stack />
</body>
</html>
