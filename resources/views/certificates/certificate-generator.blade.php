<div class="min-h-screen bg-gray-100 py-10 px-4">
    <div class="max-w-3xl mx-auto">

        {{-- Header --}}
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-blue-800">CODE Profile Certificate Generator</h1>
            <p class="text-gray-500 mt-1">Code Academy Uganda</p>
        </div>

        {{-- Mode toggle --}}
        <div class="flex gap-3 mb-6">
            <button
                wire:click="$set('bulkMode', false)"
                class="px-5 py-2 rounded-lg font-semibold text-sm transition
                       {{ !$bulkMode ? 'bg-blue-700 text-white shadow' : 'bg-white text-blue-700 border border-blue-300 hover:bg-blue-50' }}">
                Single Certificate
            </button>
            <button
                wire:click="$set('bulkMode', true)"
                class="px-5 py-2 rounded-lg font-semibold text-sm transition
                       {{ $bulkMode ? 'bg-blue-700 text-white shadow' : 'bg-white text-blue-700 border border-blue-300 hover:bg-blue-50' }}">
                Bulk (CSV)
            </button>
        </div>

        {{-- ── SINGLE MODE ── --}}
        @if (!$bulkMode)
            <div class="bg-white rounded-2xl shadow p-8 space-y-6">

                {{-- Candidate info --}}
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Candidate Name *</label>
                        <input
                            type="text"
                            wire:model.live="candidateName"
                            placeholder="e.g. John Doe"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        @error('candidateName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Candidate No. *</label>
                        <input
                            type="text"
                            wire:model.live="candidateNo"
                            placeholder="e.g. CAU-2024-001"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        @error('candidateNo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Issue / Signature Date *</label>
                    <input
                        type="date"
                        wire:model.live="signatureDate"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    @error('signatureDate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Modules --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-bold text-gray-700">Modules Completed</h2>
                        <button
                            wire:click="addModule"
                            class="text-sm text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1">
                            <span class="text-lg leading-none">+</span> Add Module
                        </button>
                    </div>

                    <div class="space-y-3">
                        @foreach ($modules as $i => $module)
                            <div class="grid grid-cols-12 gap-2 items-start">
                                <div class="col-span-5">
                                    <input
                                        type="text"
                                        wire:model.live="modules.{{ $i }}.name"
                                        placeholder="Module Name"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                    @error("modules.$i.name") <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="col-span-4">
                                    <input
                                        type="text"
                                        wire:model.live="modules.{{ $i }}.version"
                                        placeholder="Version/Syllabus"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                    @error("modules.$i.version") <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="col-span-2">
                                    <input
                                        type="date"
                                        wire:model.live="modules.{{ $i }}.date"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                    @error("modules.$i.date") <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="col-span-1 flex justify-center pt-2">
                                    @if (count($modules) > 1)
                                        <button
                                            wire:click="removeModule({{ $i }})"
                                            class="text-red-400 hover:text-red-600 text-lg leading-none font-bold">
                                            &times;
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Preview area --}}
                @if ($previewing)
                    <div class="border border-blue-200 rounded-xl bg-blue-50 p-5">
                        <h3 class="font-bold text-blue-800 mb-3">Certificate Preview</h3>
                        <div class="text-sm text-gray-700 space-y-1">
                            <p><span class="font-semibold">Candidate:</span> {{ $candidateName }}</p>
                            <p><span class="font-semibold">Candidate No.:</span> {{ $candidateNo }}</p>
                            <p><span class="font-semibold">Issue Date:</span> {{ \Carbon\Carbon::parse($signatureDate)->format('d M Y') }}</p>
                            <div class="mt-3">
                                <p class="font-semibold mb-1">Modules:</p>
                                <table class="w-full text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-blue-100">
                                            <th class="text-left px-2 py-1 border border-blue-200">Module</th>
                                            <th class="text-left px-2 py-1 border border-blue-200">Version/Syllabus</th>
                                            <th class="text-left px-2 py-1 border border-blue-200">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($modules as $m)
                                            <tr>
                                                <td class="px-2 py-1 border border-blue-200">{{ $m['name'] }}</td>
                                                <td class="px-2 py-1 border border-blue-200">{{ $m['version'] }}</td>
                                                <td class="px-2 py-1 border border-blue-200">{{ \Carbon\Carbon::parse($m['date'])->format('d M Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <button wire:click="cancelPreview" class="mt-3 text-sm text-gray-500 hover:text-gray-700 underline">
                            Edit details
                        </button>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex gap-3 pt-2">
                    @if (!$previewing)
                        <button
                            wire:click="preview"
                            class="px-6 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm transition">
                            Preview
                        </button>
                    @endif
                    <button
                        wire:click="generate"
                        wire:loading.attr="disabled"
                        class="px-7 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-semibold text-sm transition flex items-center gap-2 disabled:opacity-60">
                        <span wire:loading.remove wire:target="generate">⬇ Download Certificate</span>
                        <span wire:loading wire:target="generate">Generating…</span>
                    </button>
                </div>

            </div>
        @endif

        {{-- ── BULK MODE ── --}}
        @if ($bulkMode)
            <div class="bg-white rounded-2xl shadow p-8 space-y-6">

                <div>
                    <h2 class="font-bold text-gray-700 mb-1">Upload CSV</h2>
                    <p class="text-xs text-gray-500 mb-3">
                        Required columns:
                        <code class="bg-gray-100 px-1 rounded">candidate_name</code>,
                        <code class="bg-gray-100 px-1 rounded">candidate_no</code>,
                        <code class="bg-gray-100 px-1 rounded">module_name</code>,
                        <code class="bg-gray-100 px-1 rounded">module_version</code>,
                        <code class="bg-gray-100 px-1 rounded">module_date</code>,
                        <code class="bg-gray-100 px-1 rounded">signature_date</code>.
                        Add one row per module; candidates with the same
                        <code class="bg-gray-100 px-1 rounded">candidate_no</code> are grouped.
                    </p>
                    <input
                        type="file"
                        wire:model="csvFile"
                        accept=".csv"
                        class="text-sm text-gray-600"
                    />
                    @if ($bulkError)
                        <p class="text-red-500 text-sm mt-2">{{ $bulkError }}</p>
                    @endif
                </div>

                @if (!empty($bulkCandidates))
                    <div class="border border-green-200 bg-green-50 rounded-xl p-4">
                        <p class="text-green-700 font-semibold text-sm">
                            ✓ {{ count($bulkCandidates) }} candidate(s) loaded and ready.
                        </p>
                        <ul class="mt-2 text-xs text-green-600 space-y-0.5 max-h-32 overflow-y-auto">
                            @foreach ($bulkCandidates as $c)
                                <li>{{ $c['candidateName'] }} ({{ $c['candidateNo'] }}) — {{ count($c['modules']) }} module(s)</li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Issue / Signature Date *</label>
                        <input
                            type="date"
                            wire:model.live="signatureDate"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        @error('signatureDate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button
                        wire:click="generateBulk"
                        wire:loading.attr="disabled"
                        class="px-7 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-semibold text-sm transition flex items-center gap-2 disabled:opacity-60">
                        <span wire:loading.remove wire:target="generateBulk">⬇ Download All as ZIP</span>
                        <span wire:loading wire:target="generateBulk">Generating ZIP…</span>
                    </button>
                @endif

            </div>
        @endif

        {{-- Sample CSV download hint --}}
        <div class="mt-6 text-center text-xs text-gray-400">
            Need a CSV template?
            <a href="{{ route('certificate.csv-sample') }}" class="text-blue-500 hover:underline">
                Download sample CSV
            </a>
        </div>

    </div>
</div>