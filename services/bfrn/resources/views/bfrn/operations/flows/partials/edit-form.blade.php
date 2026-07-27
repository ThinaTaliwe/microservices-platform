<form x-ref="editFlowForm" @submit.prevent="submitEditFlow($refs.editFlowForm)">
    @csrf
    <input type="hidden" name="_method" value="PATCH">

    <div x-show="editError" class="alert alert-danger" x-cloak>
        <i class="bi bi-exclamation-triangle me-1"></i>
        <span x-text="editError"></span>
    </div>

    <div x-show="editSuccess" class="alert alert-success" x-cloak>
        <i class="bi bi-check-circle me-1"></i>
        <span x-text="editSuccess"></span>
    </div>

    <input type="hidden" name="bu_id" :value="editForm.bu_id">
    <input type="hidden" name="shipment_type_id" :value="editForm.shipment_type_id">
    <input type="hidden" name="mode_of_transport_id" :value="editForm.mode_of_transport_id">

    <div class="row g-3">

        <div class="col-12">
            <h3 class="h6 fw-bold mb-0">Shipment Details</h3>
        </div>

        <div class="col-md-4">
            <div class="border rounded-3 p-3 bg-light mb-2">
                <div class="text-muted small">Current Business Unit</div>
                <div class="fw-semibold" x-text="businessUnitName(editForm.bu_id)"></div>
                {{-- <div class="text-muted small">
                    ID: <span x-text="editForm.bu_id || '-'"></span>
                </div> --}}
            </div>

            @include('bfrn.components.searchable-select', [
                'label' => 'Change Business Unit',
                'name' => 'edit_bu_id',
                'endpoint' => '/bfrn/api/bu',
                'valueField' => 'id',
                'labelField' => 'bu_name',
                'placeholder' => 'Search business units...',
                'required' => false,
            ])
        </div>

        <div class="col-md-4">
            <div class="border rounded-3 p-3 bg-light mb-2">
                <div class="text-muted small">Current Shipment Type</div>
                <div class="fw-semibold" x-text="shipmentTypeName(editForm.shipment_type_id)"></div>
                {{-- <div class="text-muted small">
                    ID: <span x-text="editForm.shipment_type_id || '-'"></span>
                </div> --}}
            </div>

            @include('bfrn.components.searchable-select', [
                'label' => 'Change Shipment Type',
                'name' => 'edit_shipment_type_id',
                'endpoint' => '/bfrn/api/shipments/shipment-types',
                'valueField' => 'id',
                'labelField' => 'name',
                'placeholder' => 'Search shipment types...',
                'required' => false,
            ])
        </div>

        <div class="col-md-4">
            <div class="border rounded-3 p-3 bg-light mb-2">
                <div class="text-muted small">Current Mode Of Transport</div>
                <div class="fw-semibold" x-text="transportModeName(editForm.mode_of_transport_id)"></div>
                {{-- <div class="text-muted small">
                    ID: <span x-text="editForm.mode_of_transport_id || '-'"></span>
                </div> --}}
            </div>

            @include('bfrn.components.searchable-select', [
                'label' => 'Change Mode Of Transport',
                'name' => 'edit_mode_of_transport_id',
                'endpoint' => '/bfrn/api/lookups/modes-of-transport',
                'valueField' => 'id',
                'labelField' => 'name',
                'placeholder' => 'Search transport modes...',
                'required' => false,
            ])
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">Shipment Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" maxlength="45" x-model="editForm.name" required>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" class="form-control" rows="1" maxlength="255" x-model="editForm.description"></textarea>
        </div>

                <div class="col-12">
            <hr>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="h6 fw-bold mb-0">
                        <i class="bi bi-box-seam me-2"></i>Edit Shipment Cargo
                    </h3>

                    <div class="text-muted small">
                        Update the products and quantities assigned to this shipment.
                    </div>
                </div>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    @click="addEditItem()"
                >
                    <i class="bi bi-plus-circle me-1"></i>Add Item
                </button>
            </div>

            <div class="d-flex flex-column gap-3">
                <template x-for="(row, index) in editItems" :key="row.key">
                    <div class="row g-2 align-items-end border rounded-3 p-3 mx-0">
                        <div
                            class="col-md-8 position-relative"
                            x-data="searchableSelect({
                                endpoint: '/bfrn/api/lookups/items',
                                valueField: 'id',
                                labelField: 'label',
                                placeholder: 'Search items...',
                                selectedOption: {
                                    id: row.item_id,
                                    label: row.item_label
                                }
                            })"
                            x-init="await loadOptions(); selectedValue = row.item_id; search = row.item_label"
                            x-effect="row.item_id = selectedValue"
                        >
                            <label class="form-label fw-semibold">
                                Cargo Item <span class="text-danger">*</span>
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
                                style="z-index:1060;max-height:240px;"
                            >
                                <template x-if="loading">
                                    <div class="p-3 text-muted small">
                                        Loading items...
                                    </div>
                                </template>

                                <template x-if="error">
                                    <div
                                        class="p-3 text-danger small"
                                        x-text="error"
                                    ></div>
                                </template>

                                <template
                                    x-for="option in filteredOptions()"
                                    :key="option[valueField]"
                                >
                                    <button
                                        type="button"
                                        class="dropdown-item px-3 py-2 text-start w-100 border-0 bg-white"
                                        @click="selectOption(option); row.item_label = option[labelField]"
                                    >
                                        <div
                                            class="fw-semibold"
                                            x-text="option[labelField] || ('Item #' + option[valueField])"
                                        ></div>

                                        <div class="text-muted small">
                                            ID:
                                            <span x-text="option[valueField]"></span>
                                        </div>
                                    </button>
                                </template>

                                <template
                                    x-if="!loading && filteredOptions().length === 0"
                                >
                                    <div class="p-3 text-muted small">
                                        No matching items found.
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
                                title="Remove cargo item"
                                @click="removeEditItem(index)"
                                :disabled="editItems.length === 1"
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
                    <div class="text-muted small">Select a new address only when you want to change it.</div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary" @click="openAddressCreateModal()">
                    <i class="bi bi-plus-circle me-1"></i>Create New Address
                </button>
            </div>
        </div>

        <div class="col-md-6">
            <div class="border rounded-3 p-3 bg-light mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">Current Origin</div>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            @click="openAddressEditModal(editForm.from_address_id)"
                            x-show="editForm.from_address_id">
                        <i>Edit</i>
                    </button>
                </div>
                <div class="fw-semibold" x-text="addressName(editForm.from_address_id)"></div>
                <div class="text-muted small" x-text="addressDetail(editForm.from_address_id)"></div>
            </div>

            @include('bfrn.components.searchable-select', [
                'label' => 'Change Origin Address',
                'name' => 'from_address_id',
                'endpoint' => '/bfrn/api/addresses',
                'valueField' => 'id',
                'labelField' => 'name',
                'placeholder' => 'Search new origin address...',
                'required' => false,
            ])
        </div>

        <div class="col-md-6">
            <div class="border rounded-3 p-3 bg-light mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">Current Destination</div>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            @click="openAddressEditModal(editForm.to_address_id)"
                            x-show="editForm.to_address_id">
                        <i>Edit</i>
                    </button>
                </div>
                <div class="fw-semibold" x-text="addressName(editForm.to_address_id)"></div>
                <div class="text-muted small" x-text="addressDetail(editForm.to_address_id)"></div>
            </div>

            @include('bfrn.components.searchable-select', [
                'label' => 'Change Destination Address',
                'name' => 'to_address_id',
                'endpoint' => '/bfrn/api/addresses',
                'valueField' => 'id',
                'labelField' => 'name',
                'placeholder' => 'Search new destination address...',
                'required' => false,
            ])
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary" @click="editModalOpen = false" :disabled="updatingFlow">
            Cancel
        </button>

        <button type="submit" class="btn btn-primary" :disabled="updatingFlow">
            <span x-show="!updatingFlow">
                <i class="bi bi-check-circle me-1"></i>Save Changes
            </span>
            <span x-show="updatingFlow">
                <i class="bi bi-hourglass-split me-1"></i>Saving...
            </span>
        </button>
    </div>
</form>
