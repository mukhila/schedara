<?php

namespace App\Jobs\Media;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

class CleanupMediaTempJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    public int $timeout = 300;

    public function handle(): void
    {
        $disk  = config('filesystems.default', 'local');
        $files = Storage::disk($disk)->allFiles('uploads/temp');

        foreach ($files as $file) {
            if (time() - Storage::disk($disk)->lastModified($file) > 86400) {
                Storage::disk($disk)->delete($file);
            }
        }
    }
}
