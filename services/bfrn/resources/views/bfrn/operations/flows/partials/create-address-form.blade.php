<form x-ref="createAddressForm" @submit.prevent="submitCreateAddress($refs.createAddressForm)">
    @csrf

    <div x-show="addressCreateError" class="alert alert-danger" x-cloak>
        <i class="bi bi-exclamation-triangle me-1"></i>
        <span x-text="addressCreateError"></span>
    </div>

    <div x-show="addressCreateSuccess" class="alert alert-success" x-cloak>
        <i class="bi bi-check-circle me-1"></i>
        <span x-text="addressCreateSuccess"></span>
    </div>

    <input type="hidden" name="adress_type" value="1">
    <input type="hidden" name="bu" :value="activeAddressBuId">
    <input type="hidden" name="latitude">
    <input type="hidden" name="longitude">

    <div class="alert alert-info py-2">
        <i class="bi bi-info-circle me-1"></i>
        This address will be linked to Business Unit ID:
        <strong x-text="activeAddressBuId || 'Select a Business Unit first'"></strong>
    </div>

    {{-- <div class="alert alert-primary">
    <label class="form-label fw-semibold">
        Search Address
    </label>

    <input id="google-address-search"
            type="text"
            class="form-control"
            placeholder="Start typing an address...">

        <div class="small text-muted mt-1">
            Select a address to automatically fill the address form.
        </div>
    </div> --}}


    <div class="alert alert-primary">
        <label class="form-label fw-semibold">
            <i class="bi bi-geo-alt-fill me-1"></i>
            Search Address with Google
        </label>

        <input
            id="bfrn-google-address-search"
            type="text"
            class="form-control"
            placeholder="Start typing an address, suburb, building, or street..."
            autocomplete="off"
            @focus="setTimeout(() => window.bfrnSetupGoogleAddressSearch && window.bfrnSetupGoogleAddressSearch(), 300)"
        >

        <div class="small mt-2">
            Search and results will autofill the address fields below. You can still edit everything manually before saving.
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Address Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" maxlength="100" placeholder="Example: Johannesburg Warehouse" required>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">ZIP / Postal Code</label>
            <input type="text" name="zip" class="form-control" maxlength="15" placeholder="Example: 1205">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">Line 1</label>
            <input type="text" name="line1" class="form-control" maxlength="100" placeholder="Street address">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">Line 2</label>
            <input type="text" name="line2" class="form-control" maxlength="100" placeholder="Building, unit, floor">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">Line 3</label>
            <input type="text" name="line3" class="form-control" maxlength="100" placeholder="Optional extra line">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">Suburb</label>
            <input type="text" name="suburb" class="form-control" maxlength="100" placeholder="Suburb">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">P.O. Box</label>
            <input type="text" name="p_o_box" class="form-control" maxlength="45" placeholder="Optional">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">District</label>
            <input type="text" name="district" class="form-control" maxlength="100" placeholder="Optional">
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary" @click="addressCreateModalOpen = false" :disabled="creatingAddress">
            Cancel
        </button>

        <button type="submit" class="btn btn-primary" :disabled="creatingAddress || !activeAddressBuId">
            <span x-show="!creatingAddress">
                <i class="bi me-1" :class="addressModalMode === 'edit' ? 'bi-save' : 'bi-plus-circle'"></i>
                <span x-text="addressModalMode === 'edit' ? 'Save Address Changes' : 'Create Address'"></span>
            </span>
            <span x-show="creatingAddress">
                <i class="bi bi-hourglass-split me-1"></i>Creating...
            </span>
        </button>
    </div>
</form>
