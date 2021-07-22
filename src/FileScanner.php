<?php


namespace MathiasGrimm\LaravelDotEnvGen;


use CallbackFilterIterator;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SplFileInfo;

class FileScanner
{

    /**
     * @param string|null $path
     * @return Collection
     * @throws Exception
     */
    public function getFiles(string $path = null): Collection
    {
        $path = $path ?: base_path();
        
        $filesIterator = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($filesIterator);
        
        $merged = $this->getMergedRules();
        
        $iterator = new CallbackFilterIterator($iterator, function ($item) use ($merged, $path) {
            return $this->filterFile($item, $merged, $path);
        });
        
        return collect(iterator_to_array($iterator))->map(function ($item) {
            return $item->getPathname();
        })->keys();
    }

    /**
     * @return Collection
     * @throws Exception
     */
    public function getMergedRules(): Collection
    {
        $rules = config('dotenvgen');
        $merged = collect();

        $includePath = $rules['include_path'] ?? [];
        $excludePath = $rules['exclude_path'] ?? [];

        foreach ($includePath as $path) {
            $path = $this->normalizePath($path);

            if (isset($merged[$path])) {
                throw new Exception("Duplicate: {$path} it's already in the include_dir");
            }
            
            $merged[$path] = 'include';
        }

        foreach ($excludePath as $path) {
            $path = $this->normalizePath($path);

            if (isset($merged[$path])) {
                throw new Exception("Collision: {$path} is present both in the include_dir and exclude_dir");
            }

            $merged[$path] = 'exclude';
        }

        return $merged->sortBy(function ($item, $key) {
            return strlen($key);
        }, SORT_REGULAR, true);
    }

    /**
     * @param string $path
     * @return string
     */
    public function normalizePath(string $path): string
    {
        return trim($path, '/');
    }
    
    /**
     * @param SplFileInfo $file
     * @param $merged
     * @return bool
     */
    public function filterFile(SplFileInfo $file, $merged, $basePath = null): bool
    {
        if (!$file->isFile()) {
            return false;
        }

        if (!in_array($file->getExtension(), ['php'])) {
            return false;
        }

        $basePath = $basePath ?: base_path();
        
        $relativePath = trim(str_replace($basePath, '', $file->getPathname()), '/');

        return $this->filterFileByRules($relativePath, $merged);
    }

    /**
     * @param string $pathname
     * @param $merged
     * @return bool
     */
    public function filterFileByRules(string $relativePath, $merged): bool
    {
        foreach ($merged as $path => $rule) {
            if (Str::startsWith($relativePath, $path)) {
                return $rule == 'include';
            }
        }
        
        return true;
    }
}