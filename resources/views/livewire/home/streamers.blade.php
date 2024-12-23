<div>
    <div class="flex items-center justify-between py-4">
        <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-primary-500 dark:text-primary-400 sm:text-3xl">
            @lang('Streamers')
        </h1>

        {{ $this->newStreamerAction }}
    </div>

    {{ $this->table }}
</div>
