<?php

namespace Bo\Base\Services;

use Illuminate\Support\Facades\Storage;

class AddSidebarService
{
    public function add(string $code): string
    {
        $path = 'resources/views/vendor/bo/base/inc/sidebar_content.blade.php';
        $disk_name = config('bo.base.root_disk_name');
        $disk = Storage::disk($disk_name);

        if ($disk->exists($path)) {
            $contents = $disk->get($path);
            $file_lines = file($disk->path($path), FILE_IGNORE_NEW_LINES);

            if ($this->getLastLineNumberThatContains($code, $file_lines)) {
                return ('Sidebar item already existed.');
            }

            if ($disk->put($path, $contents . PHP_EOL . $code)) {
                return ('Successfully added code to sidebar_content file.');
            } else {
                return ('Could not write to sidebar_content file.');
            }
        } else {
            return ('The sidebar_content file does not exist. Make sure BoCMS is properly installed.');
        }
    }

    /**
     * Parse the given file stream and return the line number where a string is found.
     *
     * @param string $needle The string that's being searched for.
     * @param array $haystack The file where the search is being performed.
     * @return bool|int The last line number where the string was found. Or false.
     */
    private function getLastLineNumberThatContains($needle, $haystack)
    {
        $matchingLines = array_filter($haystack, function ($k) use ($needle) {
            return strpos($k, $needle) !== false;
        });

        if ($matchingLines) {
            return array_key_last($matchingLines);
        }

        return false;
    }
}
