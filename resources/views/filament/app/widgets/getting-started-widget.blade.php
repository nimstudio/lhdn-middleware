@php
    $steps = $this->getSteps();
    $nextStep = $this->getNextStep();
    $percentage = $this->getCompletionPercentage();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header --}}
        <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem;">
            <div style="flex: 1;">
                <h2 style="font-size: 1.5rem; font-weight: 700; margin: 0;">
                    Welcome to {{ config('app.name') }}! 🚀
                </h2>
                <p style="margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280;">
                    Complete these steps to start submitting invoices to LHDN MyInvois
                </p>
                @if($nextStep)
                    <div style="margin-top: 0.75rem;">
                        <x-filament::badge color="danger">
                            👉 Next: {{ $nextStep['title'] }}
                        </x-filament::badge>
                    </div>
                @endif
            </div>
            <div style="text-align: right; margin-left: 1rem;">
                <div style="font-size: 2.25rem; font-weight: 700; color: #bf4036;">{{ $percentage }}%</div>
                <div style="font-size: 0.875rem; color: #6b7280;">Complete</div>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; font-size: 0.875rem; margin-bottom: 0.5rem;">
                <span style="font-weight: 500;">Setup Progress</span>
                <span style="font-weight: 600; color: #bf4036;">{{ $percentage }}%</span>
            </div>
            <div style="position: relative; height: 1rem; overflow: hidden; border-radius: 9999px; background-color: #e5e7eb;">
                <div style="height: 100%; border-radius: 9999px; background-color: #bf4036; transition: width 0.5s; width: {{ $percentage }}%;"></div>
            </div>
        </div>

        {{-- Steps --}}
        <div style="display: grid; gap: 1rem;">
            @foreach ($steps as $index => $step)
                @php
                    $isNext = $nextStep && $nextStep['title'] === $step['title'];
                    $isCompleted = $step['completed'] ?? false;
                    $isDisabled = $step['disabled'] ?? false;

                    $borderColor = $isCompleted ? '#10b981' : ($isNext ? '#bf4036' : '#d1d5db');
                    $bgColor = $isCompleted ? '#f0fdf4' : ($isNext ? '#fef2f2' : '#ffffff');
                @endphp

                <div style="display: flex; align-items: flex-start; gap: 1rem; border-radius: 0.75rem; border: 2px solid {{ $borderColor }}; background-color: {{ $bgColor }}; padding: 1.5rem; {{ $isDisabled ? 'opacity: 0.6;' : '' }}">
                    {{-- Icon --}}
                    <div style="flex-shrink: 0;">
                        @if ($isCompleted)
                            <div style="display: flex; align-items: center; justify-content: center; height: 3rem; width: 3rem; border-radius: 9999px; background-color: #10b981; color: white; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                                <x-filament::icon icon="heroicon-o-check" style="height: 1.5rem; width: 1.5rem;" />
                            </div>
                        @else
                            <div style="display: flex; align-items: center; justify-content: center; height: 3rem; width: 3rem; border-radius: 9999px; background-color: {{ $isNext ? '#bf4036' : '#9ca3af' }}; color: white; font-weight: 700; font-size: 1.25rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                                {{ $index + 1 }}
                            </div>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    <x-filament::icon :icon="$step['icon']" style="height: 1.25rem; width: 1.25rem; color: #9ca3af;" />
                                    <h3 style="font-size: 1rem; font-weight: 600; margin: 0;">{{ $step['title'] }}</h3>
                                    @if ($isCompleted)
                                        <x-filament::badge color="success" size="sm">✓ Done</x-filament::badge>
                                    @endif
                                    @if ($isDisabled)
                                        <x-filament::badge color="gray" size="sm">🔒 Locked</x-filament::badge>
                                    @endif
                                </div>
                                <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">{{ $step['description'] }}</p>
                            </div>

                            {{-- Button --}}
                            @if (!$isDisabled)
                                @if($step['url'] === '#')
                                    <x-filament::button
                                        color="success"
                                        icon="heroicon-o-check-circle"
                                        disabled
                                    >
                                        {{ $step['button'] }}
                                    </x-filament::button>
                                @else
                                    <x-filament::button
                                        :href="$step['url']"
                                        color="primary"
                                        icon="heroicon-o-arrow-right"
                                        icon-position="after"
                                        tag="a"
                                    >
                                        {{ $step['button'] }}
                                    </x-filament::button>
                                @endif
                            @else
                                <x-filament::button color="gray" disabled>
                                    {{ $step['button'] }}
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Help Section --}}
        <div style="margin-top: 1.5rem;">
            <x-filament::section
                icon="heroicon-o-information-circle"
                icon-color="info"
            >
                <x-slot name="heading">
                    Need Help?
                </x-slot>

                <div style="font-size: 0.875rem;">
                    <p style="margin-bottom: 0.5rem;">Complete each step to unlock invoice submission capabilities. If you need assistance:</p>
                    <ul style="margin-left: 1rem; list-style-type: disc; color: #6b7280;">
                        <li>Check the <a href="https://myinvois.hasil.gov.my" target="_blank" style="font-weight: 500; color: #bf4036; text-decoration: underline;">LHDN MyInvois documentation</a></li>
                        <li>Contact our support team for technical help</li>
                        <li>Ensure your company TIN is registered with LHDN</li>
                    </ul>
                </div>
            </x-filament::section>
        </div>

        {{-- Completion Message --}}
        @if($percentage === 100)
            <div style="margin-top: 1.5rem;">
                <x-filament::section
                    icon="heroicon-o-check-badge"
                    icon-color="success"
                >
                    <x-slot name="heading">
                        🎉 Setup Complete!
                    </x-slot>

                    <p style="font-size: 0.875rem; color: #6b7280;">
                        Congratulations! Your LHDN MyInvois middleware is fully configured and ready. You can now start submitting invoices to LHDN.
                    </p>
                </x-filament::section>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
