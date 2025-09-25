<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class FilesystemCheck extends Check
{
    /**
     * @var array
     */
    protected ?array $disks = [];

    /**
     * @var bool
     */
    protected bool $withDirectory = true;

    /**
     * @param array $disks
     * @return self
     */
    public function disks(array $disks): self
    {
        $this->disks = $disks;

        return $this;
    }

    /**
     * @param bool $withDirectory
     * @return self
     */
    public function withDirectory(bool $withDirectory): self
    {
        $this->withDirectory = $withDirectory;

        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @throws \Exception
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        if (! $this->disks) {
            throw new \Exception('Disks is not set.');
        }

        $result = Result::make();

        foreach ($this->disks as $disk => $hasUrl) {
            if (is_numeric($disk)) {
                $disk = $hasUrl;
                $hasUrl = false;
            }

            try {
                \Storage::disk($disk)->files();
            } catch (\Exception $e) {
                return $result->failed("Disk \"$disk\" is not reachable.");
            }

            if ($this->withDirectory) {
                do {
                    $path = 'temp_' . sha1(\Str::random(50, 200)) . '/';
                } while (\Storage::disk($disk)->exists($path));
            } else {
                $path = '';
            }

            do {
                $file = 'temp_' . sha1(\Str::random(50, 200));
            } while (\Storage::disk($disk)->exists("{$path}{$file}"));

            if ($error = $this->checkFlow($disk, $path, $file, $hasUrl)) {
                if ($path) {
                    \Storage::disk($disk)->deleteDirectory($path);
                }
                \Storage::disk($disk)->delete("{$path}{$file}");

                return $result->failed($error);
            }
        }

        return $result->ok();
    }

    /**
     * @param string $disk
     * @param string $path
     * @param string $file
     * @param bool $hasUrl
     * @return string|null
     */
    private function checkFlow(string $disk, string $path, string $file, bool $hasUrl): ?string
    {
        // create directory?
        try {
            if ($path) {
                \Storage::disk($disk)->makeDirectory($path);
            }
        } catch (\Exception $e) {
            return "Disk \"$disk\": cannot make a directory.";
        }

        // put file
        try {
            \Storage::disk($disk)->put("{$path}{$file}", 'foo');
        } catch (\Exception $e) {
            return "Disk \"$disk\": cannot put a file.";
        }

        // get file in list
        try {
            if (! in_array("{$path}{$file}", \Storage::disk($disk)->files($path))) {
                return "Disk \"$disk\": cannot get a file in the list.";
            }
        } catch (\Exception $e) {
            return "Disk \"$disk\": cannot get a file in the list.";
        }

        // get file
        try {
            if (\Storage::disk($disk)->get("{$path}{$file}") != 'foo') {
                return "Disk \"$disk\": cannot get a file.";
            }
        } catch (\Exception $e) {
            return "Disk \"$disk\": cannot get a file.";
        }

        // access file from direct link?
        $context = stream_context_create(['http' => ['method' => 'GET', 'ignore_errors' => true]]);
        $fetch = file_get_contents(\Storage::disk($disk)->url("{$path}{$file}"), false, $context);
        $headers = json_encode($http_response_header);
        if ($hasUrl && ($fetch != 'foo' || ! stripos($headers, ' 200 '))) {
            return "Disk \"$disk\": cannot fetch (via url) a public file.";
        }
        if (! $hasUrl && (! stripos($fetch, 'AccessDenied') || ! stripos($headers, ' 403 '))) {
            return "Disk \"$disk\": can fetch (via url) a private file.";
        }

        // access file from temporary link
        try {
            if (file_get_contents(\Storage::disk($disk)->temporaryUrl("{$path}{$file}", now()->addSeconds(10))) != 'foo') {
                return "Disk \"$disk\": cannot fetch (via temporary url) a file.";
            }
        } catch (\Exception $e) {
            return "Disk \"$disk\": cannot fetch (via temporary url) a file.";
        }

        // delete file
        try {
            \Storage::disk($disk)->delete("{$path}{$file}");
        } catch (\Exception $e) {
            return "Disk \"$disk\": cannot delete a file.";
        }

        // delete directory?
        try {
            if ($path) {
                \Storage::disk($disk)->deleteDirectory($path);
            }
        } catch (\Exception $e) {
            return "Disk \"$disk\": cannot delete a directory.";
        }

        return null;
    }
}
