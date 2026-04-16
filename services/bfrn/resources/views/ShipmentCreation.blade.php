@extends('layouts.app')

@section('content')
<div class="w-full max-w-4xl mx-auto p-4" v-cloak>
    
    <div v-if="step === 1" class="bg-white/95 backdrop-blur-sm shadow-2xl rounded-3xl p-8 border border-white/20">
        
        <div class="relative flex justify-between items-center mb-12 px-10">
            <div v-for="n in 3" :key="n" class="relative z-10">
                <div :class="[
                    'w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 transition-all duration-500',
                    step > n ? 'bg-green-500 border-green-500 text-white' : 
                    (step === n ? 'bg-blue-600 border-blue-600 text-white shadow-lg' : 'bg-gray-200 border-gray-300 text-gray-500')
                ]">
                    @{{ n }}
                </div>
            </div>
            <div class="absolute h-1 bg-gray-200 top-5 left-[15%] right-[15%] z-0 rounded-full">
                <div class="h-full bg-green-500 transition-all duration-700" :style="{ width: ((step - 1) / 2 * 100) + '%' }"></div>
            </div>
        </div>

        <h1 class="text-3xl font-black text-center text-gray-900 mb-8 uppercase tracking-tight">Create Shipment</h1>

        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800 border-b-4 border-blue-600 inline-block">Shipment Instruction</h2>
        </div>

        <form @submit.prevent="step++" class="space-y-6">
            <div class="border-b-2 border-gray-200 py-1">
                <label class="text-[10px] uppercase font-bold text-gray-400">Instruction type</label>
                <select v-model="form.instruction_type_id" class="w-full bg-transparent outline-none text-lg text-gray-800 cursor-pointer">
                    <option value="">Select Instruction Type</option>
                    <option v-for="item in shipmentData.results" :key="item.id" :value="item.id">
                        @{{ item.name || 'ID ' + item.id }}
                    </option>
                </select>
            </div>

            <input type="text" v-model="form.instruction_reference" placeholder="Ref" 
                   class="w-full py-3 border-b-2 border-gray-200 focus:border-blue-600 outline-none bg-transparent text-lg">
            
            <textarea v-model="form.instruction_detail" placeholder="Details" rows="1"
                      class="w-full py-3 border-b-2 border-gray-200 focus:border-blue-600 outline-none bg-transparent text-lg resize-none"></textarea>

            <div class="div class=grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex-1 p-6 bg-gray-50/50 border-r border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 uppercase text-xs tracking-widest">From</h3>
                    <div class="space-y-1 text-sm text-gray-500 font-medium">
                        <p>Line 1: @{{ addressFrom.line1 || '---' }}</p> 
                        <p>Line 2: @{{ addressFrom.line2 || '---' }}</p>
                        <p>Suburb: @{{ addressFrom.suburb || '---' }}</p>
                        <p>City: @{{ addressFrom.city || '---' }}</p>
                        <p>Province: @{{ addressFrom.province || '---' }}</p>
                        <p>Zip: @{{ addressFrom.zip || '---' }}</p>
                    </div>
                </div>

                <div class="div class=grid grid-cols-1 md:grid-cols-2 gap-6">
                    <h3 class="font-bold text-gray-900 mb-4 uppercase text-xs tracking-widest">To</h3>
                    <div class="space-y-1 text-sm text-gray-500 font-medium">
                        <p>Line 1: @{{ addressFrom.line1 || '---' }}</p>
                        <p>Line 2: @{{ addressFrom.line2 || '---' }}</p>
                        <p>Suburb: @{{ addressFrom.suburb || '---' }}</p>
                        <p>City: @{{ addressFrom.city || '---' }}</p>
                        <p>Province: @{{ addressFrom.province || '---' }}</p>
                        <p>Zip: @{{ addressFrom.zip || '---' }}</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-8">
                <button type="submit" class="bg-gray-900 text-white px-16 py-4 rounded-xl font-black text-xl hover:bg-black transition-all shadow-lg active:scale-95">
                    NEXT
                </button>
            </div>
        </form>
    </div>

    <div v-else-if="step === 2" class="bg-white/95 backdrop-blur-sm shadow-2xl rounded-3xl p-8 border border-white/20 animate-in fade-in slide-in-from-right-4 duration-500">
    
    <div class="relative flex justify-between items-center mb-12 px-10">
        <div v-for="n in 3" :key="n" class="relative z-10 text-center">
            <div :class="['w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 transition-all duration-300', 
                step > n ? 'bg-green-500 border-green-500 text-white' : (step === n ? 'bg-blue-600 border-blue-600 text-white shadow-lg' : 'bg-gray-200 border-gray-300 text-gray-500')]">
                @{{ n }}
            </div>
        </div>
        <div class="absolute h-1 bg-gray-200 top-5 left-[15%] right-[15%] z-0 rounded-full">
            <div class="h-full bg-green-500 transition-all duration-700" :style="{ width: ((step - 1) / 2 * 100) + '%' }"></div>
        </div>
    </div>

    <h1 class="text-3xl font-black text-center text-gray-900 mb-8 uppercase tracking-tight">Create Shipment</h1>

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800 border-b-4 border-blue-600 inline-block">Shipment</h2>
       
    </div>

    <form @submit.prevent="step++" class="space-y-6">
        <div class="border-b-2 border-gray-200 py-1 group focus-within:border-blue-600 transition-all">
            <label class="text-[10px] uppercase font-bold text-gray-400">Shipment - Type</label>
            <select v-model="form.shipment_type" class="w-full bg-transparent outline-none text-lg text-gray-800 cursor-pointer">
                <option value="">Select Shipment Type</option>
                <option value="1">Standard</option>
                <option value="2">Express</option>
            </select>
        </div>

        <div class="border-b-2 border-gray-200 py-1 group focus-within:border-blue-600 transition-all">
            <label class="text-[10px] uppercase font-bold text-gray-400">Mode of Transport</label>
            <select v-model="form.mode_of_transport" class="w-full bg-transparent outline-none text-lg text-gray-800 cursor-pointer">
                <option value="road">Road</option>
                <option value="air">Air</option>
                <option value="sea">Sea</option>
                <option value="rail">Rail</option>
            </select>
        </div>

        <div class="border-b-2 border-gray-200 py-1 group focus-within:border-blue-600 transition-all">
            <label class="text-[10px] uppercase font-bold text-gray-400">Name</label>
            <input type="text" v-model="form.name" placeholder="Enter Shipment Name" 
                   class="w-full bg-transparent outline-none text-lg text-gray-800 py-1">
        </div>

        <div class="border-b-2 border-gray-200 py-1 group focus-within:border-blue-600 transition-all">
            <label class="text-[10px] uppercase font-bold text-gray-400">Description</label>
            <textarea v-model="form.description" placeholder="Enter Description" rows="2" 
                      class="w-full bg-transparent outline-none text-lg text-gray-800 py-1 resize-none"></textarea>
        </div>

        <div class="flex justify-between items-center pt-8">
            <button type="button" @click="step--" class="text-blue-600 font-bold hover:underline">← Go Back</button>
            <button type="submit" class="bg-gray-900 text-white px-16 py-4 rounded-xl font-black text-xl hover:bg-black transition-all shadow-lg active:scale-95">
                SUBMIT
            </button>
        </div>
    </form>
</div>

<!-- <div v-else class="py-20 text-center bg-white/90 rounded-3xl shadow-xl">
    <h2 class="text-4xl font-black text-gray-300">Final Confirmation</h2>
    <button @click="step--" class="mt-4 text-blue-600 font-bold underline">Go Back</button>
</div> -->
</div>

<script>
    window.addEventListener('load', function() {
        new Vue({
            el: '#app', // Targets the wrapper in layouts.app
            data: {
                step: 1,
                shipmentData: @json($shipmentData),
                form: {
                    instruction_type_id: '',
                    instruction_reference: '',
                    instruction_detail: '',
                },
                addressFrom: { line1: '', line2: '', suburb: '', city: '', province: '', zip: '' }
            }
        });
    });
</script>

<style>
    [v-cloak] { display: none !important; }
</style>
@endsection