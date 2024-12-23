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
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-1">
                <x-hugeicons-voice @class([
                    'text-primary-500' => $getRecord()->is_live,
                    'text-gray-200 dark:text-gray-600' => !$getRecord()->is_live,
                ])/>
                <span @class([
                    'block text-sm',
                    'text-primary-400 font-bold' => $getRecord()->is_live,
                    'text-gray-400 dark:text-gray-500' => !$getRecord()->is_live,
                ])>
                    {{ $getRecord()->is_live ? __('live') : __('offline') }}
                </span>
            </div>
        </div>
    </div>
</div>
