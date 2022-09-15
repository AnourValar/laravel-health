<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class FilesystemCheck extends Check
{
    /**
     * @var array
     */
    public ?array $disks = [];

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

        foreach ($this->disks as $disk) {
            try {
                \Storage::disk($disk)->files();
            } catch (\Exception $e) {
                return $result->failed("Disk \"$disk\" is not reachable.");
            }

            do {
                $path = sha1(\Str::random(50, 200));
            } while (\Storage::disk($disk)->exists($path));

            if ($error = $this->checkFlow($disk, $path, 'file')) {
                \Storage::disk($disk)->deleteDirectory($path);

                return $result->failed($error);
            }
        }

        return $result->ok();
    }

    /**
     * @param string $disk
     * @param string $path
     * @param string $file
     * @return string|null
     */
    private function checkFlow(string $disk, string $path, string $file): ?string
    {
        try {
            \Storage::disk($disk)->makeDirectory($path);
        } catch (\Exception $e) {
            return "Disk \"$disk\": cannot make a directory.";
        }

        try {
            \Storage::disk($disk)->put("$path/$file", 'foo');
        } catch (\Exception $e) {
            return "Disk \"$disk\": cannot put a file.";
        }

        try {
            if (\Storage::disk($disk)->get("$path/$file") != 'foo') {
                return "Disk \"$disk\": cannot get a file.";
            }
        } catch (\Exception $e) {
            return "Disk \"$disk\": cannot get a file.";
        }

        try {
            \Storage::disk($disk)->delete("$path/$file");
        } catch (\Exception $e) {
            return "Disk \"$disk\": cannot delete a file.";
        }

        try {
            \Storage::disk($disk)->deleteDirectory($path);
        } catch (\Exception $e) {
            return "Disk \"$disk\": cannot delete a directory.";
        }

        return null;
    }
}
