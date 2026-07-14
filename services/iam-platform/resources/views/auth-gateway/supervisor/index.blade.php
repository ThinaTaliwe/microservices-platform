<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supervisor Review</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        [x-cloak] { display: none !important; }
        button:disabled { opacity: .65; cursor: not-allowed !important; }
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body style="margin:0; padding:2em; font-family:Arial, sans-serif; background:#f3f4f6; color:#111827;">

<div style="padding:32px 20px;">
    <header style="margin-bottom:24px;">
        <h1 style="margin:0; font-size:28px; font-weight:800;">Supervisor Review Queue</h1>
        <p style="margin:8px 0 0; color:#6b7280; font-size:14px;">
            Review authentication requests that need manual approval.
        </p>
    </header>

    @if(session('success'))
        <div style="background:#ecfdf5; border:1px solid #a7f3d0; color:#047857; padding:14px 16px; border-radius:12px; margin-bottom:18px; font-size:14px; font-weight:700;">
            {{ session('success') }}
        </div>
    @endif

    <section style="display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:14px; margin-bottom:18px;">
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:14px; padding:16px;">
            <div style="color:#6b7280; font-size:13px; font-weight:700;">Pending</div>
            <div style="font-size:28px; font-weight:900; margin-top:4px;">{{ $summary['pending'] }}</div>
        </div>

        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:14px; padding:16px;">
            <div style="color:#6b7280; font-size:13px; font-weight:700;">Approved</div>
            <div style="font-size:28px; font-weight:900; margin-top:4px;">{{ $summary['approved'] }}</div>
        </div>

        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:14px; padding:16px;">
            <div style="color:#6b7280; font-size:13px; font-weight:700;">Blocked</div>
            <div style="font-size:28px; font-weight:900; margin-top:4px;">{{ $summary['blocked'] }}</div>
        </div>

        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:14px; padding:16px;">
            <div style="color:#6b7280; font-size:13px; font-weight:700;">Expired</div>
            <div style="font-size:28px; font-weight:900; margin-top:4px;">{{ $summary['expired'] }}</div>
        </div>
    </section>

    <section style="background:#ffffff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; box-shadow:0 8px 22px rgba(15,23,42,.06);">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead style="background:#f9fafb; color:#374151;">
                <tr>
                    <th style="text-align:left; padding:14px 16px;">ID</th>
                    {{-- <th style="text-align:left; padding:14px 16px;">Risk</th> --}}
                    <th style="text-align:left; padding:14px 16px;">Status</th>
                    <th style="text-align:left; padding:14px 16px;">Login Details</th>
                    <th style="text-align:left; padding:14px 16px;">Expires</th>
                    <th style="text-align:right; padding:14px 16px;">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($approvals as $approval)
                    <tr id="approval-{{ $approval->id }}" style="border-top:1px solid #e5e7eb; {{ request('approval') == $approval->id ? 'background:#eff6ff;' : '' }}">
                        <td style="padding:14px 16px; font-weight:700;">{{ $approval->id }}</td>

                        {{-- <td style="padding:14px 16px;">
                            <span style="display:inline-flex; padding:5px 9px; border-radius:999px; background:#fff7ed; color:#c2410c; font-size:12px; font-weight:800;">
                                {{ strtoupper($approval->risk_level) }}
                            </span>
                        </td> --}}

                        <td style="padding:14px 16px;">
                            @php
                                $statusStyle = match($approval->status) {
                                    'approved' => 'background:#ecfdf5;color:#047857;border-color:#a7f3d0;',
                                    'blocked' => 'background:#fef2f2;color:#b91c1c;border-color:#fecaca;',
                                    default => 'background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;',
                                };
                            @endphp

                            <span style="display:inline-flex; padding:5px 9px; border:1px solid; border-radius:999px; font-size:12px; font-weight:800; {{ $statusStyle }}">
                                {{ strtoupper($approval->status) }}
                            </span>
                        </td>

                        <td style="padding:14px 16px; color:#374151; min-width:460px;">
                            <div style="font-weight:800; color:#111827; margin-bottom:6px;">
                                {{ $approval->email }}
                            </div>

                            <div style="font-size:13px; margin-bottom:8px;">
                                {{ $approval->decision_reason }}
                            </div>

                            <div style="font-size:12px; color:#6b7280; line-height:1.7;">
                                <div><strong>BFRN User ID:</strong> {{ $approval->bfrn_user_id ?? 'Not linked' }}</div>
                                <div><strong>Device:</strong> {{ $approval->device_label }}</div>
                                <div><strong>Location:</strong> {{ $approval->location }}</div>
                                <div><strong>IP:</strong> {{ $approval->ip }}</div>
                                <div><strong>Platform:</strong> {{ $approval->platform }}</div>
                                <div><strong>Screen:</strong> {{ $approval->screen }}</div>
                                <div><strong>Timezone:</strong> {{ $approval->timezone }}</div>
                                <div style="max-width:520px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <strong>Browser:</strong> {{ $approval->user_agent }}
                                </div>
                            </div>
                        </td>

                        <td style="padding:14px 16px; color:#4b5563;">
                            {{ $approval->expires_at }}
                        </td>

                        <td style="padding:14px 16px; text-align:right;">
                            @if($approval->status === 'pending')
                                <div style="display:flex; justify-content:flex-end; gap:8px;">
                                    <form method="POST" action="/supervisor/{{ $approval->id }}/approve" onsubmit="return confirm('Approve this login request?');">
                                        @csrf

                                        <div style="display:flex; gap:8px; align-items:center; justify-content:flex-end; flex-wrap:nowrap;">
                                            <select
                                                name="approved_bu_id"
                                                title="Select business unit before approval"
                                                style="padding:9px 12px; border:1px solid #cbd5e1; border-radius:10px; width:auto; max-width:350px; background:#ffffff; font-weight:700; color:#111827;"
                                            >
                                                @foreach($businessUnits as $businessUnit)
                                                    <option value="{{ $businessUnit->id }}" @selected((int)($approval->approved_bu_id ?? 8) === (int)$businessUnit->id)>
                                                        {{ $businessUnit->bu_name }} ({{ $businessUnit->short_code }})
                                                    </option>
                                                @endforeach
                                            </select>

                                            <button
                                                type="submit"
                                                style="border:0; background:#059669; color:#fff; padding:8px 12px; border-radius:10px; font-weight:900; cursor:pointer; box-shadow:0 1px 2px rgba(0,0,0,.12);"
                                            >
                                                Approve
                                            </button>
                                        </div>
                                    </form>

                                    <form method="POST" action="/supervisor/{{ $approval->id }}/block" onsubmit="return confirm('Block this login request?');">
                                        @csrf
                                        <button
                                            type="submit"
                                            style="border:0; background:#dc2626; color:#fff; padding:9px 12px; border-radius:9px; font-weight:800; cursor:pointer;"
                                        >
                                            Block
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span style="color:#8a9091; font-size:13px;">Decided</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:36px 16px; text-align:center; color:#6b7280;">
                            No approval requests found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>


