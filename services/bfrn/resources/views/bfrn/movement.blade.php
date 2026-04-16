<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>BFRN - Movement API</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 text-gray-100">
<div class="max-w-6xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">BFRN • Movement API Viewer</h1>
        <a href="/bfrn" class="text-sm underline text-gray-300 hover:text-white">Back</a>
    </div>

    <div id="app" class="bg-gray-900/60 border border-gray-800 rounded-xl p-5">
        <div class="flex flex-wrap items-center gap-3 justify-between mb-4">
            <div class="text-sm text-gray-300">
                Endpoint: <span class="font-mono text-gray-100">@{{ endpoint }}</span>
            </div>

            <div class="flex flex-wrap gap-2">
                <button @click="setEndpoint('/bfrn/api/movement')" class="px-3 py-2 rounded bg-gray-800 hover:bg-gray-700 text-sm">Index</button>
                <button @click="setEndpoint('/bfrn/api/movement/movements')" class="px-3 py-2 rounded bg-gray-800 hover:bg-gray-700 text-sm">Movements</button>
                <button @click="setEndpoint('/bfrn/api/movement/movement-items')" class="px-3 py-2 rounded bg-gray-800 hover:bg-gray-700 text-sm">Movement Items</button>
                <button @click="setEndpoint('/bfrn/api/movement/offloadings')" class="px-3 py-2 rounded bg-gray-800 hover:bg-gray-700 text-sm">Offloadings</button>
                <button @click="setEndpoint('/bfrn/api/movement/offloading-items')" class="px-3 py-2 rounded bg-gray-800 hover:bg-gray-700 text-sm">Offloading Items</button>

                <button
                    @click="fetchApi"
                    class="px-4 py-2 rounded bg-white text-black text-sm font-semibold hover:opacity-90 disabled:opacity-50"
                    :disabled="loading"
                >
                    @{{ loading ? 'Loading...' : 'Refresh' }}
                </button>
            </div>
        </div>

        <div v-if="error" class="mb-4 p-3 rounded bg-red-900/40 text-red-200 border border-red-900">
            <div class="font-semibold">Request failed</div>
            <div class="text-sm break-words">@{{ error }}</div>
        </div>

        <pre class="bg-black/40 p-4 rounded-lg overflow-x-auto whitespace-pre-wrap text-sm min-h-[220px]">
@{{ pretty }}
        </pre>
    </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
(() => {
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                endpoint: '/bfrn/api/movement',
                loading: false,
                error: null,
                responseData: null,
            };
        },
        computed: {
            pretty() {
                if (this.loading) return 'Loading...';
                if (this.responseData === null) return 'No data yet.';
                try { return JSON.stringify(this.responseData, null, 2); }
                catch (e) { return String(this.responseData); }
            }
        },
        methods: {
            setEndpoint(ep) {
                this.endpoint = ep;
                this.fetchApi();
            },
            async fetchApi() {
                this.loading = true;
                this.error = null;
                try {
                    const res = await axios.get(this.endpoint, {
                        timeout: 15000,
                        headers: { 'Accept': 'application/json' }
                    });
                    this.responseData = res.data;
                } catch (err) {
                    const msg =
                        err?.response
                            ? `HTTP ${err.response.status}: ${JSON.stringify(err.response.data)}`
                            : (err?.message || 'Unknown error');
                    this.error = msg;
                    this.responseData = null;
                } finally {
                    this.loading = false;
                }
            }
        },
        mounted() {
            this.fetchApi();
        }
    }).mount('#app');
})();
</script>
</body>
</html>
