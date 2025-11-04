<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Lịch sử hoạt động của {{ $this->record->name }}
        </x-slot>

        <x-slot name="description">
            Email: {{ $this->record->email }}<br>
            Số dư hiện tại: {{ number_format($this->record->wallet->balance ?? 0) }} VNĐ
        </x-slot>

        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page><x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Lịch sử hoạt động của {{ $this->record->name }}
        </x-slot>

        <x-slot name="description">
            Email: {{ $this->record->email }}<br>
            Số dư hiện tại: {{ number_format($this->record->wallet->balance ?? 0) }} VNĐ
        </x-slot>

        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>