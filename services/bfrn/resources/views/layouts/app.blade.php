<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/vue@2.7.16/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- <style>
        .bg-overlay {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                              url('{{ asset("assets/truck-background.jpg") }}');
        }
    </style> -->
</head>
<body class="min-h-screen bg-overlay bg-cover bg-center bg-no-repeat bg-fixed">
    
    <div id="app">
        <nav class="bg-blue-600/90 backdrop-blur-sm p-4 text-white shadow-lg sticky top-0 z-50">
            <div class="container mx-auto flex justify-between items-center">
                <div class="font-bold tracking-wider text-xl">BFRN SHIPMENT</div>
                <div class="text-sm opacity-80 uppercase tracking-widest">Logistics Management</div>
            </div>
        </nav>

        <main>
            <div class="flex items-center justify-center min-h-[calc(100vh-64px)] p-4">
                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>