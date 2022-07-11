<?php

namespace MichaelRubel\AutoBinder\Commands;

use Illuminate\Cache\Repository;
use Illuminate\Console\Command;
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
     * @param  Repository  $cache
     *
     * @return void
     */
    public function handle(Repository $cache): void
    {
        $clue = (new AutoBinder(
            $this->argument('folder')
        ))->cacheClue();

        collect($cache->get($clue))->each(
            fn ($concrete, $interface) => app()->offsetUnset($interface)
        );

        $cache->forget($clue);
    }
}
