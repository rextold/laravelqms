<div class="flex items-center gap-4 mb-6">
    @if(isset($organization) && $organization->logo)
        <div class="w-14 h-14 bg-white rounded-xl shadow-md p-2 flex items-center justify-center">
            <img src="{{ asset('storage/' . $organization->logo) }}" alt="Organization Logo" class="h-full w-auto object-contain">
        </div>
    @else
        <div class="w-14 h-14 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl shadow-md flex items-center justify-center">
            <i class="fas fa-building text-white text-2xl"></i>
        </div>
    @endif
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900" data-org-name>{{ $organization->organization_name ?? 'QMS' }}</h1>
        <p class="text-sm md:text-base text-gray-600 flex items-center gap-2">
            <i class="fas fa-store-alt"></i>
            Counter #{{ $counter->counter_number ?? '-' }} - {{ $counter->display_name ?? '' }}
        </p>
    </div>
</div>