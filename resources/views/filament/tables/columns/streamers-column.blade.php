<div>
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <x-hugeicons-twitch class="w-8 h-8 text-gray-400 dark:text-gray-600"/>
            <div class="block">
                <x-filament-tables::columns.layout
                    :components="$getComponents()"
                    :record="$getRecord()"
                    :record-key="$recordKey"
                />
            </div>
        </div>
        <div class="relative">
            {{ ($this->twitchViewAction)([
                'username' => $getRecord()->twitch_username,
                'live' => $getRecord()->is_live,
            ]) }}
        </div>
    </div>
    <div class="flex items-center justify-end pt-2">
        <x-filament::badge icon="hugeicons-voice" :color="$getRecord()->is_live ? 'primary' : 'gray'">
            {{ $getRecord()->is_live ? __('live') : __('offline') }}
        </x-filament::badge>
    </div>
</div>
