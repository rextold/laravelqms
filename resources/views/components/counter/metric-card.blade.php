<div class="bg-white rounded-xl shadow-md p-6 border-t-4 hover:shadow-lg transition-all border-{{ $color ?? 'gray' }}-500">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">{{ $title ?? '' }}</p>
            <p class="text-4xl font-bold text-gray-900">{{ $value ?? '---' }}</p>
            @if(!empty($tooltip))
                <p class="text-xs text-gray-500 mt-2">{{ $tooltip }}</p>
            @endif
        </div>
        <div class="w-16 h-16 bg-{{ $color ?? 'gray' }}-100 rounded-full flex items-center justify-center">
            <i class="fas fa-{{ $icon ?? 'info-circle' }} text-2xl text-{{ $color ?? 'gray' }}-600"></i>
        </div>
    </div>
</div>
