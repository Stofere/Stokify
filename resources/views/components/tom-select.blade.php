@props([
    'options' => [],
    'selected' => null,
    'placeholder' => 'Pilih salah satu...'
])

<div
    wire:ignore
    x-data="{
        options: {{ json_encode($options) }},
        selected: @entangle($attributes->wire('model')),
        placeholder: '{{ $placeholder }}',
        tomSelect: null,
        init() {
            this.tomSelect = new TomSelect(this.$refs.select, {
                options: this.options,
                items: [this.selected],
                placeholder: this.placeholder,
                // Opsi lain jika diperlukan
                // create: true, // Izinkan membuat item baru
            });

            this.$watch('options', (newOptions) => {
                this.tomSelect.clearOptions();
                this.tomSelect.addOptions(newOptions);
            });

            this.$watch('selected', (newValue) => {
                if (this.tomSelect.getValue() !== newValue) {
                    this.tomSelect.setValue(newValue);
                }
            });

            this.tomSelect.on('change', (value) => {
                this.selected = value;
            });
        }
    }"
>
    <select x-ref="select" class="w-full"></select>
</div>