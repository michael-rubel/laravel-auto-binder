<?php

declare(strict_types=1);

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
    protected $signature = 'binder:clear {folder : Folder to clear the cache from}';

    /**
     * @var string
     */
    protected $description = 'Clear a cached bindings';

    /**
     * Execute the command.
     */
    public function handle(Repository $cache): void
    {
        $this->getFolders()->each(function ($folder) use ($cache) {
            $clue = $this->getClueFor($folder);

            if (! $cache->has($clue)) {
                $this->warn('Cached folder ' . $folder . ' not found.');

                return;
            }

            $this->flushContainerBindings($cache, $clue);

            $cache->forget($clue);
        });

        $this->info('Container binding cache cleared successfully!');
    }

    /**
     * Retrieves folders to flush from the command input.
     *
     * @return Collection<int, string>
     */
    private function getFolders(): Collection
    {
        $folders = explode(',', $this->argument('folder'));

        return collect($folders);
    }

    /**
     * Retrieves the cache clue for the specified folder.
     */
    private function getClueFor(string $folder): string
    {
        return (new AutoBinder($folder))->cacheClue();
    }

    /**
     * Cleans up the application container bindings.
     */
    private function flushContainerBindings(Repository $cache, string $clue): void
    {
        with($cache->get($clue), fn ($fromCache) => collect($fromCache)->each(
            fn ($concrete, $interface) => app()->offsetUnset($interface)
        ));
    }
}
