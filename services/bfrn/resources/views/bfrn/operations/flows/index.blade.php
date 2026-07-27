@extends('bfrn.layouts.operations')

@section('title', 'Shipments')

@section('content')
@php
    $canEditShipments = \App\Support\BfrnPermission::can('ship', 'edit');
    $canDeleteShipments = \App\Support\BfrnPermission::can('ship', 'delete');
@endphp
<div x-data="operationsFlowsPage()" x-init="loadMainListRelationships()" @keydown.escape.window="selected = null">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            {{-- <h1 class="h4 fw-bold mb-1">Shipments</h1> --}}
            <div class="text-muted small"></div>
        </div>

        @if($canEditShipments)
            <button class="btn btn-primary" @click="modalOpen = true">
                <i class="bi bi-plus-circle me-1"></i>Create Shipment
            </button>
        @endif
    </div>

    @if($error)
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ $error }}
        </div>
    @endif



<div class="ops-card p-3 p-md-4 mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label fw-semibold">Search Shipments</label>
                <input type="search"
                       class="form-control"
                       placeholder="Search by shipment reference, name, description, or ID"
                       x-model.debounce.200ms="search">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Status</label>
                <select class="form-select" x-model="statusFilter">
                    <option value="">All records</option>
                    <option value="Draft">Draft</option>
                    <option value="Loading">Loading</option>
                    <option value="In Transit">In Transit</option>
                    <option value="Offloading">Offloading</option>
                    <option value="Stored">Stored</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
        </div>
    </div>

    
    <div x-show="detailError" class="alert alert-danger" x-cloak>
        <i class="bi bi-exclamation-triangle me-1"></i>
        <span x-text="detailError"></span>
    </div>

    <div x-show="loadingDetails" class="alert alert-info" x-cloak>
        <i class="bi bi-hourglass-split me-1"></i>
        Loading shipment details...
    </div>

