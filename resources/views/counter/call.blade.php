@extends('layouts.app')

@section('title', 'Service Station')

@section('content')
<div class="min-h-screen flex flex-col p-4 bg-gray-50 overflow-hidden">
    <!-- Header with Logo and Organization Name -->
    <div id="panelHeader" class="w-full mb-4">
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    @if($settings->logo_url)
                        <img src="{{ $settings->logo_url }}" alt="Organization Logo" class="h-12 w-auto">
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $organization->organization_name ?? 'QMS' }}</h1>
                        <p class="text-sm text-gray-600">Counter {{ $counter->counter_number }} - {{ $counter->display_name }}</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="text-right">
                        <div class="text-lg font-semibold text-gray-700" id="headerTime"></div>
                        <div class="text-xs text-gray-500" id="headerDate"></div>
                    </div>
                    <button id="btnToggleMinimize" type="button" onclick="toggleMinimize()" class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition" title="Minimize">
                        <i class="fas fa-window-minimize"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div id="panelMain" class="flex-1 flex items-center justify-center" data-routes="{{ json_encode([
        'data' => route('counter.data', ['organization_code' => request()->route('organization_code')]),
        'notify' => route('counter.notify', ['organization_code' => request()->route('organization_code')]),
        'skip' => route('counter.skip', ['organization_code' => request()->route('organization_code')]),
        'complete' => route('counter.move-next', ['organization_code' => request()->route('organization_code')]),
        'callNext' => route('counter.call-next', ['organization_code' => request()->route('organization_code')]),
        'recall' => route('counter.recall', ['organization_code' => request()->route('organization_code')]),
        'transfer' => route('counter.transfer', ['organization_code' => request()->route('organization_code')]),
     ]) }}">
        <div class="w-full max-w-3xl">
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-xl p-6 border-2 border-white">
            <div class="text-center">
                <p class="text-white text-sm mb-2">NOW SERVING</p>
                <div id="currentNumber" class="responsive-queue-number font-extrabold text-white drop-shadow-2xl">---</div>
                <form onsubmit="return false;" autocomplete="off">
                    @csrf
                <div class="grid grid-cols-5 gap-2 mt-6 max-w-xl mx-auto">
                    <button type="button" id="btnNotify" onclick="return notifyCustomer(this, event);" class="bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
                        <i class="fas fa-bell mr-2"></i>Notify
                    </button>
                    <button type="button" id="btnSkip" onclick="skipCurrent()" class="bg-orange-500 hover:bg-orange-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
                        <i class="fas fa-forward mr-2"></i>Skip
                    </button>
                    <button type="button" id="btnComplete" onclick="moveToNext(this)" class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
                        <i class="fas fa-check-circle mr-2"></i>Complete
                    </button>
                    <button type="button" id="btnTransfer" onclick="openTransferModal()" class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
                        <i class="fas fa-exchange-alt mr-2"></i>Transfer
                    </button>
                    <button type="button" id="btnCallNext" onclick="callNext(this)" class="bg-white text-indigo-700 hover:bg-gray-100 disabled:bg-gray-400 disabled:text-gray-600 disabled:cursor-not-allowed px-3 py-2 rounded-lg font-semibold text-sm transition" disabled>
                        <i class="fas fa-bell mr-2"></i>Call Next
                    </button>
                </div>
                </form>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4 mt-4">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-base font-bold mb-3">Waiting</h3>
                <div id="waitingList" class="space-y-2 max-h-40 overflow-hidden"></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-base font-bold mb-3">Skipped</h3>
                <div id="skippedList" class="space-y-2 max-h-40 overflow-hidden"></div>
            </div>
        </div>
    </div>
</div>
</div>

    <!-- Minimized dock bar (for easy corner docking) -->
    <div id="panelDock" class="hidden fixed bottom-4 right-4 bg-white rounded-lg shadow-md px-4 py-3 flex items-center space-x-4 z-40">
        <div class="text-sm font-semibold text-gray-700">Counter {{ $counter->counter_number }}</div>
        <div class="flex items-baseline space-x-2">
            <div class="text-xs text-gray-500">Now</div>
            <div id="dockCurrentNumber" class="text-xl font-extrabold text-gray-900">---</div>
        </div>
        <button type="button" onclick="toggleMinimize(false)" class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition" title="Restore">
            <i class="fas fa-window-restore"></i>
        </button>
    </div>

<style>
.responsive-queue-number { font-size: 6rem; }
@media (orientation: portrait) { .responsive-queue-number { font-size: 4.5rem; } }
@media (max-width: 768px) and (orientation: portrait) { .responsive-queue-number { font-size: 3rem; } }
@media (max-width: 768px) and (orientation: landscape) { .responsive-queue-number { font-size: 3.5rem; } }
</style>

<!-- Transfer Queue Modal -->
<div id="transfer-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300 opacity-0">
    <div id="transfer-modal-content" class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-exchange-alt text-white text-xl"></i>
                    <h3 class="text-xl font-bold text-white">Transfer Queue</h3>
                </div>
                <button type="button" onclick="closeTransferModal()" class="text-white hover:text-gray-200 text-2xl leading-none">&times;</button>
            </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-4">Select a counter to transfer the current queue:</p>
            <div id="countersList" class="space-y-2 max-h-64 overflow-y-auto">
                <!-- Populated dynamically -->
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Note:</strong> The queue number will remain the same (first come first serve).
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-gray-200">
            <button type="button" onclick="closeTransferModal()" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">Cancel</button>
        </div>
    </div>
</div>

<!-- Skip Confirmation Modal -->
<div id="skip-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300 opacity-0">
    <div id="skip-modal-content" class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-forward text-white text-xl"></i>
                    <h3 class="text-xl font-bold text-white">Skip Current Queue?</h3>
                </div>
                <button type="button" onclick="closeSkipModal()" class="text-white hover:text-gray-200 text-2xl leading-none">&times;</button>
            </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-4">Are you sure you want to skip the current customer queue?</p>
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                <p class="text-sm text-orange-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Note:</strong> The skipped queue will be moved to the skipped list and can be recalled later.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3 border-t border-gray-200">
            <button type="button" onclick="closeSkipModal()" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition">Cancel</button>
            <button type="button" onclick="confirmSkip(this)" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold transition">
                <i class="fas fa-forward mr-2"></i>Skip Queue
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/counter-panel.js') }}" defer></script>
@endpush