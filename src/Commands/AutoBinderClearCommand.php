<?php

namespace MichaelRubel\AutoBinder\Commands;

use Illuminate\Cache\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use MichaelRubel\AutoBinder\AutoBinder;

class AutoBinderClearCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'binder:clear {folders : Folders to clear the cache from}';

    /**
     * @var string
     */
    protected $description = 'Clear a cached bindings';

    /**
     * @param  Repository  $cache
     *
     * @return int
     */
    public function handle(Repository $cache): int
    {
        $this->getFolders()->each(function ($folder) use ($cache) {
            $clue = $this->getClueFor($folder);

            if (! $cache->has($clue)) {
                $this->components->info(
                    'Cached folder ' . $folder . ' not found.'
                );

                return;
            }

            $this->flushContainerBindings($cache, $clue);

            $cache->forget($clue);
        });

        $this->components->info(
            'Container binding cache cleared successfully!'
        );

        return Command::SUCCESS;
    }

    /**
     * Retrieves folders to flush from the command input.
     *
     * @return Collection
     */
    private function getFolders(): Collection
    {
        $folders = explode(',', $this->argument('folders'));

        return collect($folders);
    }

    /**
     * Retrieves the cache clue for the specified folder.
     *
     * @param  string  $folder
     *
     * @return string
     */
    private function getClueFor(string $folder): string
    {
        return (new AutoBinder($folder))->cacheClue();
    }

    /**
     * Cleans up the application container bindings.
     *
     * @param  Repository  $cache
     * @param  string  $clue
     *
     * @return void
     */
    private function flushContainerBindings(Repository $cache, string $clue): void
    {
        with($cache->get($clue), fn ($fromCache) => collect($fromCache)->each(
            fn ($concrete, $interface) => app()->offsetUnset($interface)
        ));
    }
}
