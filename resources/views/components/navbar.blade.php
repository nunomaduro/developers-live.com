<nav class="relative w-full py-6 bg-inherit">
    <div class="mx-auto">
        <div class="w-full flex flex-col lg:flex-row">
            <div class="flex justify-between lg:flex-row w-full">
                <ul class="flex items-center ml-auto gap-4">
                    <li>
                        <x-filament::icon-button
                            icon="hugeicons-sun-02"
                            color="gray"
                            size="lg"
                            tooltip="{{ __('Toggle Light Mode') }}"
                            @click="$store.darkMode.toggle()" x-show="$store.darkMode.on"
                        />
                        <x-filament::icon-button
                            icon="hugeicons-moon-02"
                            color="gray"
                            size="lg"
                            tooltip="{{ __('Toggle Dark Mode') }}"
                            @click="$store.darkMode.toggle()" x-show="!$store.darkMode.on"
                        />
                    </li>
                    <li>
                        <a target="_blank" href="https://github.com/nunomaduro/developers-live.com">
                            <x-filament::icon-button
                                icon="hugeicons-github"
                                color="gray"
                                size="lg"
                                tooltip="{{ __('Github Repository') }}"
                            />
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