<div class="ops-card p-0 overflow-hidden w-100">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Shipment</th>
                        <th>Children</th>
                        <th>Instruction</th>
                        <th>Loading</th>
                        <th>Movement</th>
                        <th>Offloading</th>
                        <th>Storage</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $shipment)
                        <tr x-show="
                            (
                                !search ||
                                (
                                    '{{ strtolower($shipment['name'] ?? '') }} ' +
                                    '{{ strtolower($shipment['description'] ?? '') }} ' +
                                    '{{ strtolower($shipment['shipment_reference'] ?? '') }} ' +
                                    '{{ $shipment['id'] ?? '' }}'
                                ).includes(search.toLowerCase())
                            )
                            &&
                            (
                                !statusFilter ||
                                statusFilter === '{{ $shipment['derived_status'] ?? 'Draft' }}'
                            )
                            "
                        >
                            <td>
                                <div class="fw-semibold">
                                    {{ $shipment['name'] ?? 'Shipment #' . ($shipment['id'] ?? '-') }}
                                </div>
                                {{-- <div class="text-muted small">
                                    ID: {{ $shipment['id'] ?? '-' }}
                                </div> --}}
                                @if(!empty($shipment['description']))
                                    <div class="text-muted small">
                                        {{ $shipment['description'] }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <button type="button"
                                        class="btn btn-sm"
                                        :class="childCountForShipment({{ $shipment['id'] ?? 'null' }}) > 0 ? 'btn-outline-primary' : 'btn-outline-secondary'"
                                        @click="childCountForShipment({{ $shipment['id'] ?? 'null' }}) > 0 && toggleShipmentChildren({{ $shipment['id'] ?? 'null' }})"
                                        :disabled="childCountForShipment({{ $shipment['id'] ?? 'null' }}) === 0">
                                    <i class="bi me-1"
                                       :class="isShipmentChildrenExpanded({{ $shipment['id'] ?? 'null' }}) ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                                    <span x-text="childCountForShipment({{ $shipment['id'] ?? 'null' }})"></span>
                                    <span x-text="childCountForShipment({{ $shipment['id'] ?? 'null' }}) === 1 ? 'child' : 'children'"></span>
                                </button>
                            </td>

                            @php
                                $stageClassMap = [
                                    'Missing' => 'text-bg-danger',
                                    'Linked'  => 'text-bg-secondary',
                                    'Started' => 'text-bg-primary',
                                    'Ended'   => 'text-bg-dark',
                                ];
                            @endphp

                            <td>
                                @php $stage = $shipment['stage_statuses']['instruction'] ?? 'Missing'; @endphp
                                <span class="badge {{ $stageClassMap[$stage] ?? 'text-bg-secondary' }}">
                                    {{ $stage }}
                                </span>
                            </td>

                            <td>
                                @php $stage = $shipment['stage_statuses']['loading'] ?? 'Missing'; @endphp
                                <span class="badge {{ $stageClassMap[$stage] ?? 'text-bg-secondary' }}">
                                    {{ $stage }}
                                </span>
                            </td>

                            <td>
                                @php $stage = $shipment['stage_statuses']['movement'] ?? 'Missing'; @endphp
                                <span class="badge {{ $stageClassMap[$stage] ?? 'text-bg-secondary' }}">
                                    {{ $stage }}
                                </span>
                            </td>

                            <td>
                                @php $stage = $shipment['stage_statuses']['offloading'] ?? 'Missing'; @endphp
                                <span class="badge {{ $stageClassMap[$stage] ?? 'text-bg-secondary' }}">
                                    {{ $stage }}
                                </span>
                            </td>

                            <td>
                                @php $stage = $shipment['stage_statuses']['storage'] ?? 'Missing'; @endphp
                                <span class="badge {{ $stageClassMap[$stage] ?? 'text-bg-secondary' }}">
                                    {{ $stage }}
                                </span>
                            </td>

                            <td>
                                @php
                                    $status = $shipment['derived_status'] ?? 'Draft';

                                    $class = match($status) {
                                        'Completed' => 'text-bg-success',
                                        'Stored' => 'text-bg-info',
                                        'Offloading' => 'text-bg-warning',
                                        'In Transit' => 'text-bg-primary',
                                        'Loading' => 'text-bg-secondary',
                                        default => 'text-bg-light border text-dark',
                                    };
                                @endphp

                                <span class="badge {{ $class }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        @click="openShipment({{ $shipment['id'] ?? 'null' }})">
                                    View
                                </button>

                                {{-- <a href="http://192.168.1.9:8080/siya/api/shipments/shipments/{{ $shipment['id'] ?? '' }}/"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-dark">
                                    JSON
                                </a> --}}

                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="openEditShipmentById({{ $shipment['id'] ?? 'null' }})">Edit</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" @click="openDeleteConfirm(@js($shipment))">Delete</button>
                            </td>
                        </tr>

                        <tr x-show="isShipmentChildrenExpanded({{ $shipment['id'] ?? 'null' }})" x-cloak>
                            <td colspan="9" class="bg-light">
                                <div class="border rounded-3 bg-white p-3 my-2">
                                    <div class="fw-semibold mb-2">
                                        <i class="bi bi-diagram-3 me-1"></i>
                                        Child Shipments
                                    </div>

                                    <template x-if="nestedChildLinksForShipment({{ $shipment['id'] ?? 'null' }}).length === 0">
                                        <div class="text-muted small">No child shipments linked to this shipment.</div>
                                    </template>

                                    <div x-show="nestedChildLinksForShipment({{ $shipment['id'] ?? 'null' }}).length > 0">
                                        <template x-for="rel in nestedChildLinksForShipment({{ $shipment['id'] ?? 'null' }})" :key="'main-child-' + rel.id">
                                            <div class="d-flex align-items-center justify-content-between gap-2 py-2 border-top">
                                                <div class="d-flex align-items-center gap-2" style="min-width:0;">
                                                    <span class="text-muted small" x-text="'—'.repeat(rel.level + 1)"></span>

                                                    <div style="min-width:0;">
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <span class="fw-semibold text-truncate" x-text="childNameFromRelationship(rel)"></span>

                                                            <span class="badge text-bg-light border text-dark">
                                                                ID: <span x-text="rel.child_shipment"></span>
                                                            </span>

                                                            <span class="badge" :class="rel.level > 0 ? 'text-bg-info' : 'text-bg-success'"
                                                                  x-text="rel.level > 0 ? 'Nested child' : 'Child'"></span>

                                                            <span class="badge" :class="childStatusClassFromRelationship(rel)"
                                                                  x-text="childStatusFromRelationship(rel)"></span>
                                                        </div>

                                                        <div class="text-muted small mt-1">
                                                            <span class="fw-semibold">Relationship:</span>
                                                            <span x-text="rel.name || 'Parent → Child'"></span>
                                                            <span class="mx-1">•</span>
                                                            <span class="fw-semibold">Description:</span>
                                                            <span x-text="childDescriptionFromRelationship(rel)"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex gap-1 flex-shrink-0">
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary"
                                                            @click="openShipment(rel.child_shipment)">
                                                        View
                                                    </button>

                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-secondary"
                                                            @click="openEditShipmentById(rel.child_shipment)">
                                                        Edit
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                No shipment records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <template x-if="selected">
        <div @click.self="selected = null"
            class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
            style="display:flex; background:rgba(15,23,42,.45); z-index:1050;">
            <div class="bg-white rounded-4 shadow-lg w-100 mx-3" style="max-width:760px;">
                <div class="d-flex justify-content-between align-items-start border-bottom p-4">
                    <div>
                        <h2 class="h5 fw-bold mb-1" x-text="selected.name || 'Shipment details'"></h2>
                        {{-- <div class="text-muted small">
                            Shipment ID: <span x-text="selected.id || '-'"></span>
                        </div> --}}

                        <div class="mt-2 d-flex gap-2 flex-wrap">
                            <span class="badge text-bg-light border text-dark">
                                Reference:
                                <span x-text="selected.shipment_reference || '-'"></span>
                            </span>

                            <span class="badge" :class="shipmentStatusClass(shipmentStatus(selected))">
                                Current Status:
                                <span x-text="shipmentStatus(selected)"></span>
                            </span>
                        </div>
                    </div>

                    <button type="button"
                            class="btn btn-sm btn-outline-secondary"
                            x-show="parentStack.length > 0"
                            @click="goBackToParent()">
                        <i class="bi bi-arrow-left me-1"></i>Back to Parent
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="selected = null">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="p-4 overflow-auto" style="max-height:calc(90vh - 150px);">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <div class="border rounded-3 p-3 bg-light" x-text="selected.description || 'No description'"></div>
                    </div>


                    <section class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="h6 fw-bold mb-0">
                                <i class="bi bi-box-seam me-2"></i>Shipment Cargo
                            </h3>

                            <span
                                class="badge text-bg-light border text-dark"
                                x-text="`${selectedCargo.length} ${selectedCargo.length === 1 ? 'Item' : 'Items'}`"
                            ></span>
                        </div>

                        <div
                            x-show="cargoError"
                            class="alert alert-danger py-2"
                            x-cloak
                        >
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <span x-text="cargoError"></span>
                        </div>

                        <div
                            x-show="cargoLoading"
                            class="border rounded-3 p-3 text-muted small"
                            x-cloak
                        >
                            <i class="bi bi-hourglass-split me-1"></i>
                            Loading shipment cargo...
                        </div>

                        <template x-if="!cargoLoading && selectedCargo.length === 0">
                            <div class="border rounded-3 p-3 bg-light text-muted small">
                                No cargo items have been added to this shipment.
                            </div>
                        </template>

                        <div class="row g-2" x-show="!cargoLoading && selectedCargo.length > 0">
                            <template
                                x-for="cargoItem in selectedCargo"
                                :key="cargoItem.id || `${cargoItem.item_id}-${cargoItem.quantity}`"
                            >
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 h-100 bg-light">
                                        <div class="d-flex align-items-center gap-3">
                                            <div
                                                class="rounded-3 border bg-white d-flex align-items-center justify-content-center"
                                                style="width:42px;height:42px;min-width:42px;"
                                            >
                                                <i class="bi bi-box-seam text-primary"></i>
                                            </div>

                                            <div class="flex-grow-1" style="min-width:0;">
                                                <div
                                                    class="fw-semibold text-truncate"
                                                    :title="cargoItem.item_label"
                                                    x-text="cargoItem.item_label"
                                                ></div>

                                                <div class="text-muted small">
                                                    Item ID:
                                                    <span x-text="cargoItem.item_id"></span>
                                                </div>
                                            </div>

                                            <span class="badge text-bg-primary">
                                                Qty
                                                <span x-text="formatQuantity(cargoItem.quantity)"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </section>


                    <div class="mt-4">
                        <h3 class="h6 fw-bold mb-2">Origin & Destination</h3>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="text-muted small">Origin Address</div>
                                    <div class="fw-semibold" x-text="addressName(selected.from_address || selected.from_address_id)"></div>
                                    <div class="text-muted small mt-1" x-text="addressDetail(selected.from_address || selected.from_address_id)"></div>
                                    {{-- <div class="text-muted small mt-1">
                                        ID: <span x-text="selected.from_address || selected.from_address_id || '-'"></span>
                                    </div> --}}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="text-muted small">Destination Address</div>
                                    <div class="fw-semibold" x-text="addressName(selected.to_address || selected.to_address_id)"></div>
                                    <div class="text-muted small mt-1" x-text="addressDetail(selected.to_address || selected.to_address_id)"></div>
                                    {{-- <div class="text-muted small mt-1">
                                        ID: <span x-text="selected.to_address || selected.to_address_id || '-'"></span>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                    </div>

                                    <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-6">
                        <h3 class="h6 fw-bold mb-0">Documents</h3>

                        <label class="btn btn-sm btn-outline-primary mb-0" :class="{ disabled: uploadingDocument }">
                            <i class="bi bi-upload me-1"></i>
                            <span x-show="!uploadingDocument">Upload</span>
                            <span x-show="uploadingDocument">Uploading...</span>
                            <input type="file" class="d-none" @change="uploadDocument($event)" accept=".jpg,.jpeg,.png,.webp,.pdf">
                        </label>
                    </div>

                    <div x-show="documentsError" class="alert alert-danger py-2" x-cloak>
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <span x-text="documentsError"></span>
                    </div>

                    <div x-show="documentsSuccess" class="alert alert-success py-2" x-cloak>
                        <i class="bi bi-check-circle me-1"></i>
                        <span x-text="documentsSuccess"></span>
                    </div>

                    <div x-show="documentsLoading" class="text-muted small py-2" x-cloak>
                        <i class="bi bi-hourglass-split me-1"></i>Loading documents...
                    </div>

                    <div x-show="!documentsLoading">
                        <template x-if="documents.length === 0">
                            <div class="border rounded-3 p-3 text-muted small">No documents uploaded yet.</div>
                        </template>

                        <div class="row g-3">
                            <template x-for="doc in documents" :key="doc.name">
                                <div class="col-md-6 col-xl-4" style="min-width:0;">
                                    <div class="border rounded-4 p-3 bg-white h-100 shadow-sm" style="min-width:0; overflow:hidden;">

                                        <div class="d-flex gap-3 align-items-start">
                                            <template x-if="isImageDocument(doc)">
                                                <a :href="publicDocumentUrl(doc.url)" target="_blank" rel="noopener noreferrer">
                                                    <img :src="publicDocumentUrl(doc.url)"
                                                         class="rounded-3 border"
                                                         style="width:88px;height:88px;object-fit:cover;"
                                                         alt="Document thumbnail">
                                                </a>
                                            </template>

                                            <template x-if="!isImageDocument(doc)">
                                                <div class="rounded-3 border bg-light d-flex align-items-center justify-content-center"
                                                     style="width:88px;height:88px;min-width:88px;">
                                                    <i class="bi fs-1" :class="documentIcon(doc)"></i>
                                                </div>
                                            </template>

                                            <div class="flex-grow-1" style="min-width:0; overflow:hidden;">
                                                <div class="fw-semibold text-truncate"
                                                        style="max-width:100%;"
                                                        :title="doc.name"
                                                        x-text="doc.name"></div>
                                                <div class="text-muted small" x-text="documentTypeLabel(doc)"></div>
                                                <div class="text-muted small" x-text="formatFileSize(doc.size_bytes)"></div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 mt-3">
                                            <a class="btn btn-sm btn-outline-primary flex-fill"
                                               :href="publicDocumentUrl(doc.url)"
                                               target="_blank"
                                               rel="noopener noreferrer">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>View
                                            </a>

                                            <a class="btn btn-sm btn-outline-secondary flex-fill"
                                               :href="publicDocumentUrl(doc.url)"
                                               :download="doc.name">
                                                <i class="bi bi-download me-1"></i>Download
                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    @click="deleteDocument(doc.name)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>


                    {{-- <div class="row g-3">
                        <div class="col-md-6"><div class="border rounded-3 p-3"><div class="text-muted small">Instruction ID</div><div class="fw-semibold" x-text="selected.shipment_instruction || '-'"></div></div></div>
                        <div class="col-md-6"><div class="border rounded-3 p-3"><div class="text-muted small">Loading ID</div><div class="fw-semibold" x-text="selected.loading || '-'"></div></div></div>
                        <div class="col-md-6"><div class="border rounded-3 p-3"><div class="text-muted small">Movement ID</div><div class="fw-semibold" x-text="selected.movement || '-'"></div></div></div>
                        <div class="col-md-6"><div class="border rounded-3 p-3"><div class="text-muted small">Offloading ID</div><div class="fw-semibold" x-text="selected.offloading || '-'"></div></div></div>
                        <div class="col-md-6"><div class="border rounded-3 p-3"><div class="text-muted small">Storage ID</div><div class="fw-semibold" x-text="selected.storage || '-'"></div></div></div>
                        <div class="col-md-6"><div class="border rounded-3 p-3"><div class="text-muted small">Business Unit ID</div><div class="fw-semibold" x-text="selected.bu || '-'"></div></div></div>
                    </div> --}}

                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="h6 fw-bold mb-0">Shipment Relationships</h3>

                        <button type="button" class="btn btn-sm btn-outline-primary" @click="openChildCreateModal()">
                            <i class="bi bi-plus-circle me-1"></i>Add Child Shipment
                        </button>
                    </div>

                    <div x-show="relationshipsError" class="alert alert-danger py-2" x-cloak>
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <span x-text="relationshipsError"></span>
                    </div>

                    <div x-show="relationshipsLoading" class="text-muted small py-2" x-cloak>
                        <i class="bi bi-hourglass-split me-1"></i>Loading relationships...
                    </div>

                    <template x-if="selected && relationshipTree">
                        <div class="border rounded-3 p-3 bg-light mb-3">

                            <div class="fw-bold fs-6 mb-3">
                                <i class="bi bi-diagram-3 me-2"></i>
                                Shipment Tree
                            </div>

                            <div class="relationship-tree">

                                <template x-if="relationshipTree">
                                    <div class="tree-node">

                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary py-0 px-2"
                                                    @click="toggleTreeNode(relationshipTree.id)">
                                                <span x-text="isTreeNodeExpanded(relationshipTree.id) ? '−' : '+'"></span>
                                            </button>

                                            <span class="fw-bold text-primary" x-text="relationshipTree.name"></span>

                                            <span class="badge text-bg-primary">Parent</span>
                                        </div>

                                        <div class="ms-4 mt-2 border-start ps-3"
                                             x-show="isTreeNodeExpanded(relationshipTree.id)">

                                            <template x-for="child in relationshipTree.children" :key="'child-' + child.id">
                                                <div class="mb-2">

                                                    <div class="d-flex align-items-center gap-2">
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-secondary py-0 px-2"
                                                                @click="toggleTreeNode(child.id)"
                                                                x-show="child.children.length > 0">
                                                            <span x-text="isTreeNodeExpanded(child.id) ? '−' : '+'"></span>
                                                        </button>

                                                        <span x-show="child.children.length === 0" class="text-muted">└──</span>

                                                        <span class="fw-semibold" x-text="child.name"></span>

                                                        <span class="badge text-bg-success">Child</span>

                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-primary"
                                                                @click="viewChildShipment(child.id)">
                                                            View
                                                        </button>

                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-secondary"
                                                                @click="openEditShipmentById(child.id)">
                                                            Edit
                                                        </button>

                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger"
                                                                @click="openDeleteConfirm({ id: child.id, name: child.name })">
                                                            Delete
                                                        </button>
                                                    </div>

                                                    <div class="ms-4 mt-2 border-start ps-3"
                                                         x-show="isTreeNodeExpanded(child.id)"
                                                         x-if="child.children.length > 0">

                                                        <template x-for="grandChild in child.children" :key="'grandchild-' + grandChild.id">
                                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                                <span class="text-muted">└──</span>

                                                                <span class="fw-semibold" x-text="grandChild.name"></span>

                                                                <span class="badge text-bg-info">Nested Child</span>

                                                                <button type="button"
                                                                        class="btn btn-sm btn-outline-primary"
                                                                        @click="viewChildShipment(grandChild.id)">
                                                                    View
                                                                </button>

                                                                <button type="button"
                                                                        class="btn btn-sm btn-outline-secondary"
                                                                        @click="openEditShipmentById(grandChild.id)">
                                                                    Edit
                                                                </button>

                                                                <button type="button"
                                                                        class="btn btn-sm btn-outline-danger"
                                                                        @click="openDeleteConfirm({ id: grandChild.id, name: grandChild.name })">
                                                                    Delete
                                                                </button>
                                                            </div>
                                                        </template>

                                                    </div>

                                                </div>
                                            </template>

                                        </div>

                                    </div>
                                </template>

                            </div>

                        </div>
                    </template>

                    
                    {{-- <template x-if="selected && relationships.length > 0">
                        <div class="row g-2 mb-3">

                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="text-muted small">Master Shipment</div>
                                    <div class="fw-semibold" x-text="selected.name"></div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="text-muted small">Related Legs</div>
                                    <div class="fw-semibold" x-text="relationships.length"></div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="text-muted small">Relationship Records</div>
                                    <div class="fw-semibold" x-text="relationships.length"></div>
                                </div>
                            </div>

                        </div>
                    </template> --}}

                    {{-- <div class="border rounded-3 overflow-hidden" x-show="!relationshipsLoading">
                            <template x-if="relationships.length === 0">
                                <div class="p-3 text-muted small">No parent or child shipment relationships found.</div>
                            </template>

                            <template x-for="rel in relationships" :key="rel.id">
                                <div class="p-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="fw-semibold" x-text="rel.name || rel.code || ('Relationship #' + rel.id)"></div>
                                            <div class="text-muted small" x-text="rel.description || 'No description'"></div>
                                        </div>

                                        <span class="badge text-bg-light border text-dark" x-text="rel.direction"></span>
                                    </div>

                                    <div class="mt-3 row g-2">
                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-2 bg-light">
                                                <div class="text-muted small">Parent</div>
                                                <div class="fw-semibold" x-text="rel.parent_name"></div>
                                                <div class="text-muted small">
                                                    ID: <span x-text="rel.parent_shipment"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-2 bg-light">
                                                <div class="text-muted small">Child</div>
                                                <div class="fw-semibold" x-text="rel.child_name"></div>
                                                <div class="text-muted small">
                                                    ID: <span x-text="rel.child_shipment"></span>
                                                </div>

                                                <div class="d-flex gap-2 mt-2">
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary"
                                                            @click="viewChildShipment(rel.child_shipment)">
                                                        View Child
                                                    </button>

                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-secondary"
                                                            @click="openEditShipmentById(rel.child_shipment)">
                                                        Edit Child
                                                    </button>

                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            @click="openDeleteConfirm({
                                                                id: rel.child_shipment,
                                                                name: rel.child_name
                                                            })">
                                                        Delete Child
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div> --}}

                


                <div class="mt-4">
                    <h3 class="h6 fw-bold mb-2">Operational Actions</h3>

                    <div class="row g-3">

                        <template x-for="stage in ['loading', 'movement', 'offloading', 'storage']" :key="stage">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <div class="fw-semibold text-capitalize" x-text="stage"></div>
                                            <div class="text-muted small">
                                                Current:
                                                <span class="badge"
                                                      :class="stageStatusClass(stageStatus(selected, stage))"
                                                      x-text="stageStatus(selected, stage)">
                                                </span>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 flex-wrap justify-content-end">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    x-show="canStartStage(selected, stage)"
                                                    @click="updateShipmentStage(stage, 'start')">
                                                Start
                                            </button>

                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success"
                                                    x-show="canEndStage(selected, stage)"
                                                    @click="updateShipmentStage(stage, 'end')">
                                                Finish
                                            </button>

                                            <span class="badge text-bg-success"
                                                  x-show="stageStatus(selected, stage) === 'Ended'">
                                                Completed
                                            </span>

                                            <span class="badge text-bg-secondary"
                                                  x-show="stageStatus(selected, stage) === 'Linked'">
                                                Ready
                                            </span>

                                            <span class="badge text-bg-danger"
                                                  x-show="stageStatus(selected, stage) === 'Missing'">
                                                Missing
                                            </span>

                                            <span class="badge text-bg-warning"
                                                  x-show="!canStartStage(selected, stage) &&
                                                          !canEndStage(selected, stage) &&
                                                          stageStatus(selected, stage) === 'Linked' &&
                                                          nextStageRequirement(selected, stage)"
                                                  x-text="nextStageRequirement(selected, stage)">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                    </div>
                </div>


                <div class="mt-4">
                    <h3 class="h6 fw-bold mb-2">Shipment Timeline</h3>

                    <div class="border rounded-3 overflow-hidden">
                        <template x-for="(item, index) in shipmentTimeline(selected)" :key="item.label">
                            <div class="d-flex align-items-start gap-3 p-3 border-bottom position-relative">

                                <div class="position-relative d-flex flex-column align-items-center" style="width:46px; min-width:46px;">

                                    <div class="rounded-circle border d-flex align-items-center justify-content-center position-relative"
                                         :class="timelineItemClass(item.value)"
                                         style="width:46px; height:46px; z-index:2;">
                                        <i class="bi fs-5" :class="[item.icon, timelineIconClass(item.value)]"></i>
                                    </div>

                                    <div x-show="index < shipmentTimeline(selected).length - 1"
                                         class="position-absolute start-50 translate-middle-x"
                                         :class="item.value ? 'bg-success' : 'bg-secondary'"
                                         style="top:46px; width:4px; height:54px; opacity:.75;">
                                    </div>

                                </div>

                                <div class="flex-grow-1">
                                    <div class="fw-semibold" x-text="item.label"></div>
                                    <div class="text-muted small" x-text="formatTimelineDate(item.value)"></div>
                                </div>

                                <span class="badge"
                                      :class="item.value ? 'text-bg-success' : 'text-bg-light border text-dark'"
                                      x-text="item.value ? 'Done' : 'Pending'">
                                </span>
                            </div>
                        </template>
                    </div>
                </div>

                </div>

                <div class="d-flex justify-content-end gap-2 border-top p-4">

                    {{-- <a class="btn btn-outline-dark"
                       :href="`http://192.168.1.9:8080/siya/api/shipments/shipments/${selected.id}/`"
                       target="_blank">
                        <i class="bi bi-braces me-1"></i>JSON
                    </a> --}}

                    <button type="button" class="btn btn-secondary" @click="selected = null">Close</button>
                </div>
            </div>
        </div>
    </template>

    @include('bfrn.components.modal', [
        'title' => 'Create Shipment',
        'maxWidth' => '990px',
        'body' => view('bfrn.operations.flows.partials.create-form')->render(),
    ])


    <template x-if="editModalOpen">
        <div
            class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
            style="display:flex; background:rgba(15,23,42,.45); z-index:1060;"
            @click.self="editModalOpen = false"
        >
            <div class="bg-white rounded-4 shadow-lg w-100 mx-3 d-flex flex-column" style="max-width:900px; max-height:90vh;">
                <div class="d-flex justify-content-between align-items-start border-bottom p-4">
                    <div>
                        <h2 class="h5 fw-bold mb-1">Edit Shipment</h2>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="editModalOpen = false">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="p-4">
                    @include('bfrn.operations.flows.partials.edit-form')
                </div>
            </div>
        </div>
    </template>


    <template x-if="deleteModalOpen">
        <div
            class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
            style="display:flex; background:rgba(15,23,42,.45); z-index:1070;"
            @click.self="deleteModalOpen = false"
        >
            <div class="bg-white rounded-4 shadow-lg w-100 mx-3" style="max-width:520px;">
                <div class="border-bottom p-4">
                    <h2 class="h5 fw-bold mb-1 text-danger">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Confirm Delete
                    </h2>
                    <div class="text-muted small">
                        This action will delete the shipment record and its shipment items.
                    </div>
                </div>

                <div class="p-4">

                <!-- Normal Shipment -->
                <div x-show="!deleteBlocked && !deleteIsChild"
                    class="alert alert-info mb-3">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Are you sure you want to delete this shipment?
                </div>

                <!-- Child Shipment -->
                <div x-show="deleteIsChild && !deleteBlocked"
                    class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    This shipment is a child to a parent shipment.
                </div>

                <!-- Parent Shipment -->
                <div x-show="deleteBlocked"
                    class="alert alert-danger mb-3">
                    <i class="bi bi-x-octagon-fill me-2"></i>
                    <strong>Delete Blocked.</strong>
                    <span x-text="deleteError"></span>
                </div>

                <div class="border rounded-3 p-3 bg-light">
                    <div class="fw-semibold"
                        x-text="pendingDelete?.name || 'Unnamed shipment'">
                    </div>

                    <div class="small">
                        <i class="bi bi-info-circle me-1"></i>
                        Shipment ID:
                        <span x-text="pendingDelete?.id || '-'"></span>
                    </div>
                </div>

            </div>

                <div class="d-flex justify-content-end gap-2 border-top p-4">
                    <button type="button" class="btn btn-outline-secondary" @click="deleteModalOpen = false" :disabled="deletingFlow">
                        Cancel
                    </button>

                    <button type="button" class="btn btn-danger" @click="confirmDeleteFlow()" :disabled="deletingFlow || deleteBlocked">
                        <span x-show="!deletingFlow">
                            <i class="bi bi-trash me-1"></i>Yes, Delete
                        </span>
                        <span x-show="deletingFlow">
                            <i class="bi bi-hourglass-split me-1"></i>Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </template>


    <template x-if="childCreateModalOpen">
        <div
            class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
            style="display:flex; background:rgba(15,23,42,.45); z-index:1080;"
            @click.self="childCreateModalOpen = false"
        >
            <div class="bg-white rounded-4 shadow-lg w-100 mx-3" style="max-width:980px;">
                <div class="d-flex justify-content-between align-items-start border-bottom p-4">
                    <div>
                        <h2 class="h5 fw-bold mb-1">Create Child Shipment</h2>
                        <div class="text-muted small">
                            Parent: <span x-text="selected?.name || '-'"></span>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="childCreateModalOpen = false">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="p-4">
                    @include('bfrn.operations.flows.partials.create-child-form')
                </div>
            </div>
        </div>
    </template>


    <template x-if="addressCreateModalOpen">
        <div
            class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
            style="display:flex; background:rgba(15,23,42,.45); z-index:1090;"
            @click.self="addressCreateModalOpen = false"
        >
            <div class="bg-white rounded-4 shadow-lg w-100 mx-3" style="max-width:900px;">
                <div class="d-flex justify-content-between align-items-start border-bottom p-4">
                    <div>
                        <h2 class="h5 fw-bold mb-1" x-text="addressModalMode === 'edit' ? 'Edit Address' : 'Create New Address'"></h2>
                        <div class="text-muted small" x-text="addressModalMode === 'edit' ? 'Update the selected address details.' : 'Add an address, then search it in the Origin/Destination dropdown.'"></div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="addressCreateModalOpen = false">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="p-4">
                    @include('bfrn.operations.flows.partials.create-address-form')
                </div>
            </div>
        </div>
    </template>


</div>
@endsection