</div>

</body>
{{-- </html>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Supervisor Review</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-light">
<div class="container-fluid py-4" style="max-width:1320px;">
    <header class="mb-4">
        <h1 class="h3 fw-bold mb-1">Supervisor Review Queue</h1>
        <p class="text-muted mb-0">Review high-risk authentication requests and assign the correct business unit.</p>
    </header>

    @if(session('success'))
        <div class="alert alert-success fw-semibold">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger fw-semibold">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="small text-muted fw-semibold">Pending</div>
                <div class="h3 fw-bold mb-0">{{ $summary['pending'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="small text-muted fw-semibold">Approved</div>
                <div class="h3 fw-bold mb-0">{{ $summary['approved'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="small text-muted fw-semibold">Blocked</div>
                <div class="h3 fw-bold mb-0">{{ $summary['blocked'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="small text-muted fw-semibold">Expired</div>
                <div class="h3 fw-bold mb-0">{{ $summary['expired'] }}</div>
            </div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:70px;">ID</th>
                        <th>Email</th>
                        <th style="width:120px;">Risk</th>
                        <th style="width:130px;">Status</th>
                        <th style="width:260px;">Business Unit</th>
                        <th style="width:250px;" class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($approvals as $approval)
                        @php
                            $statusClass = match($approval->status) {
                                'approved' => 'success',
                                'blocked' => 'danger',
                                'expired' => 'secondary',
                                default => 'primary',
                            };

                            $riskClass = strtolower($approval->risk_level) === 'high' ? 'warning text-dark' : 'success';
                        @endphp

                        <tr x-data="{ open: {{ request('approval') == $approval->id ? 'true' : 'false' }} }" id="approval-{{ $approval->id }}">
                            <td class="fw-bold">{{ $approval->id }}</td>

                            <td>
                                <div class="fw-semibold text-truncate" style="max-width:320px;">{{ $approval->email }}</div>
                                <div class="small text-muted text-truncate" style="max-width:420px;">{{ $approval->decision_reason }}</div>
                            </td>

                            <td>
                                <span class="badge text-bg-{{ $riskClass }}">{{ strtoupper($approval->risk_level) }}</span>
                            </td>

                            <td>
                                <span class="badge text-bg-{{ $statusClass }}">{{ strtoupper($approval->status) }}</span>
                            </td>

                            <td>
                                @if($approval->status === 'pending')
                                    <form id="approve-form-{{ $approval->id }}" method="POST" action="/supervisor/{{ $approval->id }}/approve">
                                        @csrf
                                        <select name="approved_bu_id" class="form-select form-select-sm">
                                            @foreach($businessUnits as $businessUnit)
                                                <option value="{{ $businessUnit->id }}" @selected((int)($approval->approved_bu_id ?? 8) === (int)$businessUnit->id)>
                                                    {{ $businessUnit->short_code }} — {{ $businessUnit->bu_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    <span class="text-muted small">No action needed</span>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="open = !open">
                                        Details
                                    </button>

                                    @if($approval->status === 'pending')
                                        <button
                                            type="submit"
                                            form="approve-form-{{ $approval->id }}"
                                            class="btn btn-sm btn-success"
                                            onclick="return confirm('Approve this login request?');"
                                        >
                                            Approve
                                        </button>

                                        <form method="POST" action="/supervisor/{{ $approval->id }}/block">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Block this login request?');">
                                                Block
                                            </button>
                                        </form>
                                    @else
                                        <span class="small text-muted align-self-center">Decided</span>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <tr x-data="{ open: {{ request('approval') == $approval->id ? 'true' : 'false' }} }" x-show="open" style="display:none;">
                            <td colspan="6" class="bg-body-tertiary">
                                <div class="row g-3 small p-3">
                                    <div class="col-md-3"><strong>BFRN User ID:</strong><br>{{ $approval->bfrn_user_id ?? 'Not linked' }}</div>
                                    <div class="col-md-3"><strong>Device:</strong><br>{{ $approval->device_label }}</div>
                                    <div class="col-md-3"><strong>Location:</strong><br>{{ $approval->location }}</div>
                                    <div class="col-md-3"><strong>IP:</strong><br>{{ $approval->ip }}</div>
                                    <div class="col-md-3"><strong>Platform:</strong><br>{{ $approval->platform }}</div>
                                    <div class="col-md-3"><strong>Screen:</strong><br>{{ $approval->screen }}</div>
                                    <div class="col-md-3"><strong>Timezone:</strong><br>{{ $approval->timezone }}</div>
                                    <div class="col-md-3"><strong>Expires:</strong><br>{{ $approval->expires_at }}</div>
                                    <div class="col-12"><strong>Browser:</strong><br><span class="text-muted">{{ $approval->user_agent }}</span></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">No approval requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html> --}}
