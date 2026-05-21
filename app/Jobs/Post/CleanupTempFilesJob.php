<?php

namespace App\Jobs\Post;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

class CleanupTempFilesJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    public int $timeout = 300;

    public function handle(): void
    {
        $disk  = config('filesystems.default', 'local');
        $files = Storage::disk($disk)->allFiles('tmp/uploads');

        foreach ($files as $file) {
            $lastModified = Storage::disk($disk)->lastModified($file);
            if (time() - $lastModified > 86400) { // older than 24h
                Storage::disk($disk)->delete($file);
            }
        }
    }
}
