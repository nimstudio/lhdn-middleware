@props(['name', 'options' => [], 'selected' => '', 'required' => false, 'valueKey' => 'id', 'labelKey' => 'name', 'searchKeys' => ['name'], 'class' => '', 'model' => null])

<div class="relative {{ $class }}" x-data="{
    open: false,
    search: '',
    selected: '{{ $selected }}',
    selectedLabel: '',
    options: {{ json_encode($options) }},
    get filteredOptions() {
        if (this.search === '') return this.options;
        return this.options.filter(option => {
            const searchLower = this.search.toLowerCase();
            @foreach($searchKeys as $key)
            if (option['{{ $key }}'] && option['{{ $key }}'].toLowerCase().includes(searchLower)) return true;
            @endforeach
            return false;
        });
    },
    init() {
        if (this.selected) {
            const option = this.options.find(o => o['{{ $valueKey }}'] == this.selected);
            if (option) {
                this.selectedLabel = option['{{ $labelKey }}'];
            }
        }
    },
    scrollToSelected() {
        this.$nextTick(() => {
            const selectedElement = this.$el.querySelector('.bg-primary-100');
            if (selectedElement) {
                selectedElement.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        });
    }
}" @click.away="open = false">

    <!-- Display button -->
    <button type="button"
            @click="open = !open; if(open) scrollToSelected()"
            class="min-w-[140px] !px-2 !py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white text-left flex items-center justify-between w-full">
        <span x-text="selectedLabel || 'Select Classification'"
              :class="!selectedLabel && 'text-gray-400'"
              class="truncate"></span>
        <svg class="w-3 h-3 text-gray-400 flex-shrink-0 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown -->
    <div x-show="open"
         x-transition
         class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60"
         style="min-width: 200px;">

        <!-- Search input -->
        <div class="p-2 border-b border-gray-200">
            <input type="text"
                   x-model="search"
                   placeholder="Search..."
                   class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                   @click.stop>
        </div>

        <!-- Options list -->
        <div class="overflow-y-auto max-h-48 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
            <template x-for="option in filteredOptions" :key="option['{{ $valueKey }}']">
                <button type="button"
                        @click="selected = option['{{ $valueKey }}']; selectedLabel = option['{{ $labelKey }}']; open = false; search = ''; @if($model) {{ $model }} = option['{{ $valueKey }}'] @endif"
                        class="w-full px-3 py-1.5 text-left hover:bg-primary-50 transition-colors duration-150 text-xs"
                        :class="selected == option['{{ $valueKey }}'] && 'bg-primary-100 font-medium'"
                        x-text="option['{{ $labelKey }}']">
                </button>
            </template>

            <!-- No results -->
            <div x-show="filteredOptions.length === 0" class="px-3 py-2 text-xs text-gray-500 text-center">
                No results found
            </div>
        </div>
    </div>
</div>

