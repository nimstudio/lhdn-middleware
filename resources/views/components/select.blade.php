@props(['name', 'label', 'options' => [], 'selected' => '', 'required' => false, 'class' => ''])

<div class="space-y-2">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
    </label>
    <select name="{{ $name }}"
            id="{{ $name }}"
            class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors duration-200 @error($name) border-red-300 focus:ring-red-500 focus:border-red-500 @enderror {{ $class }}"
            {{ $required ? 'required' : '' }}>
        <option value="">Select {{ ucfirst(str_replace(['_id', '_'], ['', ' '], $name)) }}</option>
        @foreach($options as $option)
            <option value="{{ is_array($option) ? $option['id'] : $option->id }}" {{ $selected == (is_array($option) ? $option['id'] : $option->id) ? 'selected' : '' }}>
                {{ is_array($option) ? $option['label'] : ($option->name ?? $option->label ?? '') }}
            </option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1 text-sm text-red-600 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
