{{-- <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>AIS Stream Test</title>
    <style>
        body { font-family: Arial, sans-serif; background:#111; color:#eee; padding:20px; }
        #log { white-space: pre-wrap; background:#222; padding:15px; border-radius:8px; max-height:500px; overflow:auto; font-family: monospace; font-size: 12px; }
        button { padding:10px 20px; margin-bottom:15px; margin-right:10px; cursor: pointer; }
        button:disabled { opacity: 0.5; cursor: not-allowed; }
        .status { color: #0f0; font-weight: bold; margin-bottom: 10px; }
        .error { color: #f66; }
        .warning { color: #ff0; }
    </style>
</head>
<body>

<h2>AIS Stream WebSocket Test</h2>
<div id="status" class="status">Status: Disconnected</div>
<button id="connectBtn" onclick="connectStream()">Connect</button>
<button id="disconnectBtn" onclick="disconnectStream()" disabled>Disconnect</button>
<button onclick="clearLog()">Clear Log</button>

<div id="log"></div>

<script>
    let socket;
    const API_KEY = "301ebf4ecc5fefa429a92f5a7288a6c0ccb9fe3b"; // ⚠️ WARNING: Never expose API keys in client-side code in production!

    function log(message, type = 'info') {
        const logDiv = document.getElementById("log");
        const timestamp = new Date().toLocaleTimeString();
        const prefix = type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️';
        
        // Use textContent for safety (prevents XSS) and create formatted line
        const line = document.createElement('div');
        line.className = type;
        line.textContent = `[${timestamp}] ${prefix} ${message}`;
        
        logDiv.appendChild(line);
        logDiv.scrollTop = logDiv.scrollHeight;
    }

    function updateStatus(status, isError = false) {
        const statusDiv = document.getElementById("status");
        statusDiv.textContent = `Status: ${status}`;
        statusDiv.className = isError ? 'status error' : 'status';
    }

    function updateButtons(connected) {
        document.getElementById("connectBtn").disabled = connected;
        document.getElementById("disconnectBtn").disabled = !connected;
    }

    function clearLog() {
        document.getElementById("log").innerHTML = '';
        log("Log cleared.");
    }

    function connectStream() {
        if (socket && socket.readyState === WebSocket.OPEN) {
            log("Already connected.", "warning");
            return;
        }

        // Clear previous socket if exists
        if (socket) {
            socket.close();
        }

        log("Connecting to AIS Stream...");
        updateStatus("Connecting...");
        updateButtons(false);

        try {
            socket = new WebSocket("wss://stream.aisstream.io/v0/stream");
        } catch (e) {
            log(`Failed to create WebSocket: ${e.message}`, "error");
            updateStatus("Connection Failed", true);
            updateButtons(false);
            return;
        }

        socket.onopen = function () {
            log("✅ Connected to AIS Stream.");
            updateStatus("Connected");
            updateButtons(true);

            const subscriptionMessage = {
                Apikey: API_KEY,
                BoundingBoxes: [[[ -90, -180 ], [ 90, 180 ]]],
                FiltersShipMMSI: ["368207620", "367719770", "211476060"],
                FilterMessageTypes: ["PositionReport"]
            };

            try {
                socket.send(JSON.stringify(subscriptionMessage));
                log("✅ Subscription message sent.");
                log(`📦 Payload: ${JSON.stringify(subscriptionMessage)}`);
            } catch (e) {
                log(`❌ Failed to send subscription: ${e.message}`, "error");
            }
        };

        socket.onmessage = function (event) {
            try {
                const aisMessage = JSON.parse(event.data);
                log("📩 Received message:");
                log(JSON.stringify(aisMessage, null, 2));
            } catch (e) {
                log(`❌ Failed to parse message: ${e.message}`, "error");
                log(`Raw data: ${event.data}`, "warning");
            }
        };

        socket.onerror = function (error) {
            // WebSocket error event doesn't have .message property
            // Check readyState for more context
            let state = "UNKNOWN";
            if (socket) {
                switch(socket.readyState) {
                    case WebSocket.CONNECTING: state = "CONNECTING"; break;
                    case WebSocket.OPEN: state = "OPEN"; break;
                    case WebSocket.CLOSING: state = "CLOSING"; break;
                    case WebSocket.CLOSED: state = "CLOSED"; break;
                }
            }
            log(`❌ WebSocket error occurred (State: ${state})`, "error");
            log(`Error event: ${JSON.stringify(error)}`, "warning");
            updateStatus("Error", true);
        };

        socket.onclose = function (event) {
            log(`🔌 Disconnected (Code: ${event.code}, Reason: ${event.reason || 'No reason provided'})`);
            updateStatus("Disconnected");
            updateButtons(false);
            socket = null;
        };

    }

    function disconnectStream() {
        if (socket) {
            log("Manually disconnecting...");
            socket.close(1000, "User requested disconnect");
        } else {
            log("No active connection to close.", "warning");
        }
    }

    // Initial log
    log("Page loaded. Click 'Connect' to start.");
</script>

</body>
</html> --}}



















<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>AIS Stream + RIAC Meter Data Test</title>
    <style>
        body { font-family: Arial, sans-serif; background:#111; color:#eee; padding:20px; margin:0; }
        .container { max-width: 1200px; margin: 0 auto; }
        h2 { border-bottom: 2px solid #444; padding-bottom: 10px; margin-top: 30px; }
        
        /* Tabs */
        .tabs { display: flex; gap: 5px; margin-bottom: 15px; }
        .tab-btn { 
            padding: 10px 20px; 
            background: #333; 
            border: none; 
            color: #eee; 
            cursor: pointer; 
            border-radius: 5px 5px 0 0;
            font-weight: bold;
        }
        .tab-btn.active { background: #0066cc; color: white; }
        .tab-content { display: none; background: #1a1a1a; padding: 15px; border-radius: 0 8px 8px 8px; }
        .tab-content.active { display: block; }
        
        /* Forms */
        .form-group { margin: 10px 0; }
        .form-group label { display: block; margin-bottom: 5px; color: #aaa; }
        .form-group input, .form-group select { 
            width: 100%; max-width: 400px; 
            padding: 8px; 
            background: #222; 
            border: 1px solid #444; 
            color: #eee; 
            border-radius: 4px;
        }
        .form-row { display: flex; gap: 15px; flex-wrap: wrap; }
        .form-row .form-group { flex: 1; min-width: 200px; }
        
        /* Buttons */
        button { 
            padding: 10px 20px; 
            margin: 5px 5px 5px 0; 
            background: #0066cc; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer;
        }
        button:hover { background: #0055aa; }
        button:disabled { opacity: 0.5; cursor: not-allowed; }
        button.danger { background: #cc3333; }
        button.danger:hover { background: #aa2222; }
        button.success { background: #33aa33; }
        button.success:hover { background: #229922; }
        
        /* Log */
        #log { 
            white-space: pre-wrap; 
            background:#1a1a1a; 
            padding:15px; 
            border-radius:8px; 
            max-height:500px; 
            overflow:auto; 
            font-family: monospace; 
            font-size: 11px;
            border: 1px solid #333;
        }
        .log-entry { margin: 2px 0; line-height: 1.4; }
        .log-timestamp { color: #666; margin-right: 8px; }
        .log-info { color: #6cf; }
        .log-success { color: #6f6; }
        .log-warning { color: #ff6; }
        .log-error { color: #f66; }
        .log-debug { color: #99f; }
        .log-json { color: #9cf; margin-left: 20px; }
        
        /* Status */
        .status-bar { 
            padding: 8px 15px; 
            background: #222; 
            border-radius: 4px; 
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status-dot { 
            width: 10px; height: 10px; 
            border-radius: 50%; 
            background: #666;
        }
        .status-dot.connected { background: #3f3; }
        .status-dot.error { background: #f33; }
        .status-dot.connecting { background: #ff3; animation: pulse 1s infinite; }
        
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
        
        /* Data display */
        .data-section { 
            background: #222; 
            padding: 12px; 
            border-radius: 6px; 
            margin: 10px 0;
            border-left: 3px solid #0066cc;
        }
        .data-section h4 { margin: 0 0 10px 0; color: #00aaff; }
        .meter-card { 
            background: #2a2a2a; 
            padding: 10px; 
            margin: 8px 0; 
            border-radius: 4px;
            cursor: pointer;
        }
        .meter-card:hover { background: #333; }
        .sensor-data { 
            max-height: 300px; 
            overflow: auto; 
            font-size: 10px;
            background: #111;
            padding: 10px;
            border-radius: 4px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-row { flex-direction: column; }
            .tabs { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🛰️ AIS Stream + ⚡ RIAC Meter Data Test</h1>
    
    <div class="status-bar">
        <div id="aisStatusDot" class="status-dot"></div>
        <span id="aisStatusText">AIS: Disconnected</span>
        <span style="margin-left:20px; color:#666;">|</span>
        <div id="riacStatusDot" class="status-dot"></div>
        <span id="riacStatusText">RIAC: Not Authenticated</span>
    </div>
    
    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('ais')">🛰️ AIS Stream</button>
        <button class="tab-btn" onclick="switchTab('riac')">⚡ RIAC Meter API</button>
        <button class="tab-btn" onclick="switchTab('debug')">🐛 Debug Log</button>
    </div>
    
    <!-- AIS Stream Tab -->
    <div id="ais-tab" class="tab-content active">
        <h3>AIS Stream WebSocket</h3>
        <div class="form-group">
            <label>API Key (⚠️ Never expose in production):</label>
            <input type="password" id="aisApiKey" value="301ebf4ecc5fefa429a92f5a7288a6c0ccb9fe3b">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Bounding Box (Min Lat, Min Lon):</label>
                <input type="text" id="bboxMin" value="-90, -180">
            </div>
            <div class="form-group">
                <label>Bounding Box (Max Lat, Max Lon):</label>
                <input type="text" id="bboxMax" value="90, 180">
            </div>
        </div>
        <div class="form-group">
            <label>Filter Ship MMSI (comma-separated, optional):</label>
            <input type="text" id="filterMMSI" value="368207620, 367719770, 211476060">
        </div>
        <div>
            <button onclick="connectAIS()">🔗 Connect</button>
            <button onclick="disconnectAIS()" class="danger">✖ Disconnect</button>
            <button onclick="clearLog()">🗑️ Clear Log</button>
        </div>
        <div class="data-section">
            <h4>📡 Connection Details</h4>
            <div id="aisDetails">Not connected</div>
        </div>
    </div>
    
    <!-- RIAC Meter API Tab -->
    <div id="riac-tab" class="tab-content">
        <h3>RIAC Meter Data API</h3>
        
        <!-- Authentication -->
        <div class="data-section">
            <h4>🔐 Authentication</h4>
            <div class="form-row">
                <div class="form-group">
                    <label>Username (Email):</label>
                    <input type="email" id="riacUsername" placeholder="your@email.com">
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" id="riacPassword">
                </div>
            </div>
            <div class="form-group">
                <label>Application Name:</label>
                <input type="text" id="riacApplication" value="TestApp">
            </div>
            <div class="form-group">
                <label>Client ID (fixed):</label>
                <input type="text" value="1A03BDE4-2EEA-4140-8295-76FEFD35B382" disabled>
            </div>
            <button onclick="authenticateRIAC()" class="success">🔑 Get Token</button>
            <button onclick="clearRIACAuth()" class="danger">🚫 Clear Auth</button>
        </div>
        
        <!-- Get Meters -->
        <div class="data-section" id="riacMetersSection" style="display:none;">
            <h4>📊 Get Meters</h4>
            <button onclick="getMeters()">📋 Fetch Meter List</button>
            <div id="metersList" style="margin-top:10px; max-height:200px; overflow:auto;"></div>
        </div>
        
        <!-- Get Specific Meter -->
        <div class="data-section" id="riacMeterDetailSection" style="display:none;">
            <h4>🔍 Get Specific Meter</h4>
            <div class="form-group">
                <label>Meter ID:</label>
                <input type="number" id="meterIdInput" placeholder="Enter Meter ID">
            </div>
            <button onclick="getSpecificMeter()">🔎 Fetch Meter Details</button>
            <div id="meterDetail" style="margin-top:10px;"></div>
        </div>
        
        <!-- Get Meter Sensor Data -->
        <div class="data-section" id="riacDataSection" style="display:none;">
            <h4>📈 Get Meter Sensor Data</h4>
            <div class="form-row">
                <div class="form-group">
                    <label>Meter ID:</label>
                    <input type="number" id="dataMeterId" placeholder="Enter Meter ID">
                </div>
                <div class="form-group">
                    <label>Period Start:</label>
                    <input type="datetime-local" id="periodStart">
                </div>
                <div class="form-group">
                    <label>Period End:</label>
                    <input type="datetime-local" id="periodEnd">
                </div>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="includeProcessed" checked> Include Processed Data
                </label>
            </div>
            <button onclick="getMeterSensorData()">📊 Fetch Sensor Data</button>
            <div id="sensorDataOutput" class="sensor-data" style="margin-top:10px;"></div>
        </div>
    </div>
    
    <!-- Debug Log Tab -->
    <div id="debug-tab" class="tab-content">
        <h3>🐛 Debug Log (All Messages)</h3>
        <div id="log"></div>
    </div>
    
</div>

<script>
    // ==================== GLOBAL STATE ====================
    let aisSocket = null;
    let riacToken = null;
    let riacTokenExpiry = null;
    
    // ==================== LOGGING UTILITIES ====================
    function log(message, type = 'info', data = null) {
        const logDiv = document.getElementById("log");
        const timestamp = new Date().toLocaleTimeString('en-US', { hour12: false });
        const prefix = {
            'info': 'ℹ️',
            'success': '✅',
            'warning': '⚠️',
            'error': '❌',
            'debug': '🔍'
        }[type] || 'ℹ️';
        
        const entry = document.createElement('div');
        entry.className = `log-entry log-${type}`;
        entry.innerHTML = `<span class="log-timestamp">[${timestamp}]</span>${prefix} ${escapeHtml(message)}`;
        
        if (data !== null) {
            const jsonPre = document.createElement('pre');
            jsonPre.className = 'log-json';
            jsonPre.textContent = typeof data === 'object' ? JSON.stringify(data, null, 2) : data;
            entry.appendChild(jsonPre);
        }
        
        logDiv.appendChild(entry);
        logDiv.scrollTop = logDiv.scrollHeight;
        
        // Also log to console for dev tools
        console[type === 'error' ? 'error' : type === 'warning' ? 'warn' : 'log'](
            `[${type.toUpperCase()}] ${message}`, data || ''
        );
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function clearLog() {
        document.getElementById("log").innerHTML = '';
        log("Log cleared.", "info");
    }
    
    function updateAISStatus(status, state = 'disconnected') {
        const dot = document.getElementById("aisStatusDot");
        const text = document.getElementById("aisStatusText");
        text.textContent = `AIS: ${status}`;
        dot.className = `status-dot ${state}`;
    }
    
    function updateRIACStatus(status, state = 'disconnected') {
        const dot = document.getElementById("riacStatusDot");
        const text = document.getElementById("riacStatusText");
        text.textContent = `RIAC: ${status}`;
        dot.className = `status-dot ${state}`;
    }
    
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById(`${tabName}-tab`).classList.add('active');
        event.target.classList.add('active');
        if (tabName === 'debug') {
            // Scroll to bottom of log when opening debug tab
            setTimeout(() => {
                const logDiv = document.getElementById("log");
                logDiv.scrollTop = logDiv.scrollHeight;
            }, 100);
        }
    }
    
    // ==================== AIS STREAM FUNCTIONS ====================
    function connectAIS() {
        if (aisSocket && aisSocket.readyState === WebSocket.OPEN) {
            log("Already connected to AIS Stream.", "warning");
            return;
        }
        
        const apiKey = document.getElementById("aisApiKey").value.trim();
        if (!apiKey) {
            log("Please enter an API key.", "error");
            return;
        }
        
        log("🔗 Connecting to AIS Stream WebSocket...", "info");
        updateAISStatus("Connecting...", "connecting");
        
        try {
            aisSocket = new WebSocket("wss://stream.aisstream.io/v0/stream");
        } catch (e) {
            log(`❌ Failed to create WebSocket: ${e.name}: ${e.message}`, "error");
            updateAISStatus("Connection Failed", "error");
            return;
        }
        
        aisSocket.onopen = function () {
            log("✅ WebSocket connection OPEN (readyState: " + aisSocket.readyState + ")", "success");
            updateAISStatus("Connected", "connected");
            
            // Parse bounding box
            const [minLat, minLon] = document.getElementById("bboxMin").value.split(',').map(s => parseFloat(s.trim()));
            const [maxLat, maxLon] = document.getElementById("bboxMax").value.split(',').map(s => parseFloat(s.trim()));
            
            // Parse MMSI filter
            const mmsiFilter = document.getElementById("filterMMSI").value
                .split(',')
                .map(s => s.trim())
                .filter(s => s.length > 0);
            
            const subscriptionMessage = {
                Apikey: apiKey,
                BoundingBoxes: [[[ minLat, minLon ], [ maxLat, maxLon ]]],
                FiltersShipMMSI: mmsiFilter.length > 0 ? mmsiFilter : undefined,
                FilterMessageTypes: ["PositionReport"]
            };
            
            log("📤 Sending subscription message:", "debug", subscriptionMessage);
            
            try {
                aisSocket.send(JSON.stringify(subscriptionMessage));
                log("✅ Subscription message sent successfully.", "success");
                document.getElementById("aisDetails").innerHTML = 
                    `<strong>Connected:</strong> ${new Date().toLocaleTimeString()}<br>` +
                    `<strong>Bounding Box:</strong> [${minLat}, ${minLon}] to [${maxLat}, ${maxLon}]<br>` +
                    `<strong>Filters:</strong> ${mmsiFilter.length > 0 ? mmsiFilter.join(', ') : 'None'}`;
            } catch (e) {
                log(`❌ Failed to send subscription: ${e.message}`, "error");
            }
        };
        
        aisSocket.onmessage = function (event) {
            log("📩 Raw message received (" + event.data.length + " chars)", "debug");
            try {
                const aisMessage = JSON.parse(event.data);
                log("✅ Parsed AIS message:", "success", aisMessage);
                
                // Show summary for PositionReport
                if (aisMessage.MessageType === "PositionReport" && aisMessage.Data) {
                    const d = aisMessage.Data;
                    log(`🚢 Position: MMSI=${d.MMSI}, Lat=${d.Latitude?.toFixed(4)}, Lon=${d.Longitude?.toFixed(4)}, SOG=${d.SOG}`, "info");
                }
            } catch (e) {
                log(`❌ Failed to parse message: ${e.message}`, "error");
                log(`📄 Raw data (first 500 chars): ${event.data.substring(0, 500)}...`, "warning");
            }
        };
        
        aisSocket.onerror = function (error) {
            // WebSocket error event has limited info - log everything we can
            const state = aisSocket ? aisSocket.readyState : 'unknown';
            const stateNames = {0:'CONNECTING',1:'OPEN',2:'CLOSING',3:'CLOSED'};
            
            log(`❌ WebSocket ERROR event fired`, "error");
            log(`   ReadyState: ${state} (${stateNames[state] || 'UNKNOWN'})`, "debug");
            log(`   Event type: ${error.type}`, "debug");
            log(`   Event trusted: ${error.isTrusted}`, "debug");
            log(`   Full event object:`, "debug", error);
            
            // Try to get more info from browser console
            if (window.chrome?.webstore) {
                log("💡 Tip: Check browser DevTools → Console for additional CORS/network errors", "warning");
            }
        };
        
        aisSocket.onclose = function (event) {
            const codeNames = {
                1000: 'Normal Closure',
                1001: 'Going Away',
                1002: 'Protocol Error',
                1003: 'Unsupported Data',
                1006: 'Abnormal Closure (no close frame)',
                1007: 'Invalid Frame Payload Data',
                1008: 'Policy Violation',
                1009: 'Message Too Big',
                1011: 'Internal Error',
                1015: 'TLS Handshake Failure'
            };
            
            log(`🔌 WebSocket CLOSED`, "info");
            log(`   Code: ${event.code} (${codeNames[event.code] || 'Unknown'})`, event.code === 1006 ? "error" : "debug");
            log(`   Reason: ${event.reason || '(no reason provided)'}`, event.code === 1006 ? "error" : "debug");
            log(`   WasClean: ${event.wasClean}`, event.wasClean ? "debug" : "warning");
            log(`   Event object:`, "debug", event);
            
            updateAISStatus(`Disconnected (Code ${event.code})`, event.code === 1000 ? 'disconnected' : 'error');
            document.getElementById("aisDetails").textContent = "Disconnected";
            aisSocket = null;
            
            // Auto-diagnose common 1006 causes
            if (event.code === 1006) {
                log("🔍 Possible causes for Code 1006:", "warning");
                log("   • Invalid/expired API key → Verify at https://aisstream.io/account", "warning");
                log("   • Network/firewall blocking wss:// connections", "warning");
                log("   • Server rejected subscription (check API key permissions)", "warning");
                log("   • Browser extension interfering with WebSocket", "warning");
                log("   • Try testing in Incognito mode or different browser", "warning");
            }
        };
    }
    
    function disconnectAIS() {
        if (aisSocket) {
            log("✋ Manually disconnecting AIS WebSocket...", "info");
            aisSocket.close(1000, "User requested disconnect");
        } else {
            log("No active AIS connection to close.", "warning");
        }
    }
    
    // ==================== RIAC METER API FUNCTIONS ====================
    const RIAC_BASE = "https://portal.meterdata.co.za/data/api";
    const RIAC_CLIENT_ID = "1A03BDE4-2EEA-4140-8295-76FEFD35B382";
    
    async function authenticateRIAC() {
        const username = document.getElementById("riacUsername").value.trim();
        const password = document.getElementById("riacPassword").value;
        const application = document.getElementById("riacApplication").value.trim() || "TestApp";
        
        if (!username || !password) {
            log("Please enter username and password for RIAC authentication.", "error");
            return;
        }
        
        log("🔐 Authenticating with RIAC API...", "info");
        updateRIACStatus("Authenticating...", "connecting");
        
        const params = new URLSearchParams();
        params.append("grant_type", "password");
        params.append("client_id", RIAC_CLIENT_ID);
        params.append("username", username);
        params.append("password", password);
        params.append("application", application);
        
        try {
            const response = await fetch(`${RIAC_BASE}/accounts/token`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: params.toString()
            });
            
            log(`📡 Auth response status: ${response.status} ${response.statusText}`, "debug");
            
            if (!response.ok) {
                const errorText = await response.text();
                log(`❌ Authentication failed (${response.status}): ${errorText}`, "error");
                updateRIACStatus("Auth Failed", "error");
                return;
            }
            
            const tokenData = await response.json();
            log("✅ Authentication successful!", "success");
            log("🎫 Token response:", "debug", tokenData);
            
            riacToken = tokenData.access_token;
            riacTokenExpiry = new Date(Date.now() + (tokenData.expires_in * 1000));
            
            updateRIACStatus(`Authenticated (expires ${riacTokenExpiry.toLocaleTimeString()})`, "connected");
            
            // Show meter sections
            document.getElementById("riacMetersSection").style.display = "block";
            document.getElementById("riacMeterDetailSection").style.display = "block";
            document.getElementById("riacDataSection").style.display = "block";
            
            // Auto-fetch meters
            await getMeters();
            
        } catch (e) {
            log(`❌ Network error during authentication: ${e.name}: ${e.message}`, "error");
            log("💡 Check: CORS, network connectivity, API endpoint URL", "warning");
            updateRIACStatus("Network Error", "error");
        }
    }
    
    function clearRIACAuth() {
        riacToken = null;
        riacTokenExpiry = null;
        updateRIACStatus("Not Authenticated", "disconnected");
        document.getElementById("riacMetersSection").style.display = "none";
        document.getElementById("riacMeterDetailSection").style.display = "none";
        document.getElementById("riacDataSection").style.display = "none";
        document.getElementById("metersList").innerHTML = "";
        document.getElementById("meterDetail").innerHTML = "";
        document.getElementById("sensorDataOutput").innerHTML = "";
        log("🚫 RIAC authentication cleared.", "info");
    }
    
    function getAuthHeader() {
        if (!riacToken) {
            log("❌ Not authenticated. Please authenticate first.", "error");
            return null;
        }
        if (riacTokenExpiry && new Date() > riacTokenExpiry) {
            log("⚠️ Token expired. Please re-authenticate.", "warning");
            return null;
        }
        return `Bearer ${riacToken}`;
    }
    
    async function getMeters() {
        const authHeader = getAuthHeader();
        if (!authHeader) return;
        
        log("📋 Fetching meter list...", "info");
        
        try {
            const response = await fetch(`${RIAC_BASE}/meters`, {
                method: "GET",
                headers: { "Authorization": authHeader }
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                log(`❌ Failed to get meters (${response.status}): ${errorText}`, "error");
                return;
            }
            
            const meters = await response.json();
            log(`✅ Retrieved ${meters.length} meter(s)`, "success");
            log("📊 Meters:", "debug", meters);
            
            const container = document.getElementById("metersList");
            container.innerHTML = "";
            
            if (meters.length === 0) {
                container.innerHTML = "<em>No meters found</em>";
                return;
            }
            
            meters.forEach(meter => {
                const card = document.createElement("div");
                card.className = "meter-card";
                card.innerHTML = `
                    <strong>${escapeHtml(meter.name || 'Unnamed')}</strong> 
                    <small>(ID: ${meter.id})</small><br>
                    <small>📍 ${escapeHtml(meter.location || 'No location')} | 📱 ${escapeHtml(meter.deviceName || 'No device')}</small><br>
                    <small>📡 Sensors: ${meter.sensorCount || 0} | 🗓️ Commissioned: ${meter.commissionDate ? new Date(meter.commissionDate).toLocaleDateString() : 'N/A'}</small>
                `;
                card.onclick = () => {
                    document.getElementById("meterIdInput").value = meter.id;
                    document.getElementById("dataMeterId").value = meter.id;
                    log(`🎯 Selected meter ID: ${meter.id}`, "info");
                };
                container.appendChild(card);
            });
            
        } catch (e) {
            log(`❌ Error fetching meters: ${e.name}: ${e.message}`, "error");
        }
    }
    
    async function getSpecificMeter() {
        const authHeader = getAuthHeader();
        if (!authHeader) return;
        
        const meterId = document.getElementById("meterIdInput").value;
        if (!meterId) {
            log("Please enter a Meter ID.", "error");
            return;
        }
        
        log(`🔍 Fetching details for meter ID: ${meterId}`, "info");
        
        try {
            const response = await fetch(`${RIAC_BASE}/meters/${meterId}`, {
                method: "GET",
                headers: { "Authorization": authHeader }
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                log(`❌ Failed to get meter ${meterId} (${response.status}): ${errorText}`, "error");
                return;
            }
            
            const meter = await response.json();
            log("✅ Meter details:", "success", meter);
            
            document.getElementById("meterDetail").innerHTML = `
                <pre style="background:#111;padding:10px;border-radius:4px;overflow:auto;">${JSON.stringify(meter, null, 2)}</pre>
            `;
            
        } catch (e) {
            log(`❌ Error fetching meter details: ${e.name}: ${e.message}`, "error");
        }
    }
    
    async function getMeterSensorData() {
        const authHeader = getAuthHeader();
        if (!authHeader) return;
        
        const meterId = document.getElementById("dataMeterId").value;
        const periodStart = document.getElementById("periodStart").value;
        const periodEnd = document.getElementById("periodEnd").value;
        const includeProcessed = document.getElementById("includeProcessed").checked;
        
        if (!meterId) {
            log("Please enter a Meter ID.", "error");
            return;
        }
        if (!periodStart || !periodEnd) {
            log("Please select both start and end dates.", "error");
            return;
        }
        
        const url = new URL(`${RIAC_BASE}/meters/${meterId}/data`);
        url.searchParams.append("periodStart", periodStart);
        url.searchParams.append("periodEnd", periodEnd);
        url.searchParams.append("includeProcessedData", includeProcessed.toString());
        
        log(`📈 Fetching sensor data for meter ${meterId} [${periodStart} to ${periodEnd}]`, "info");
        
        try {
            const response = await fetch(url.toString(), {
                method: "GET",
                headers: { "Authorization": authHeader }
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                log(`❌ Failed to get sensor data (${response.status}): ${errorText}`, "error");
                return;
            }
            
            const data = await response.json();
            log("✅ Sensor data retrieved:", "success");
            log("📦 Full response:", "debug", data);
            
            const output = document.getElementById("sensorDataOutput");
            output.innerHTML = "";
            
            // Display sensors
            if (data.sensors?.length > 0) {
                output.innerHTML += `<strong>📡 Sensors (${data.sensors.length}):</strong><br>`;
                data.sensors.forEach(sensor => {
                    output.innerHTML += `• ${escapeHtml(sensor.name)} [${escapeHtml(sensor.sensorType)}] - Column: ${sensor.rawMapping?.valueColumn ?? 'N/A'}<br>`;
                });
                output.innerHTML += "<br>";
            }
            
            // Display sample of raw data
            if (data.rawData?.length > 0) {
                output.innerHTML += `<strong>📄 Raw Data (first 5 records):</strong><br>`;
                data.rawData.slice(0, 5).forEach(record => {
                    output.innerHTML += `⏰ ${record.logTimeStamp}: [${record.values?.join(', ') || 'no values'}]<br>`;
                });
                if (data.rawData.length > 5) {
                    output.innerHTML += `<em>... and ${data.rawData.length - 5} more records</em><br>`;
                }
                output.innerHTML += "<br>";
            }
            
            // Display sample of processed data
            if (includeProcessed && data.processedData?.length > 0) {
                output.innerHTML += `<strong>⚙️ Processed Data (first 5 records):</strong><br>`;
                data.processedData.slice(0, 5).forEach(record => {
                    output.innerHTML += `⏰ ${record.logTimeStamp}: [${record.values?.join(', ') || 'no values'}]<br>`;
                });
                if (data.processedData.length > 5) {
                    output.innerHTML += `<em>... and ${data.processedData.length - 5} more records</em>`;
                }
            }
            
            if (!data.sensors?.length && !data.rawData?.length && !data.processedData?.length) {
                output.innerHTML = "<em>No data returned for this period</em>";
            }
            
        } catch (e) {
            log(`❌ Error fetching sensor data: ${e.name}: ${e.message}`, "error");
            document.getElementById("sensorDataOutput").innerHTML = `<span class="log-error">Error: ${escapeHtml(e.message)}</span>`;
        }
    }
    
    // ==================== INITIALIZATION ====================
    window.addEventListener('DOMContentLoaded', () => {
        log("🚀 Page loaded. Ready to connect.", "info");
        
        // Set default dates for RIAC data fetch (last 24 hours)
        const now = new Date();
        const yesterday = new Date(now.getTime() - 24*60*60*1000);
        
        const toLocalISO = (d) => {
            const offset = d.getTimezoneOffset() * 60000;
            return new Date(d.getTime() - offset).toISOString().slice(0, 16);
        };
        
        document.getElementById("periodStart").value = toLocalISO(yesterday);
        document.getElementById("periodEnd").value = toLocalISO(now);
        
        // Auto-focus first input in each tab
        document.getElementById("ais-tab").querySelector("input")?.focus();
    });
    
    // Handle page visibility change to detect disconnects
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && aisSocket?.readyState === WebSocket.CLOSED) {
            log("👁️ Page became visible - WebSocket is closed", "debug");
        }
    });
</script>

</body>
</html>