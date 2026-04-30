<x-filament-panels::page>
    {{-- Widgets --}}
    @foreach($this->getWidgets() as $widget)
        @livewire($widget)
    @endforeach
</x-filament-panels::page>
