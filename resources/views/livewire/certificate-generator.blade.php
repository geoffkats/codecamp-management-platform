<div class="container mx-auto max-w-4xl py-8 px-4">
    <div class="bg-white rounded-lg shadow-lg p-8">
        
        <h1 class="text-3xl font-bold mb-8">Certificate Generator</h1>

        {{-- Mode Toggle --}}
        <div class="flex gap-4 mb-8 border-b pb-4">
            <button 
                wire:click="$set('bulkMode', false)"
                class="px-6 py-2 rounded-lg font-semibold transition {{ !$bulkMode ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}"
            >
                Single Certificate
            </button>
            <button 
                wire:click="$set('bulkMode', true)"
                class="px-6 py-2 rounded-lg font-semibold transition {{ $bulkMode ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}"
            >
                Bulk (CSV)
            </button>
        </div>

        {{-- Single Mode --}}
        @if (!$bulkMode)
            <div class="space-y-6">
                
                {{-- Candidate Info --}}
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Candidate Name</label>
                        <input 
                            type="text" 
                            wire:model="candidateName"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., Jane Nakato"
                        />
                        @error('candidateName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Candidate Number</label>
                        <input 
                            type="text" 
                            wire:model="candidateNo"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., CAU-2024-001"
                        />
                        @error('candidateNo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Signature Date</label>
                    <input 
                        type="date" 
                        wire:model="signatureDate"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    @error('signatureDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Modules --}}
                <div class="border-t pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <label class="block text-sm font-semibold">Modules Completed</label>
                        <button 
                            type="button"
                            wire:click="addModule"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                        >
                            + Add Module
                        </button>
                    </div>

                    <div class="space-y-4">
                        @foreach ($modules as $index => $module)
                            <div class="flex gap-4 bg-gray-50 p-4 rounded-lg">
                                <div class="flex-1">
                                    <input 
                                        type="text" 
                                        wire:model.live="modules.{{ $index }}.name"
                                        placeholder="Module name"
                                        class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div class="w-32">
                                    <input 
                                        type="text" 
                                        wire:model.live="modules.{{ $index }}.version"
                                        placeholder="Version"
                                        class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div class="w-32">
                                    <input 
                                        type="date" 
                                        wire:model.live="modules.{{ $index }}.date"
                                        class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <button 
                                    type="button"
                                    wire:click="removeModule({{ $index }})"
                                    class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm"
                                >
                                    Remove
                                </button>
                            </div>
                        @endforeach
                    </div>
                    @error('modules') <span class="text-red-500 text-sm block mt-2">{{ $message }}</span> @enderror
                </div>

                {{-- Preview & Download --}}
                <div class="flex gap-4 pt-6 border-t">
                    <button 
                        type="button"
                        wire:click="preview"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
                    >
                        Preview
                    </button>
                    <button 
                        type="button"
                        wire:click="generate"
                        wire:loading.attr="disabled"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold disabled:opacity-50"
                    >
                        <span wire:loading.remove>Download PDF</span>
                        <span wire:loading>Generating...</span>
                    </button>
                </div>
            </div>
        @endif

        {{-- Bulk Mode --}}
        @if ($bulkMode)
            <div class="space-y-6">
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Signature Date (for all)</label>
                    <input 
                        type="date" 
                        wire:model="signatureDate"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    @error('signatureDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                    <label class="cursor-pointer">
                        <span class="text-lg font-semibold text-gray-700">Upload CSV File</span>
                        <p class="text-sm text-gray-500 mt-2">Or drag and drop</p>
                        <input 
                            type="file" 
                            wire:model="csvFile"
                            accept=".csv"
                            class="hidden"
                        />
                    </label>
                    @if ($csvFile)
                        <p class="text-sm text-green-600 mt-3">✓ {{ $csvFile->getClientOriginalName() }}</p>
                    @endif
                    @error('csvFile') <span class="text-red-500 text-sm block mt-2">{{ $message }}</span> @enderror
                </div>

                @if ($bulkError)
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                        {{ $bulkError }}
                    </div>
                @endif

                @if (count($bulkCandidates) > 0)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm font-semibold mb-3">{{ count($bulkCandidates) }} candidate(s) loaded</p>
                        <div class="max-h-64 overflow-y-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2 px-2">Name</th>
                                        <th class="text-left py-2 px-2">Number</th>
                                        <th class="text-left py-2 px-2">Modules</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bulkCandidates as $candidate)
                                        <tr class="border-b text-xs">
                                            <td class="py-2 px-2">{{ $candidate['candidateName'] }}</td>
                                            <td class="py-2 px-2">{{ $candidate['candidateNo'] }}</td>
                                            <td class="py-2 px-2">{{ count($candidate['modules']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <button 
                        type="button"
                        wire:click="generateBulk"
                        wire:loading.attr="disabled"
                        class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold disabled:opacity-50"
                    >
                        <span wire:loading.remove>Generate & Download ZIP</span>
                        <span wire:loading>Generating certificates...</span>
                    </button>
                @endif

                <a 
                    href="{{ route('certificate.csv-sample') }}"
                    class="text-blue-600 hover:underline text-sm"
                    download
                >
                    📥 Download Sample CSV
                </a>
            </div>
        @endif

    </div>
</div>