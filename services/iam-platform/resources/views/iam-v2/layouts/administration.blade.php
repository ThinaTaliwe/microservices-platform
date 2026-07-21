<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'IAM Administration')</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <script
        defer
        src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"
    ></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            margin: 0;
            background: #f8fafc;
            color: #0f172a;
            font-family:
                system-ui,
                -apple-system,
                "Segoe UI",
                sans-serif;
        }

        .iam-shell {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            min-height: 100vh;
            transition: grid-template-columns .2s ease;
        }

        .iam-shell.sidebar-collapsed {
            grid-template-columns: 78px minmax(0, 1fr);
        }

        .iam-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            padding: 1rem;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
        }

        .iam-main {
            min-width: 0;
            padding: 1.5rem;
        }

        .iam-sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 1.5rem;
        }

        .iam-brand {
            display: flex;
            align-items: center;
            gap: .65rem;
            color: #2563eb;
            font-weight: 800;
            white-space: nowrap;
        }

        .iam-brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: .75rem;
            background: #eef2ff;
            color: #2563eb;
        }

        .iam-sidebar-toggle {
            width: 36px;
            height: 36px;
            border: 1px solid #e2e8f0;
            border-radius: .65rem;
            background: #ffffff;
            color: #334155;
        }

        .iam-nav-section {
            margin: 1rem 0 .4rem;
            color: #94a3b8;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .iam-nav-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: .25rem;
            padding: .65rem .8rem;
            border-radius: .6rem;
            color: #64748b;
            text-decoration: none;
            white-space: nowrap;
        }

        .iam-nav-link i {
            min-width: 22px;
            text-align: center;
            font-size: 1.05rem;
        }

        .iam-nav-link:hover,
        .iam-nav-link.active {
            background: #eef2ff;
            color: #2563eb;
        }

        .iam-card,
        .iam-topbar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: .9rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .08);
        }

        .iam-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: .9rem 1rem;
        }

        .sidebar-collapsed .iam-brand-text,
        .sidebar-collapsed .iam-nav-label,
        .sidebar-collapsed .iam-nav-section {
            display: none;
        }

        .sidebar-collapsed .iam-sidebar-header {
            flex-direction: column;
            justify-content: center;
        }

        .sidebar-collapsed .iam-nav-link {
            justify-content: center;
            padding: .75rem;
        }

        @media (max-width: 992px) {
            .iam-shell,
            .iam-shell.sidebar-collapsed {
                grid-template-columns: 1fr;
            }

            .iam-sidebar {
                position: fixed;
                z-index: 1200;
                width: 260px;
                transform: translateX(-100%);
                transition: transform .2s ease;
            }

            .iam-shell.mobile-open .iam-sidebar {
                transform: translateX(0);
            }

            .iam-main {
                padding: 1rem;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
<div
    id="iam-administration-shell"
    class="iam-shell"
    x-data="{
        sidebarCollapsed:
            localStorage.getItem(
                'iam_v2_sidebar_collapsed'
            ) === '1',

        mobileSidebarOpen: false,

        toggleSidebar() {
            this.sidebarCollapsed =
                !this.sidebarCollapsed;

            localStorage.setItem(
                'iam_v2_sidebar_collapsed',
                this.sidebarCollapsed ? '1' : '0'
            );
        }
    }"
    :class="{
        'sidebar-collapsed': sidebarCollapsed,
        'mobile-open': mobileSidebarOpen
    }"
>
    <aside class="iam-sidebar">
        <div class="iam-sidebar-header">
            <div class="iam-brand">
                <span class="iam-brand-icon">
                    <i class="bi bi-shield-lock"></i>
                </span>

                <span class="iam-brand-text">
                    IAM Platform
                </span>
            </div>

            <button
                type="button"
                class="iam-sidebar-toggle"
                title="Toggle sidebar"
                @click="toggleSidebar()"
            >
                <i
                    class="bi"
                    :class="
                        sidebarCollapsed
                            ? 'bi-chevron-double-right'
                            : 'bi-chevron-double-left'
                    "
                ></i>
            </button>
        </div>

        <div class="iam-nav-section">
            Access Management
        </div>

        <a
            href="{{ route('iam-v2.role-assignments.page') }}"
            class="iam-nav-link active"
        >
            <i class="bi bi-person-badge"></i>
            <span class="iam-nav-label">
                Role Assignments
            </span>
        </a>

        <a href="#" class="iam-nav-link">
            <i class="bi bi-people"></i>
            <span class="iam-nav-label">
                Identities
            </span>
        </a>

        <a href="#" class="iam-nav-link">
            <i class="bi bi-building"></i>
            <span class="iam-nav-label">
                Companies
            </span>
        </a>

        <a href="#" class="iam-nav-link">
            <i class="bi bi-diagram-3"></i>
            <span class="iam-nav-label">
                Business Units
            </span>
        </a>

        <a href="#" class="iam-nav-link">
            <i class="bi bi-grid"></i>
            <span class="iam-nav-label">
                Systems
            </span>
        </a>

        <div class="iam-nav-section">
            Security
        </div>

        <a href="#" class="iam-nav-link">
            <i class="bi bi-journal-text"></i>
            <span class="iam-nav-label">
                Audit Log
            </span>
        </a>
    </aside>

    <main class="iam-main">
        <header class="iam-topbar">
            <div>
                <h1 class="h4 fw-bold mb-1">
                    @yield('title', 'IAM Administration')
                </h1>

                <div class="small text-secondary">
                    @yield(
                        'subtitle',
                        'Manage contextual access securely.'
                    )
                </div>
            </div>

            <button
                type="button"
                class="btn btn-outline-primary d-lg-none"
                @click="mobileSidebarOpen = true"
            >
                <i class="bi bi-list"></i>
            </button>
        </header>

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
