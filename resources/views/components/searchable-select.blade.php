@props(['name' => null, 'label' => null, 'options' => [], 'selected' => '', 'required' => false, 'valueKey' => 'id', 'labelKey' => 'name', 'searchKeys' => ['name']])

<div class="space-y-2" x-data="{
    open: false,
    search: '',
    selected: null,
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
        this.$watch('selected', (val) => {
            this.updateLabel(val);
        });
        this.updateLabel(this.selected);
    },
    updateLabel(val) {
        if (val) {
            const option = this.options.find(o => o['{{ $valueKey }}'] == val);
            if (option) {
                this.selectedLabel = option['{{ $labelKey }}'];
            }
        } else {
            this.selectedLabel = '';
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
}" x-init="selected = items[idx].item_classification_id; updateLabel(selected)" @click.away="open = false">
    @if($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
    </label>
    @endif

    <div class="relative">
        <!-- Hidden input for form submission -->
        @if($name)
        <input type="hidden" name="{{ $name }}" :value="selected" {{ $required ? 'required' : '' }}>
        @endif

        <!-- Display button -->
        <button type="button"
                @click="open = !open; if(open) scrollToSelected()"
                class="mt-2 w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors duration-200 bg-white text-left flex items-center justify-between @if($name) @error($name) border-red-300 @enderror @endif">
            <span x-text="selectedLabel || 'Select {{ $label ?: 'option' }}'"
                  :class="!selectedLabel && 'text-gray-400'"></span>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <!-- Dropdown -->
        <div x-show="open"
              x-transition
              class="absolute z-50 w-full mt-2 bg-white border border-gray-300 rounded-xl shadow-lg max-h-80">

            <!-- Search input -->
            <div class="p-3 border-b border-gray-200">
                <input type="text"
                       x-model="search"
                       placeholder="Search..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"
                       @click.stop>
            </div>

            <!-- Options list -->
            <div class="overflow-y-auto max-h-64 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                <template x-for="option in filteredOptions" :key="option['{{ $valueKey }}']">
                    <button type="button"
                            @click="selected = option['{{ $valueKey }}']; selectedLabel = option['{{ $labelKey }}']; open = false; search = ''; $dispatch('input', selected)"
                            class="w-full px-4 py-2.5 text-left hover:bg-primary-50 transition-colors duration-150 text-sm"
                            :class="selected == option['{{ $valueKey }}'] && 'bg-primary-100 font-medium'"
                            x-text="option['{{ $labelKey }}']">
                    </button>
                </template>

                <!-- No results -->
                <div x-show="filteredOptions.length === 0" class="px-4 py-3 text-sm text-gray-500 text-center">
                    No results found
                </div>
            </div>
        </div>
    </div>

    @if($name)
    @error($name)
        <p class="mt-1 text-sm text-red-600 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            {{ $message }}
        </p>
    @enderror
    @endif
</div>
