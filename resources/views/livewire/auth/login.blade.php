<div class="w-full max-w-sm">
    <x-card title="Login Inventaris" shadow separator>
        <x-form wire:submit="login">
            <x-input label="Email" wire:model="email" icon="o-envelope" />
            <x-input label="Password" wire:model="password" type="password" icon="o-key" />

            <x-slot:actions>
                <x-button label="Login" type="submit" class="btn-primary" spinner="login" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
