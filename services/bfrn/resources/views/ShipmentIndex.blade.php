{{-- Absolutely! Let's make the shipping lines come alive with **visual route mapping**.

I will update the application to:
1.  **Add Realistic Route Paths:** Define coordinate paths for major shipping lanes (e.g., Asia-Europe, Intra-Africa, Coastal).
2.  **Visualize on Map:** Draw polylines on the Leaflet map when a shipping line is selected.
3.  **Multiple Routes:** Support multiple routes per shipping line (e.g., Maersk might have both an Asia route and an Europe route).
4.  **Route Info Panel:** Display active routes in the details panel.
5.  **Color Coding:** Different colors for different route types (International vs. Regional). --}}




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PortHub - South African</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #007749;
            --primary-dark: #005a37;
            --primary-light: rgba(0, 119, 73, 0.15);
            --secondary: #FFB81C;
            --secondary-light: rgba(255, 184, 28, 0.15);
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1e293b;
            --gray: #64748b;
            --light: rgba(241, 245, 249, 0.7);
            --white: rgba(255, 255, 255, 0.95);
            --glass-bg: rgba(255, 255, 255, 0.12);
            --glass-bg-solid: rgba(255, 255, 255, 0.2);
            --glass-border: rgba(255, 255, 255, 0.25);
            --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.2);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -1px rgba(0, 0, 0, 0.12);
            --shadow-lg: 0 10px 40px -3px rgba(0, 0, 0, 0.25), 0 4px 6px -2px rgba(0, 0, 0, 0.15);
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.85);
            --text-muted: rgba(255, 255, 255, 0.65);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            color: var(--text-primary);
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 20% 80%, rgba(0, 119, 73, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 184, 28, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(0, 119, 73, 0.1) 0%, transparent 40%);
            animation: gradientMove 20s ease infinite;
            z-index: -1;
        }

        @keyframes gradientMove {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            50% {
                transform: translate(-2%, -2%) rotate(5deg);
            }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            background: var(--glass-bg-solid);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 24px;
            box-shadow: var(--glass-shadow);
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }

        .header-image {
            width: 200px;
            height: 150px;
            border-radius: 16px;
            object-fit: cover;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--glass-border);
        }

        .header-content {
            flex: 1;
            min-width: 300px;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        .header h1 .icon {
            font-size: 2.5rem;
        }

        .header p {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.6;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        }

        .api-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, rgba(0, 119, 73, 0.95), rgba(0, 90, 55, 0.95));
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: var(--text-primary);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 12px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 4px 12px rgba(0, 119, 73, 0.3);
        }

        .api-badge .dot {
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 2s infinite;
            box-shadow: 0 0 8px var(--success);
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Navigation Tabs */
        .nav-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .nav-tab {
            padding: 14px 28px;
            background: var(--glass-bg-solid);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--glass-shadow);
            color: var(--text-primary);
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        }

        .nav-tab:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.3);
            box-shadow: var(--shadow-lg);
        }

        .nav-tab.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--text-primary);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 16px rgba(0, 119, 73, 0.4);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--glass-bg-solid);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 20px;
            padding: 24px;
            box-shadow: var(--glass-shadow);
            border: 1px solid var(--glass-border);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            background: rgba(255, 255, 255, 0.25);
        }

        .stat-card .icon {
            font-size: 2rem;
            margin-bottom: 12px;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        .stat-card .label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 4px;
        }

        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 24px;
        }

        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
            }
        }

        /* Search & Filter */
        .search-panel {
            background: var(--glass-bg-solid);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 20px;
            padding: 24px;
            box-shadow: var(--glass-shadow);
            margin-bottom: 24px;
            border: 1px solid var(--glass-border);
        }

        .search-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 14px 18px;
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: var(--text-primary);
        }

        .search-input::placeholder {
            color: var(--text-muted);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 0 3px rgba(0, 119, 73, 0.25);
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-primary);
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 4px 15px rgba(0, 119, 73, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 119, 73, 0.45);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Filter Row */
        .filter-row {
            display: flex;
            gap: 12px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .select-input {
            padding: 12px 16px;
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            font-size: 0.95rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            cursor: pointer;
            min-width: 180px;
            color: var(--text-primary);
        }

        .select-input option {
            background: var(--dark);
            color: var(--text-primary);
        }

        .select-input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.25);
        }

        /* Data Panel */
        .data-panel {
            background: var(--glass-bg-solid);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 20px;
            box-shadow: var(--glass-shadow);
            border: 1px solid var(--glass-border);
            overflow: hidden;
        }

        .panel-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            background: rgba(255, 255, 255, 0.08);
        }

        .panel-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        }

        .data-count {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--text-primary);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid var(--glass-border);
            box-shadow: 0 2px 8px rgba(0, 119, 73, 0.3);
        }

        .data-list {
            max-height: 600px;
            overflow-y: auto;
        }

        .data-item {
            padding: 20px 24px;
            border-bottom: 1px solid var(--glass-border);
            cursor: pointer;
            transition: all 0.3s ease;
            background: transparent;
        }

        .data-item:hover {
            background: linear-gradient(135deg, rgba(0, 119, 73, 0.2), rgba(255, 184, 28, 0.1));
        }

        .data-item.active {
            background: linear-gradient(135deg, rgba(0, 119, 73, 0.3), rgba(0, 90, 55, 0.25));
            border-left: 4px solid var(--primary);
        }

        .data-item:last-child {
            border-bottom: none;
        }

        .data-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        }

        .data-name .icon {
            color: var(--secondary);
        }

        .data-details {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .data-detail {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .data-tag {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 8px;
            margin-right: 6px;
            border: 1px solid var(--glass-border);
        }

        .data-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: auto;
            border: 1px solid var(--glass-border);
        }

        .status-active {
            background: rgba(16, 185, 129, 0.25);
            color: #6ee7b7;
        }

        .status-inactive {
            background: rgba(239, 68, 68, 0.25);
            color: #fca5a5;
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.25);
            color: #fcd34d;
        }

        /* Map Panel */
        .map-panel {
            background: var(--glass-bg-solid);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 20px;
            box-shadow: var(--glass-shadow);
            border: 1px solid var(--glass-border);
            overflow: hidden;
            position: sticky;
            top: 24px;
        }

        .map-container {
            height: 400px;
            width: 100%;
        }

        .map-info {
            padding: 20px 24px;
            border-top: 1px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.05);
        }

        .map-info h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--text-primary);
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        }

        .map-info p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .coordinate-box {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 12px 16px;
            border-radius: 12px;
            margin-top: 12px;
            font-family: monospace;
            font-size: 0.9rem;
            color: var(--text-primary);
            border: 1px solid var(--glass-border);
        }

        .info-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 16px;
        }

        .info-stat {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 12px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
        }

        .info-stat .label {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .info-stat .value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .contact-info {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 16px;
            border-radius: 12px;
            margin-top: 16px;
            border: 1px solid var(--glass-border);
        }

        .contact-info .item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .contact-info .item:last-child {
            margin-bottom: 0;
        }

        /* Route Visualization Styles */
        .route-list {
            margin-top: 16px;
            max-height: 200px;
            overflow-y: auto;
        }

        .route-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border-radius: 10px;
            margin-bottom: 8px;
            border: 1px solid var(--glass-border);
            font-size: 0.85rem;
        }

        .route-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
            box-shadow: 0 0 8px currentColor;
        }

        .route-name {
            font-weight: 600;
            color: var(--text-primary);
            flex: 1;
        }

        .route-type {
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.15);
            color: var(--text-secondary);
            border: 1px solid var(--glass-border);
        }

        /* Loading State */
        .loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 16px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state .icon {
            font-size: 4rem;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            color: var(--text-primary);
            padding: 16px 24px;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 12px;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            border: 1px solid var(--glass-border);
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast.success {
            background: rgba(16, 185, 129, 0.95);
        }

        .toast.error {
            background: rgba(239, 68, 68, 0.95);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }

            .header-image {
                width: 100%;
                max-width: 300px;
                height: 180px;
            }

            .header h1 {
                justify-content: center;
            }

            .search-row {
                flex-direction: column;
            }

            .search-input,
            .btn {
                width: 100%;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .nav-tabs {
                justify-content: center;
            }
        }

        /* Scrollbar */
        .data-list::-webkit-scrollbar {
            width: 8px;
        }

        .data-list::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .data-list::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        .data-list::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* South Africa Flag Colors */
        .sa-flag {
            display: inline-flex;
            gap: 3px;
            margin-left: 8px;
        }

        .sa-flag span {
            width: 12px;
            height: 8px;
            border-radius: 2px;
        }

        .sa-flag .green {
            background: #007749;
        }

        .sa-flag .yellow {
            background: #FFB81C;
        }

        .sa-flag .black {
            background: #000000;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            {{-- <img src="https://image.qwenlm.ai/public_source/f1eb65fb-f72e-4a37-9bf1-465fd059e738/187587ae4-626c-452c-aca9-5a4a2a5e7d15.png" alt="Shipping Port" class="header-image"> --}}
            <img src="https://image.qwenlm.ai/public_source/75ad6831-1a69-4a8a-ac03-9979f8ddcd76/1d4bca349-a2b1-4cdf-a441-f75794dc09dc.png"
                alt="Shipping Port" class="header-image">
            <div class="header-content">
                <h1>
                    <span class="icon">🚢</span>
                    PortHub South Africa
                    <div class="sa-flag">
                        <span class="green"></span>
                        <span class="yellow"></span>
                        <span class="black"></span>
                    </div>
                </h1>
                {{-- <p>Comprehensive maritime directory for South African ports, shipping lines, and customs clearing
                    agents. Your complete resource for logistics and trade operations.</p> --}}
                    <p>South African ports, shipping lines, and clearing
                    agents.</p>
                <div class="api-badge">
                    <span class="dot"></span>
                    SA Database Info
                </div>
            </div>
        </header>
        <!-- Navigation Tabs -->
        <div class="nav-tabs">
            <button class="nav-tab active" onclick="switchTab('ports')">
                ⚓ Ports
            </button>
            <button class="nav-tab" onclick="switchTab('shipping')">
                🚢 Shipping Lines
            </button>
            <button class="nav-tab" onclick="switchTab('agents')">
                📋 Clearing Agents
            </button>
        </div>
        <!-- Stats -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <div class="icon">🚢</div>
                <div class="value" id="stat1">0</div>
                <div class="label" id="stat1Label">Total Ports</div>
            </div>
            <div class="stat-card">
                <div class="icon">📦</div>
                <div class="value" id="stat2">0</div>
                <div class="label" id="stat2Label">Capacity</div>
            </div>
            <div class="stat-card">
                <div class="icon">🏗️</div>
                <div class="value" id="stat3">0</div>
                <div class="label" id="stat3Label">Operational</div>
            </div>
            <div class="stat-card">
                <div class="icon">📍</div>
                <div class="value" id="stat4">3,000km</div>
                <div class="label" id="stat4Label">Coastline</div>
            </div>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <!-- Left Panel -->
            <div class="left-panel">
                <!-- Search Panel -->
                <div class="search-panel">
                    <div class="search-row">
                        <input type="text" class="search-input" id="searchInput" placeholder="Search...">
                        <button class="btn btn-primary" onclick="searchData()">
                            🔍 Search
                        </button>
                        <button class="btn btn-secondary" onclick="resetSearch()">
                            🔄 Reset
                        </button>
                    </div>
                    <div class="filter-row" id="filterRow">
                        <select class="select-input" id="filter1" onchange="applyFilters()">
                            <option value="">All Options</option>
                        </select>
                        <select class="select-input" id="filter2" onchange="applyFilters()">
                            <option value="">All Options</option>
                        </select>
                        <button class="btn btn-primary" onclick="refreshData()">
                            🔄 Refresh
                        </button>
                    </div>
                </div>
                <!-- Data List -->
                <div class="data-panel">
                    <div class="panel-header">
                        <h2 id="panelTitle">📍 South African Ports</h2>
                        <span class="data-count" id="dataCount">0 items</span>
                    </div>
                    <div class="data-list" id="dataList">
                        <div class="loading">
                            <div class="loading-spinner"></div>
                            <p>Loading data...</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Right Panel (Map/Info) -->
            <div class="right-panel">
                <div class="map-panel">
                    <div id="map" class="map-container"></div>
                    <div class="map-info">
                        <h3 id="infoTitle">🗺️ Location Details</h3>
                        <p id="infoDescription">Select an item from the list to view detailed information.</p>
                        <div class="coordinate-box" id="coordinates" style="display: none;">
                            Lat: -- | Lng: --
                        </div>
                        <div class="info-stats" id="infoStats" style="display: none;">
                        </div>
                        <div class="contact-info" id="contactInfo" style="display: none;">
                        </div>
                        <div id="routeInfo" style="display: none; margin-top: 16px;">
                            <h4
                                style="font-size: 0.95rem; font-weight: 600; margin-bottom: 8px; color: var(--text-primary);">
                                🗺️ Active Shipping Routes</h4>
                            <div class="route-list" id="routeList"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Toast -->
    <div class="toast" id="toast">
        <span id="toastIcon">✓</span>
        <span id="toastMessage">Operation successful</span>
    </div>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Realistic South African Port Data
        const portsData = [{
                id: "ZA-DUR-001",
                name: "Port of Durban",
                province: "KwaZulu-Natal",
                lat: -29.8587,
                lon: 31.0218,
                type: "Commercial",
                status: "active",
                capacity: "2.9 million TEU",
                berths: 58,
                maxDraft: "12.8m",
                operator: "Transnet National Ports Authority",
                description: "Africa's busiest container port and the main gateway for cargo in Southern Africa.",
                facilities: ["Container Terminal", "Bulk Cargo", "Automotive", "Oil Refinery"]
            },
            {
                id: "ZA-CPT-001",
                name: "Port of Cape Town",
                province: "Western Cape",
                lat: -33.9067,
                lon: 18.4233,
                type: "Commercial",
                status: "active",
                capacity: "1.1 million TEU",
                berths: 37,
                maxDraft: "11.0m",
                operator: "Transnet National Ports Authority",
                description: "Major container and fruit export port serving the Western Cape region.",
                facilities: ["Container Terminal", "Fruit Terminal", "Grain Silo", "Oil Basin"]
            },
            {
                id: "ZA-PE-001",
                name: "Port of Gqeberha (Port Elizabeth)",
                province: "Eastern Cape",
                lat: -33.9608,
                lon: 25.6022,
                type: "Commercial",
                status: "active",
                capacity: "850,000 TEU",
                berths: 24,
                maxDraft: "11.5m",
                operator: "Transnet National Ports Authority",
                description: "Key automotive export hub and general cargo port in the Eastern Cape.",
                facilities: ["Automotive Terminal", "Container Terminal", "Manganese Ore", "Fruit"]
            },
            {
                id: "ZA-EL-001",
                name: "Port of East London",
                province: "Eastern Cape",
                lat: -33.0153,
                lon: 27.9116,
                type: "Commercial",
                status: "active",
                capacity: "180,000 TEU",
                berths: 16,
                maxDraft: "10.5m",
                operator: "Transnet National Ports Authority",
                description: "Important automotive and agricultural export port on the east coast.",
                facilities: ["Automotive Terminal", "Grain Terminal", "General Cargo", "Timber"]
            },
            {
                id: "ZA-RB-001",
                name: "Port of Richards Bay",
                province: "KwaZulu-Natal",
                lat: -28.7831,
                lon: 32.0378,
                type: "Industrial",
                status: "active",
                capacity: "91 million tonnes",
                berths: 32,
                maxDraft: "17.0m",
                operator: "Transnet National Ports Authority",
                description: "South Africa's largest coal export terminal and bulk cargo port.",
                facilities: ["Coal Terminal", "Aluminium Smelter", "Iron Ore", "Timber"]
            },
            {
                id: "ZA-SH-001",
                name: "Port of Saldanha Bay",
                province: "Western Cape",
                lat: -33.0117,
                lon: 17.9442,
                type: "Industrial",
                status: "active",
                capacity: "60 million tonnes",
                berths: 12,
                maxDraft: "23.0m",
                operator: "Transnet National Ports Authority",
                description: "Deep-water port primarily for iron ore exports and oil imports.",
                facilities: ["Iron Ore Terminal", "Oil Terminal", "Steel Manufacturing", "Aquaculture"]
            },
            {
                id: "ZA-MOS-001",
                name: "Port of Mossel Bay",
                province: "Western Cape",
                lat: -34.1833,
                lon: 22.1333,
                type: "Fishing",
                status: "active",
                capacity: "500,000 tonnes",
                berths: 8,
                maxDraft: "8.5m",
                operator: "Transnet National Ports Authority",
                description: "Regional port serving fishing industry and offshore gas operations.",
                facilities: ["Fishing Harbour", "Gas Pipeline", "General Cargo", "Yacht Marina"]
            },
            {
                id: "ZA-SIM-001",
                name: "Naval Base Simon's Town",
                province: "Western Cape",
                lat: -34.1928,
                lon: 18.4292,
                type: "Naval",
                status: "active",
                capacity: "N/A",
                berths: 14,
                maxDraft: "9.0m",
                operator: "South African Navy",
                description: "Historic naval base and home of the South African Navy fleet.",
                facilities: ["Naval Dockyard", "Submarine Base", "Training Facility", "Museum"]
            }
        ];
        // Realistic Shipping Routes (Coordinate Paths)
        const shippingRoutes = {
            "asia-route": {
                name: "Asia Express Lane",
                type: "International",
                color: "#2563eb",
                path: [
                    [-29.8587, 31.0218],
                    [-28.5, 32.5],
                    [-25.0, 35.0],
                    [-20.0, 40.0],
                    [-15.0, 45.0],
                    [-10.0, 50.0],
                    [-5.0, 55.0],
                    [0.0, 60.0],
                    [5.0, 65.0],
                    [10.0, 70.0],
                    [15.0, 75.0],
                    [20.0, 80.0],
                    [25.0, 90.0],
                    [30.0, 100.0],
                    [35.0, 110.0],
                    [40.0, 120.0]
                ]
            },
            "europe-route": {
                name: "Europe Atlantic Lane",
                type: "International",
                color: "#dc2626",
                path: [
                    [-33.9067, 18.4233],
                    [-30.0, 15.0],
                    [-25.0, 10.0],
                    [-20.0, 5.0],
                    [-15.0, 0.0],
                    [-10.0, -5.0],
                    [-5.0, -10.0],
                    [0.0, -15.0],
                    [5.0, -20.0],
                    [10.0, -25.0],
                    [20.0, -30.0],
                    [30.0, -35.0],
                    [40.0, -40.0],
                    [50.0, -45.0]
                ]
            },
            "africa-route": {
                name: "Intra-Africa Coastal",
                type: "Regional",
                color: "#059669",
                path: [
                    [-29.8587, 31.0218],
                    [-25.9656, 32.5892],
                    [-25.0, 33.0],
                    [-20.0, 35.0],
                    [-15.0, 37.0],
                    [-10.0, 39.0],
                    [-5.0, 40.0],
                    [0.0, 41.0],
                    [5.0, 42.0],
                    [-4.0435, 39.6682]
                ]
            },
            "coastal-route": {
                name: "SA Coastal Service",
                type: "Domestic",
                color: "#f59e0b",
                path: [
                    [-29.8587, 31.0218],
                    [-30.5, 30.0],
                    [-31.5, 29.0],
                    [-32.5, 28.0],
                    [-33.0, 27.0],
                    [-33.5, 26.0],
                    [-34.0, 24.0],
                    [-34.5, 22.0],
                    [-34.0, 20.0],
                    [-33.9067, 18.4233]
                ]
            },
            "americas-route": {
                name: "Americas Atlantic Lane",
                type: "International",
                color: "#7c3aed",
                path: [
                    [-33.9067, 18.4233],
                    [-35.0, 10.0],
                    [-38.0, 0.0],
                    [-40.0, -10.0],
                    [-42.0, -20.0],
                    [-43.0, -30.0],
                    [-42.0, -40.0],
                    [-40.0, -50.0],
                    [-35.0, -60.0]
                ]
            }
        };
        // Realistic Shipping Lines Operating in South Africa
        const shippingLinesData = [{
                id: "SL-001",
                name: "Maersk South Africa",
                headquarters: "Copenhagen, Denmark",
                saOffice: "Durban, KwaZulu-Natal",
                lat: -29.8587,
                lon: 31.0218,
                type: "Container",
                status: "active",
                founded: "1904",
                vessels: 24,
                routes: ["Asia Express Lane", "Europe Atlantic Lane"],
                routeKeys: ["asia-route", "europe-route"],
                services: ["Container Shipping", "Logistics", "Supply Chain", "Cold Chain"],
                contact: {
                    phone: "+27 31 369 8000",
                    email: "southafrica@maersk.com",
                    website: "www.maersk.com/za"
                },
                description: "Global leader in container shipping with extensive South African operations."
            },
            {
                id: "SL-002",
                name: "MSC South Africa",
                headquarters: "Geneva, Switzerland",
                saOffice: "Cape Town, Western Cape",
                lat: -33.9067,
                lon: 18.4233,
                type: "Container",
                status: "active",
                founded: "1970",
                vessels: 32,
                routes: ["Asia Express Lane", "Americas Atlantic Lane", "Intra-Africa Coastal"],
                routeKeys: ["asia-route", "americas-route", "africa-route"],
                services: ["Container Shipping", "Terminal Operations", "Logistics"],
                contact: {
                    phone: "+27 21 408 3900",
                    email: "capetown@msc.com",
                    website: "www.msc.com/za"
                },
                description: "Mediterranean Shipping Company - one of the largest container lines serving SA."
            },
            {
                id: "SL-003",
                name: "CMA CGM South Africa",
                headquarters: "Marseille, France",
                saOffice: "Durban, KwaZulu-Natal",
                lat: -29.8587,
                lon: 31.0218,
                type: "Container",
                status: "active",
                founded: "1978",
                vessels: 18,
                routes: ["Europe Atlantic Lane", "Asia Express Lane"],
                routeKeys: ["europe-route", "asia-route"],
                services: ["Container Shipping", "Logistics", "Inland Transportation"],
                contact: {
                    phone: "+27 31 369 7200",
                    email: "durban@cmacgm.com",
                    website: "www.cma-cgm.com/za"
                },
                description: "French container line with strong presence in South African trade lanes."
            },
            {
                id: "SL-004",
                name: "Hapag-Lloyd South Africa",
                headquarters: "Hamburg, Germany",
                saOffice: "Johannesburg, Gauteng",
                lat: -26.2041,
                lon: 28.0473,
                type: "Container",
                status: "active",
                founded: "1970",
                vessels: 15,
                routes: ["Europe Atlantic Lane", "Asia Express Lane"],
                routeKeys: ["europe-route", "asia-route"],
                services: ["Container Shipping", "Reefer Services", "Project Cargo"],
                contact: {
                    phone: "+27 11 306 1900",
                    email: "johannesburg@hapag-lloyd.com",
                    website: "www.hapag-lloyd.com/za"
                },
                description: "German container shipping company with comprehensive SA coverage."
            },
            {
                id: "SL-005",
                name: "Safmarine",
                headquarters: "Cape Town, South Africa",
                saOffice: "Cape Town, Western Cape",
                lat: -33.9067,
                lon: 18.4233,
                type: "Multi-Purpose",
                status: "active",
                founded: "1946",
                vessels: 12,
                routes: ["Intra-Africa Coastal", "Europe Atlantic Lane", "SA Coastal Service"],
                routeKeys: ["africa-route", "europe-route", "coastal-route"],
                services: ["Break Bulk", "Project Cargo", "Container", "RoRo"],
                contact: {
                    phone: "+27 21 443 6911",
                    email: "info@safmarine.com",
                    website: "www.safmarine.com"
                },
                description: "South African owned shipping line specializing in African trade."
            },
            {
                id: "SL-006",
                name: "COSCO Shipping South Africa",
                headquarters: "Shanghai, China",
                saOffice: "Durban, KwaZulu-Natal",
                lat: -29.8587,
                lon: 31.0218,
                type: "Container",
                status: "active",
                founded: "1961",
                vessels: 20,
                routes: ["Asia Express Lane", "SA Coastal Service"],
                routeKeys: ["asia-route", "coastal-route"],
                services: ["Container Shipping", "Bulk Cargo", "Logistics"],
                contact: {
                    phone: "+27 31 369 8500",
                    email: "durban@cosco-shipping.com",
                    website: "www.cosco-shipping.com/za"
                },
                description: "Chinese state-owned shipping line with growing SA presence."
            },
            {
                id: "SL-007",
                name: "Grindrod Shipping",
                headquarters: "Hamilton, Bermuda",
                saOffice: "Durban, KwaZulu-Natal",
                lat: -29.8587,
                lon: 31.0218,
                type: "Bulk",
                status: "active",
                founded: "1850",
                vessels: 8,
                routes: ["SA Coastal Service", "Americas Atlantic Lane"],
                routeKeys: ["coastal-route", "americas-route"],
                services: ["Dry Bulk", "Coal", "Iron Ore", "Chrome"],
                contact: {
                    phone: "+27 31 369 7800",
                    email: "info@grindrod.co.za",
                    website: "www.grindrod.co.za"
                },
                description: "Historic South African shipping company specializing in bulk commodities."
            },
            {
                id: "SL-008",
                name: "Delmas (Now part of CMA CGM)",
                headquarters: "Le Havre, France",
                saOffice: "Durban, KwaZulu-Natal",
                lat: -29.8587,
                lon: 31.0218,
                type: "Container",
                status: "inactive",
                founded: "1927",
                vessels: 0,
                routes: [],
                routeKeys: [],
                services: ["Container Shipping", "Intra-Africa Trade"],
                contact: {
                    phone: "+27 31 369 7200",
                    email: "info@delmas.com",
                    website: "www.cma-cgm.com"
                },
                description: "Historic African shipping line, now integrated into CMA CGM Group."
            },
            {
                id: "SL-009",
                name: "Nedlloyd (Now part of Maersk)",
                headquarters: "Rotterdam, Netherlands",
                saOffice: "Cape Town, Western Cape",
                lat: -33.9067,
                lon: 18.4233,
                type: "Container",
                status: "inactive",
                founded: "1970",
                vessels: 0,
                routes: [],
                routeKeys: [],
                services: ["Container Shipping", "Logistics"],
                contact: {
                    phone: "+27 21 408 3900",
                    email: "info@maersk.com",
                    website: "www.maersk.com"
                },
                description: "Former Dutch shipping line, merged with Maersk in 1997."
            },
            {
                id: "SL-010",
                name: "Evergreen South Africa",
                headquarters: "Taipei, Taiwan",
                saOffice: "Durban, KwaZulu-Natal",
                lat: -29.8587,
                lon: 31.0218,
                type: "Container",
                status: "active",
                founded: "1968",
                vessels: 14,
                routes: ["Asia Express Lane", "Europe Atlantic Lane"],
                routeKeys: ["asia-route", "europe-route"],
                services: ["Container Shipping", "Reefer", "Logistics"],
                contact: {
                    phone: "+27 31 369 8200",
                    email: "durban@evergreen-marine.com",
                    website: "www.evergreen-marine.com/za"
                },
                description: "Taiwanese container line with regular services to South Africa."
            }
        ];
        // Realistic Customs Clearing Agents in South Africa
        const clearingAgentsData = [{
                id: "CA-001",
                name: "Bolloré Transport & Logistics South Africa",
                headquarters: "Johannesburg, Gauteng",
                offices: ["Johannesburg", "Durban", "Cape Town", "Gqeberha"],
                lat: -26.2041,
                lon: 28.0473,
                type: "Full Service",
                status: "active",
                founded: "1995",
                employees: "250+",
                licenceno: "CNF-2024-001",
                services: ["Customs Clearing", "Freight Forwarding", "Warehousing", "Transport"],
                ports: ["Durban", "Cape Town", "Johannesburg", "Gqeberha"],
                contact: {
                    phone: "+27 11 306 5000",
                    email: "info.za@bollore.com",
                    website: "www.bollore-logistics.com/za"
                },
                description: "International logistics provider with comprehensive customs clearing services."
            },
            {
                id: "CA-002",
                name: "DHL Global Forwarding South Africa",
                headquarters: "Johannesburg, Gauteng",
                offices: ["Johannesburg", "Durban", "Cape Town", "Pretoria"],
                lat: -26.2041,
                lon: 28.0473,
                type: "Full Service",
                status: "active",
                founded: "1988",
                employees: "400+",
                licenceno: "CNF-2024-002",
                services: ["Air Freight", "Ocean Freight", "Customs Brokerage", "Supply Chain"],
                ports: ["All SA Ports"],
                contact: {
                    phone: "+27 11 928 7000",
                    email: "southafrica@dhl.com",
                    website: "www.dhl.com/za"
                },
                description: "Global freight forwarder with extensive customs clearing capabilities."
            },
            {
                id: "CA-003",
                name: "Kuehne + Nagel South Africa",
                headquarters: "Johannesburg, Gauteng",
                offices: ["Johannesburg", "Durban", "Cape Town"],
                lat: -26.2041,
                lon: 28.0473,
                type: "Full Service",
                status: "active",
                founded: "1992",
                employees: "350+",
                licenceno: "CNF-2024-003",
                services: ["Sea Freight", "Air Freight", "Customs Clearance", "Contract Logistics"],
                ports: ["Durban", "Cape Town", "Richards Bay"],
                contact: {
                    phone: "+27 11 306 8800",
                    email: "za.info@kuehne-nagel.com",
                    website: "www.kuehne-nagel.com/za"
                },
                description: "Swiss logistics company with strong customs brokerage services."
            },
            {
                id: "CA-004",
                name: "DB Schenker South Africa",
                headquarters: "Johannesburg, Gauteng",
                offices: ["Johannesburg", "Durban", "Cape Town", "Gqeberha"],
                lat: -26.2041,
                lon: 28.0473,
                type: "Full Service",
                status: "active",
                founded: "1996",
                employees: "300+",
                licenceno: "CNF-2024-004",
                services: ["Ocean Freight", "Air Freight", "Customs Brokerage", "Land Transport"],
                ports: ["All SA Ports"],
                contact: {
                    phone: "+27 11 306 7700",
                    email: "southafrica@dbschenker.com",
                    website: "www.dbschenker.com/za"
                },
                description: "German logistics provider with comprehensive customs solutions."
            },
            {
                id: "CA-005",
                name: "Bidvest Freight & Logistics",
                headquarters: "Johannesburg, Gauteng",
                offices: ["Johannesburg", "Durban", "Cape Town", "Pretoria", "Gqeberha"],
                lat: -26.2041,
                lon: 28.0473,
                type: "Full Service",
                status: "active",
                founded: "1985",
                employees: "500+",
                licenceno: "CNF-2024-005",
                services: ["Customs Clearing", "Freight Forwarding", "Warehousing", "Distribution"],
                ports: ["All SA Ports"],
                contact: {
                    phone: "+27 11 401 6900",
                    email: "info@bidvestfreight.co.za",
                    website: "www.bidvestfreight.co.za"
                },
                description: "South African owned logistics company with extensive clearing network."
            },
            {
                id: "CA-006",
                name: "Grindrod Logistics",
                headquarters: "Durban, KwaZulu-Natal",
                offices: ["Durban", "Johannesburg", "Cape Town"],
                lat: -29.8587,
                lon: 31.0218,
                type: "Full Service",
                status: "active",
                founded: "1850",
                employees: "450+",
                licenceno: "CNF-2024-006",
                services: ["Customs Brokerage", "Freight Management", "Warehousing", "Transport"],
                ports: ["Durban", "Richards Bay", "Cape Town"],
                contact: {
                    phone: "+27 31 369 7800",
                    email: "info@grindrod.co.za",
                    website: "www.grindrod.co.za"
                },
                description: "Historic South African company with deep roots in customs clearing."
            },
            {
                id: "CA-007",
                name: "Imperial Logistics Customs Clearing",
                headquarters: "Johannesburg, Gauteng",
                offices: ["Johannesburg", "Durban", "Cape Town", "Gqeberha", "East London"],
                lat: -26.2041,
                lon: 28.0473,
                type: "Full Service",
                status: "active",
                founded: "1980",
                employees: "600+",
                licenceno: "CNF-2024-007",
                services: ["Customs Clearing", "Import/Export", "Bonded Warehousing", "Transit"],
                ports: ["All SA Ports"],
                contact: {
                    phone: "+27 11 492 3000",
                    email: "customs@imperiallogistics.com",
                    website: "www.imperiallogistics.com"
                },
                description: "Leading African logistics provider with comprehensive customs services."
            },
            {
                id: "CA-008",
                name: "Agility Logistics South Africa",
                headquarters: "Johannesburg, Gauteng",
                offices: ["Johannesburg", "Durban", "Cape Town"],
                lat: -26.2041,
                lon: 28.0473,
                type: "Full Service",
                status: "active",
                founded: "2005",
                employees: "200+",
                licenceno: "CNF-2024-008",
                services: ["Customs Brokerage", "Freight Forwarding", "Project Logistics", "Warehousing"],
                ports: ["Durban", "Cape Town", "Johannesburg"],
                contact: {
                    phone: "+27 11 306 9500",
                    email: "southafrica@agility.com",
                    website: "www.agility.com/za"
                },
                description: "Kuwait-based logistics company with growing SA customs operations."
            },
            {
                id: "CA-009",
                name: "CEVA Logistics South Africa",
                headquarters: "Johannesburg, Gauteng",
                offices: ["Johannesburg", "Durban", "Cape Town", "Pretoria"],
                lat: -26.2041,
                lon: 28.0473,
                type: "Full Service",
                status: "active",
                founded: "2000",
                employees: "280+",
                licenceno: "CNF-2024-009",
                services: ["Customs Clearance", "Freight Management", "Supply Chain", "Automotive Logistics"],
                ports: ["All SA Ports"],
                contact: {
                    phone: "+27 11 306 8200",
                    email: "southafrica@cevalogistics.com",
                    website: "www.cevalogistics.com/za"
                },
                description: "French logistics provider with strong automotive customs expertise."
            },
            {
                id: "CA-010",
                name: "Peggy Wilmore Customs Clearing",
                headquarters: "Durban, KwaZulu-Natal",
                offices: ["Durban", "Johannesburg", "Cape Town"],
                lat: -29.8587,
                lon: 31.0218,
                type: "Specialized",
                status: "active",
                founded: "1972",
                employees: "150+",
                licenceno: "CNF-2024-010",
                services: ["Customs Clearing", "Permits", "Quotations", "Rebates"],
                ports: ["Durban", "Richards Bay", "Johannesburg"],
                contact: {
                    phone: "+27 31 369 7500",
                    email: "info@peggywilmore.co.za",
                    website: "www.peggywilmore.co.za"
                },
                description: "Specialized South African customs clearing agent with 50+ years experience."
            },
            {
                id: "CA-011",
                name: "Barloworld Logistics Customs",
                headquarters: "Johannesburg, Gauteng",
                offices: ["Johannesburg", "Durban", "Cape Town", "Gqeberha"],
                lat: -26.2041,
                lon: 28.0473,
                type: "Full Service",
                status: "active",
                founded: "1990",
                employees: "320+",
                licenceno: "CNF-2024-011",
                services: ["Customs Brokerage", "Import/Export", "Bonded Storage", "Transit Cargo"],
                ports: ["All SA Ports"],
                contact: {
                    phone: "+27 11 306 7000",
                    email: "customs@barloworld.com",
                    website: "www.barloworldlogistics.com"
                },
                description: "South African conglomerate with comprehensive customs clearing division."
            },
            {
                id: "CA-012",
                name: "Nippon Express South Africa",
                headquarters: "Johannesburg, Gauteng",
                offices: ["Johannesburg", "Durban", "Cape Town"],
                lat: -26.2041,
                lon: 28.0473,
                type: "Full Service",
                status: "active",
                founded: "2008",
                employees: "180+",
                licenceno: "CNF-2024-012",
                services: ["Customs Clearance", "Air Freight", "Ocean Freight", "Warehousing"],
                ports: ["Durban", "Cape Town", "Johannesburg"],
                contact: {
                    phone: "+27 11 306 8900",
                    email: "southafrica@nipponexpress.com",
                    website: "www.nipponexpress.com/za"
                },
                description: "Japanese logistics company with specialized Asian trade customs expertise."
            }
        ];
        // Global state
        let currentTab = 'ports';
        let allData = [];
        let filteredData = [];
        let map = null;
        let markers = [];
        let routeLayers = [];
        let selectedItem = null;
        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            initMap();
            switchTab('ports');
        });
        // Initialize Leaflet Map
        function initMap() {
            map = L.map('map').setView([-30.5595, 22.9375], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
        }
        // Switch Tab
        function switchTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
            document.querySelector(`[onclick="switchTab('${tab}')"]`).classList.add('active');
            const titles = {
                'ports': '📍 South African Ports',
                'shipping': '🚢 Shipping Lines',
                'agents': '📋 Clearing Agents'
            };
            document.getElementById('panelTitle').textContent = titles[tab];
            const placeholders = {
                'ports': 'Search ports by name or province...',
                'shipping': 'Search shipping lines by name...',
                'agents': 'Search clearing agents by name...'
            };
            document.getElementById('searchInput').placeholder = placeholders[tab];
            clearRoutes();
            loadData();
        }
        // Load Data
        function loadData() {
            const dataList = document.getElementById('dataList');
            dataList.innerHTML = `
                <div class="loading">
                <div class="loading-spinner"></div>
                <p>Loading data...</p>
                </div>
                `;
            setTimeout(() => {
                switch (currentTab) {
                    case 'ports':
                        allData = [...portsData];
                        updateStats('ports');
                        break;
                    case 'shipping':
                        allData = [...shippingLinesData];
                        updateStats('shipping');
                        break;
                    case 'agents':
                        allData = [...clearingAgentsData];
                        updateStats('agents');
                        break;
                }
                filteredData = [...allData];
                updateFilters();
                updateDataList();
                updateMapMarkers();
                showToast('Data loaded successfully!', 'success');
            }, 500);
        }
        // Update Stats
        function updateStats(type) {
            const stats = {
                'ports': {
                    stat1: allData.length,
                    stat1Label: 'Total Ports',
                    stat2: '5.03M',
                    stat2Label: 'Annual TEU',
                    stat3: allData.filter(p => p.status === 'active').length,
                    stat3Label: 'Operational',
                    stat4: '3,000km',
                    stat4Label: 'Coastline'
                },
                'shipping': {
                    stat1: allData.length,
                    stat1Label: 'Shipping Lines',
                    stat2: allData.reduce((sum, s) => sum + s.vessels, 0),
                    stat2Label: 'Total Vessels',
                    stat3: allData.filter(s => s.status === 'active').length,
                    stat3Label: 'Active Lines',
                    stat4: '50+',
                    stat4Label: 'Countries'
                },
                'agents': {
                    stat1: allData.length,
                    stat1Label: 'Clearing Agents',
                    stat2: allData.reduce((sum, a) => sum + parseInt(a.employees), 0).toLocaleString(),
                    stat2Label: 'Total Employees',
                    stat3: allData.filter(a => a.status === 'active').length,
                    stat3Label: 'Licensed',
                    stat4: '12',
                    stat4Label: 'Major Cities'
                }
            };
            const s = stats[type];
            document.getElementById('stat1').textContent = s.stat1;
            document.getElementById('stat1Label').textContent = s.stat1Label;
            document.getElementById('stat2').textContent = s.stat2;
            document.getElementById('stat2Label').textContent = s.stat2Label;
            document.getElementById('stat3').textContent = s.stat3;
            document.getElementById('stat3Label').textContent = s.stat3Label;
            document.getElementById('stat4').textContent = s.stat4;
            document.getElementById('stat4Label').textContent = s.stat4Label;
        }
        // Update Filters
        function updateFilters() {
            const filter1 = document.getElementById('filter1');
            const filter2 = document.getElementById('filter2');
            if (currentTab === 'ports') {
                filter1.innerHTML = '<option value="">All Provinces</option>' + [...new Set(allData.map(p => p.province))]
                    .sort().map(p =>
                        `<option value="${p}">${p}</option>`
                    ).join('');
                filter2.innerHTML = '<option value="">All Types</option>' + [...new Set(allData.map(p => p.type))].sort()
                    .map(t =>
                        `<option value="${t}">${t}</option>`
                    ).join('');
            } else if (currentTab === 'shipping') {
                filter1.innerHTML = '<option value="">All Types</option>' + [...new Set(allData.map(s => s.type))].sort()
                    .map(t =>
                        `<option value="${t}">${t}</option>`
                    ).join('');
                filter2.innerHTML = '<option value="">All Status</option>' + [...new Set(allData.map(s => s.status))].sort()
                    .map(s =>
                        `<option value="${s}">${s.charAt(0).toUpperCase() + s.slice(1)}</option>`
                    ).join('');
            } else if (currentTab === 'agents') {
                filter1.innerHTML = '<option value="">All Types</option>' + [...new Set(allData.map(a => a.type))].sort()
                    .map(t =>
                        `<option value="${t}">${t}</option>`
                    ).join('');
                filter2.innerHTML = '<option value="">All Cities</option>' + ['Johannesburg', 'Durban', 'Cape Town',
                    'Gqeberha', 'Pretoria', 'East London'
                ].map(c =>
                    `<option value="${c}">${c}</option>`
                ).join('');
            }
        }
        // Update Data List
        function updateDataList() {
            const dataList = document.getElementById('dataList');
            document.getElementById('dataCount').textContent = `${filteredData.length} items`;
            if (filteredData.length === 0) {
                dataList.innerHTML = `
                    <div class="empty-state">
                    <div class="icon">🔍</div>
                    <h3>No Items Found</h3>
                    <p>Try adjusting your search or filter criteria.</p>
                    </div>
                    `;
                return;
            }
            if (currentTab === 'ports') {
                dataList.innerHTML = filteredData.map((item, index) => `
                    <div class="data-item ${selectedItem?.id === item.id ? 'active' : ''}"
                    onclick="selectItem(${index})" data-id="${item.id}">
                    <div class="data-name">
                    <span class="icon">⚓</span>
                    ${escapeHtml(item.name)}
                    <span class="data-status status-${item.status}">${item.status.toUpperCase()}</span>
                    </div>
                    <div class="data-details">
                    <span class="data-detail">📍 ${escapeHtml(item.province)}</span>
                    <span class="data-detail">🏷️ ${escapeHtml(item.type)}</span>
                    <span class="data-detail">📊 ${item.capacity}</span>
                    </div>
                    <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
                    ${item.facilities.slice(0, 3).map(f => `<span class="data-tag">${escapeHtml(f)}</span>`).join('')}
                    </div>
                    </div>
                    `).join('');
                                } else if (currentTab === 'shipping') {
                                    dataList.innerHTML = filteredData.map((item, index) => `
                    <div class="data-item ${selectedItem?.id === item.id ? 'active' : ''}"
                    onclick="selectItem(${index})" data-id="${item.id}">
                    <div class="data-name">
                    <span class="icon">🚢</span>
                    ${escapeHtml(item.name)}
                    <span class="data-status status-${item.status}">${item.status.toUpperCase()}</span>
                    </div>
                    <div class="data-details">
                    <span class="data-detail">🏢 ${escapeHtml(item.saOffice)}</span>
                    <span class="data-detail">🚢 ${item.vessels} Vessels</span>
                    <span class="data-detail">📅 ${item.founded}</span>
                    </div>
                    <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
                    ${item.services.slice(0, 3).map(s => `<span class="data-tag">${escapeHtml(s)}</span>`).join('')}
                    </div>
                    </div>
                    `).join('');
                                } else if (currentTab === 'agents') {
                                    dataList.innerHTML = filteredData.map((item, index) => `
                    <div class="data-item ${selectedItem?.id === item.id ? 'active' : ''}"
                    onclick="selectItem(${index})" data-id="${item.id}">
                    <div class="data-name">
                    <span class="icon">📋</span>
                    ${escapeHtml(item.name)}
                    <span class="data-status status-${item.status}">${item.status.toUpperCase()}</span>
                    </div>
                    <div class="data-details">
                    <span class="data-detail">🏢 ${escapeHtml(item.headquarters)}</span>
                    <span class="data-detail">👥 ${item.employees}</span>
                    <span class="data-detail">📜 ${item.licenceno}</span>
                    </div>
                    <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
                    ${item.services.slice(0, 3).map(s => `<span class="data-tag">${escapeHtml(s)}</span>`).join('')}
                    </div>
                    </div>
                    `).join('');
            }
        }
        // Clear Routes
        function clearRoutes() {
            routeLayers.forEach(layer => map.removeLayer(layer));
            routeLayers = [];
            document.getElementById('routeInfo').style.display = 'none';
        }
        // Update Map Markers
        function updateMapMarkers() {
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
            clearRoutes();
            filteredData.forEach(item => {
                const markerColor = item.status === 'active' ? '#007749' : '#f59e0b';
                const customIcon = L.divIcon({
                    html: `<div style="background: ${markerColor}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                    className: 'custom-marker',
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                });
                const marker = L.marker([item.lat, item.lon], {
                        icon: customIcon
                    })
                    .addTo(map)
                    .bindPopup(
                        `<b>${escapeHtml(item.name)}</b><br>${currentTab === 'ports' ? item.province : item.saOffice || item.headquarters}`
                        );
                markers.push(marker);
            });
            if (markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds(), {
                    padding: [50, 50],
                    maxZoom: 8
                });
            }
        }
        // Draw Routes for Shipping Line
        function drawRoutes(item) {
            clearRoutes();
            if (!item.routeKeys || item.routeKeys.length === 0) {
                document.getElementById('routeInfo').style.display = 'none';
                return;
            }
            const routeList = document.getElementById('routeList');
            routeList.innerHTML = '';
            item.routeKeys.forEach(key => {
                const route = shippingRoutes[key];
                if (route) {
                    const polyline = L.polyline(route.path, {
                        color: route.color,
                        weight: 3,
                        opacity: 0.7,
                        dashArray: '10, 10',
                        lineCap: 'round'
                    }).addTo(map);
                    routeLayers.push(polyline);
                    const routeItem = document.createElement('div');
                    routeItem.className = 'route-item';
                    routeItem.innerHTML = `
<div class="route-color" style="background: ${route.color}"></div>
<span class="route-name">${route.name}</span>
<span class="route-type">${route.type}</span>
`;
                    routeList.appendChild(routeItem);
                    polyline.bindPopup(`<b>${route.name}</b><br>Type: ${route.type}`);
                }
            });
            document.getElementById('routeInfo').style.display = 'block';
            if (routeLayers.length > 0) {
                const group = new L.featureGroup([...markers, ...routeLayers]);
                map.fitBounds(group.getBounds(), {
                    padding: [50, 50]
                });
            }
        }
        // Select Item
        function selectItem(index) {
            const item = filteredData[index];
            selectedItem = item;
            document.querySelectorAll('.data-item').forEach(i => {
                i.classList.remove('active');
            });
            document.querySelector(`[data-id="${item.id}"]`)?.classList.add('active');
            map.setView([item.lat, item.lon], 9);
            document.getElementById('infoTitle').textContent = currentTab === 'ports' ? '🗺️ Port Location' :
                currentTab === 'shipping' ? '🏢 Company Location' : '🏢 Office Location';
            document.getElementById('infoDescription').innerHTML = `
<strong>${escapeHtml(item.name)}</strong><br>
${currentTab === 'ports' ? item.description : item.description}
`;
            document.getElementById('coordinates').style.display = 'block';
            document.getElementById('coordinates').textContent =
                `Lat: ${item.lat.toFixed(6)} | Lng: ${item.lon.toFixed(6)}`;
            const statsDiv = document.getElementById('infoStats');
            statsDiv.style.display = 'grid';
            if (currentTab === 'ports') {
                statsDiv.innerHTML = `
                    <div class="info-stat">
                    <div class="label">Capacity</div>
                    <div class="value">${item.capacity}</div>
                    </div>
                    <div class="info-stat">
                    <div class="label">Berths</div>
                    <div class="value">${item.berths}</div>
                    </div>
                    <div class="info-stat">
                    <div class="label">Max Draft</div>
                    <div class="value">${item.maxDraft}</div>
                    </div>
                    <div class="info-stat">
                    <div class="label">Operator</div>
                    <div class="value">${item.operator}</div>
                    </div>
                    `;
                document.getElementById('contactInfo').style.display = 'none';
                document.getElementById('routeInfo').style.display = 'none';
            } else if (currentTab === 'shipping') {
                statsDiv.innerHTML = `
                        <div class="info-stat">
                        <div class="label">Vessels</div>
                        <div class="value">${item.vessels}</div>
                        </div>
                        <div class="info-stat">
                        <div class="label">Founded</div>
                        <div class="value">${item.founded}</div>
                        </div>
                        <div class="info-stat">
                        <div class="label">Type</div>
                        <div class="value">${item.type}</div>
                        </div>
                        <div class="info-stat">
                        <div class="label">HQ</div>
                        <div class="value">${item.headquarters}</div>
                        </div>
                        `;
                document.getElementById('contactInfo').style.display = 'block';
                document.getElementById('contactInfo').innerHTML = `
                            <div class="item">📞 ${item.contact.phone}</div>
                            <div class="item">✉️ ${item.contact.email}</div>
                            <div class="item">🌐 ${item.contact.website}</div>
                            `;
                                            drawRoutes(item);
                                        } else if (currentTab === 'agents') {
                                            statsDiv.innerHTML = `
                            <div class="info-stat">
                            <div class="label">Employees</div>
                            <div class="value">${item.employees}</div>
                            </div>
                            <div class="info-stat">
                            <div class="label">License</div>
                            <div class="value">${item.licenceno}</div>
                            </div>
                            <div class="info-stat">
                            <div class="label">Type</div>
                            <div class="value">${item.type}</div>
                            </div>
                            <div class="info-stat">
                            <div class="label">Offices</div>
                            <div class="value">${item.offices.length}</div>
                            </div>
                            `;
                document.getElementById('contactInfo').style.display = 'block';
                document.getElementById('contactInfo').innerHTML = `
                    <div class="item">📞 ${item.contact.phone}</div>
                    <div class="item">✉️ ${item.contact.email}</div>
                    <div class="item">🌐 ${item.contact.website}</div>
                    `;
                document.getElementById('routeInfo').style.display = 'none';
            }
            showToast(`Selected ${item.name}`, 'success');
        }
        // Search Data
        function searchData() {
            const query = document.getElementById('searchInput').value.toLowerCase().trim();
            if (!query) {
                filteredData = [...allData];
            } else {
                filteredData = allData.filter(item => {
                    const searchable = Object.values(item).join(' ').toLowerCase();
                    return searchable.includes(query);
                });
            }
            updateDataList();
            updateMapMarkers();
            showToast(`Found ${filteredData.length} items`, 'success');
        }
        // Reset Search
        function resetSearch() {
            document.getElementById('searchInput').value = '';
            document.getElementById('filter1').value = '';
            document.getElementById('filter2').value = '';
            filteredData = [...allData];
            updateDataList();
            updateMapMarkers();
            showToast('Search reset', 'success');
        }
        // Apply Filters
        function applyFilters() {
            const filter1 = document.getElementById('filter1').value;
            const filter2 = document.getElementById('filter2').value;
            const searchQuery = document.getElementById('searchInput').value.toLowerCase().trim();
            filteredData = allData.filter(item => {
                let matches = true;
                if (currentTab === 'ports') {
                    if (filter1) matches = matches && item.province === filter1;
                    if (filter2) matches = matches && item.type === filter2;
                } else if (currentTab === 'shipping') {
                    if (filter1) matches = matches && item.type === filter1;
                    if (filter2) matches = matches && item.status === filter2;
                } else if (currentTab === 'agents') {
                    if (filter1) matches = matches && item.type === filter1;
                    if (filter2) matches = matches && item.offices.includes(filter2);
                }
                if (searchQuery) {
                    const searchable = Object.values(item).join(' ').toLowerCase();
                    matches = matches && searchable.includes(searchQuery);
                }
                return matches;
            });
            updateDataList();
            updateMapMarkers();
        }
        // Refresh Data
        function refreshData() {
            loadData();
        }
        // Show Toast
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastIcon = document.getElementById('toastIcon');
            const toastMessage = document.getElementById('toastMessage');
            toastIcon.textContent = type === 'success' ? '✓' : '✗';
            toastMessage.textContent = message;
            toast.className = `toast ${type} show`;
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
        // Escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>

</html>
