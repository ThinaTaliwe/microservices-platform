<form x-ref="createFlowForm" @submit.prevent="submitCreateFlow($refs.createFlowForm)">
    @csrf

    <div x-show="createError" class="alert alert-danger" x-cloak>
        <i class="bi bi-exclamation-triangle me-1"></i>
        <span x-text="createError"></span>
    </div>

    <div x-show="createSuccess" class="alert alert-success" x-cloak>
        <i class="bi bi-check-circle me-1"></i>
        <span x-text="createSuccess"></span>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            @include('bfrn.components.searchable-select', [
                'label' => 'Business Unit',
                'name' => 'bu_id',
                'endpoint' => '/bfrn/api/bu',
                'valueField' => 'id',
                'labelField' => 'bu_name',
                'placeholder' => 'Search business units...',
                'required' => true,
                'selectedOption' => [
                    'id' => session('active_bu_id'),
                    'bu_name' => session('active_bu_name'),
                ],
            ])
        </div>

        <div class="col-md-6">
            @include('bfrn.components.searchable-select', [
                'label' => 'Shipment Type',
                'name' => 'shipment_type_id',
                'endpoint' => '/bfrn/api/shipments/shipment-types',
                'valueField' => 'id',
                'labelField' => 'name',
                'placeholder' => 'Search shipment types...',
                'required' => true,
            ])
        </div>

        <div class="col-md-6">
            @include('bfrn.components.searchable-select', [
                'label' => 'Mode Of Transport',
                'name' => 'mode_of_transport_id',
                'endpoint' => '/bfrn/api/lookups/modes-of-transport',
                'valueField' => 'id',
                'labelField' => 'name',
                'placeholder' => 'Search transport modes...',
                'required' => true,
            ])
        </div>

        <div class="col-12">
            <hr>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="h6 fw-bold mb-0">Shipment Items</h3>
                    <div class="text-muted small">
                        Add one or more products and specify the quantity for each item.
                    </div>
                </div>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    @click="addCreateItem()"
                >
                    <i class="bi bi-plus-circle me-1"></i>Add Item
                </button>
            </div>

            <div class="d-flex flex-column gap-3">
                <template x-for="(row, index) in createItems" :key="row.key">
                    <div class="row g-2 align-items-end border rounded-3 p-3 mx-0">
                        <div
                            class="col-md-8 position-relative"
                            x-data="searchableSelect({
                                endpoint: '/bfrn/api/lookups/items',
                                valueField: 'id',
                                labelField: 'label',
                                placeholder: 'Search items...'
                            })"
                            x-init="loadOptions()"
                        >
                            <label class="form-label fw-semibold">
                                Item <span class="text-danger">*</span>
                            </label>

                            <input
                                type="hidden"
                                :name="`items[${index}][item_id]`"
                                x-model="selectedValue"
                                required
                            >

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    x-model="search"
                                    :placeholder="placeholder"
                                    @focus="open = true"
                                    @input="open = true"
                                    @keydown.escape="open = false"
                                    required
                                >
                            </div>

                            <div
                                x-show="open"
                                x-cloak
                                class="position-absolute bg-white border rounded-3 shadow-sm w-100 mt-1 overflow-auto"
                                style="z-index: 1060; max-height: 240px;"
                            >
                                <template x-if="loading">
                                    <div class="p-3 text-muted small">
                                        <i class="bi bi-hourglass-split me-1"></i>Loading options...
                                    </div>
                                </template>

                                <template x-if="error">
                                    <div class="p-3 text-danger small" x-text="error"></div>
                                </template>

                                <template x-for="option in filteredOptions()" :key="option[valueField]">
                                    <button
                                        type="button"
                                        class="dropdown-item px-3 py-2 text-start w-100 border-0 bg-white"
                                        @click="selectOption(option)"
                                    >
                                        <div
                                            class="fw-semibold"
                                            x-text="option[labelField] || ('Record #' + option[valueField])"
                                        ></div>

                                        <div class="text-muted small">
                                            ID: <span x-text="option[valueField]"></span>
                                        </div>
                                    </button>
                                </template>

                                <template x-if="!loading && filteredOptions().length === 0">
                                    <div class="p-3 text-muted small">
                                        No matching records found.
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                Quantity <span class="text-danger">*</span>
                            </label>

                            <input
                                type="number"
                                step="0.000001"
                                min="0.000001"
                                :name="`items[${index}][quantity]`"
                                class="form-control"
                                x-model="row.quantity"
                                required
                            >
                        </div>

                        <div class="col-md-1">
                            <button
                                type="button"
                                class="btn btn-outline-danger w-100"
                                title="Remove item"
                                @click="removeCreateItem(index)"
                                :disabled="createItems.length === 1"
                            >
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>


        <div class="col-12">
            <hr>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="h6 fw-bold mb-0">Origin & Destination</h3>
                    <div class="text-muted small">Optional. Leave blank if the shipment address is not known yet.</div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary" @click="openAddressCreateModal()">
                    <i class="bi bi-plus-circle me-1"></i>Create New Address
                </button>
            </div>
        </div>

        <div class="col-md-6">
            @include('bfrn.components.searchable-select', [
                'label' => 'Origin Address',
                'name' => 'from_address_id',
                'endpoint' => '/bfrn/api/addresses',
                'valueField' => 'id',
                'labelField' => 'name',
                'placeholder' => 'Search origin address...',
                'required' => false,
            ])
        </div>

        <div class="col-md-6">
            @include('bfrn.components.searchable-select', [
                'label' => 'Destination Address',
                'name' => 'to_address_id',
                'endpoint' => '/bfrn/api/addresses',
                'valueField' => 'id',
                'labelField' => 'name',
                'placeholder' => 'Search destination address...',
                'required' => false,
            ])
        </div>


        <div class="col-12">
            <label class="form-label fw-semibold">Shipment Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" maxlength="45" placeholder="Example: Customer Delivery Flow" required>
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" class="form-control" rows="3" maxlength="255" placeholder="Short description for this flow"></textarea>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary" @click="modalOpen = false" :disabled="creatingFlow">
            Cancel
        </button>

        <button type="submit" class="btn btn-primary" :disabled="creatingFlow">
            <span x-show="!creatingFlow">
                <i class="bi bi-check-circle me-1"></i>Create Shipment
            </span>
            <span x-show="creatingFlow">
                <i class="bi bi-hourglass-split me-1"></i>Creating...
            </span>
        </button>
    </div>
</form>
