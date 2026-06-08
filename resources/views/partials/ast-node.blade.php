@php
    $nodeColors = [
        0 => 'text-indigo-300 bg-indigo-500/10 border border-indigo-500/25',
        1 => 'text-blue-300 bg-blue-500/10 border border-blue-500/25',
        2 => 'text-emerald-300 bg-emerald-500/10 border border-emerald-500/25',
        3 => 'text-amber-300 bg-amber-500/10 border border-amber-500/25',
        4 => 'text-purple-300 bg-purple-500/10 border border-purple-500/25',
    ];
    $connectorColors = [
        0 => 'border-indigo-500/20',
        1 => 'border-blue-500/20',
        2 => 'border-emerald-500/20',
        3 => 'border-amber-500/20',
        4 => 'border-purple-500/20',
    ];
    $colorClass     = $nodeColors[min($depth, 4)];
    $connectorClass = $connectorColors[min($depth, 4)];
    $hasChildren    = !empty($node['children']);
    $childCount     = $hasChildren ? count($node['children']) : 0;
@endphp

<div x-data="{ open: true }" class="my-px">

    {{-- Node row --}}
    <div class="flex items-center gap-2 px-2 py-1 rounded-md hover:bg-white/[0.05] transition-colors duration-100 group {{ $hasChildren ? 'cursor-pointer' : '' }}"
         @if($hasChildren) @click="open = !open" @endif>

        {{-- Animated chevron --}}
        @if($hasChildren)
        <svg :class="open ? 'rotate-90' : 'rotate-0'"
             class="w-3 h-3 text-slate-500 group-hover:text-slate-300 shrink-0 transition-transform duration-150"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        @else
        <span class="w-3 h-3 shrink-0"></span>
        @endif

        {{-- Node type badge --}}
        <span class="px-2 py-0.5 rounded text-xs font-mono font-medium shrink-0 {{ $colorClass }}">
            {{ $node['type'] ?? 'Node' }}
        </span>

        {{-- Value --}}
        @if(isset($node['value']) && $node['value'] !== null && $node['value'] !== '')
        <span class="text-slate-600 text-xs mx-0.5">:</span>
        <span class="text-slate-300 text-xs font-mono truncate max-w-xs leading-5">{{ $node['value'] }}</span>
        @endif

        {{-- Attributes (e.g. start=07/2019 end=2019) --}}
        @if(!empty($node['attributes']))
        @foreach($node['attributes'] as $key => $val)
        <span class="text-xs font-mono shrink-0">
            <span class="text-slate-500">{{ $key }}</span><span class="text-slate-600">=</span><span class="text-amber-400/80">{{ is_array($val) ? implode(',', $val) : $val }}</span>
        </span>
        @endforeach
        @endif

        {{-- Child count hint (visible on hover) --}}
        @if($hasChildren)
        <span class="ml-auto text-xs text-slate-700 font-mono opacity-0 group-hover:opacity-100 transition-opacity duration-150 shrink-0 pr-1">
            {{ $childCount }}
        </span>
        @endif
    </div>

    {{-- Children with connector line --}}
    @if($hasChildren)
    <div x-show="open" x-cloak
         x-transition:enter="transition-opacity duration-150 ease-out"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-100 ease-in"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="ml-5 pl-3 border-l {{ $connectorClass }}">
        @foreach($node['children'] as $child)
            @include('partials.ast-node', ['node' => $child, 'depth' => $depth + 1])
        @endforeach
    </div>
    @endif

</div>
