<?php

namespace Tests\Unit;

use MathiasGrimm\LaravelDotEnvGen\FileScanner;
use SplFileInfo;

class FileScannerTest extends TestCase
{
    public function test_it_normalizes_paths()
    {
        $fileScanner = new FileScanner();

        $this->assertEquals('a', $fileScanner->normalizePath('a'));
        $this->assertEquals('a', $fileScanner->normalizePath('a/'));
        $this->assertEquals('a', $fileScanner->normalizePath('/a'));
        $this->assertEquals('a', $fileScanner->normalizePath('/a/'));
    }
    
    public function test_it_sorts_merged_rules_by_length_desc()
    {
        $fileScanner = new FileScanner();

        config([
            'dotenvgen' => [
                'include_path' => [
                    'a/b/c/d',
                    'a/b'
                ],
                'exclude_path' => [
                    'a/',
                    'a/b/c',
                ],
            ],
        ]);

        $merged = $fileScanner->getMergedRules();
        $keys = $merged->keys()->toArray();
        
        $this->assertEquals([
            'a/b/c/d',
            'a/b/c',
            'a/b',
            'a',
        ], $keys);
    }
    
    public function test_it_merges_rules()
    {
        $fileScanner = new FileScanner();
        
        config([
            'dotenvgen' => [
                'include_path' => [
                    'a/b'
                ],
                'exclude_path' => [
                    'a/'
                ],    
            ],
        ]);
        
        $merged = $fileScanner->getMergedRules();
        
        $this->assertCount(2, $merged);
        $this->assertEquals('include', $merged['a/b']);
        $this->assertEquals('exclude', $merged['a']);
    }

    public function test_it_throws_exception_on_duplicates()
    {
        $fileScanner = new FileScanner();

        config([
            'dotenvgen' => [
                'include_path' => [
                    'a/',
                    'a/',
                ],
                'exclude_path' => [
                    
                ],
            ],
        ]);

        $this->expectException(\Exception::class);
        $fileScanner->getMergedRules();
    }
    
    public function test_it_throws_exception_on_collisions()
    {
        $fileScanner = new FileScanner();

        config([
            'dotenvgen' => [
                'include_path' => [
                    'a/'
                ],
                'exclude_path' => [
                    'a/'
                ],
            ],
        ]);

        $this->expectException(\Exception::class);
        $fileScanner->getMergedRules();
    }
    
    public function test_it_allows_php_files()
    {
        $fileScanner = new FileScanner();
        
        $file = new SplFileInfo(__FILE__);
        $this->assertTrue($fileScanner->filterFile($file, []));
    }

    public function test_it_rejects_non_php_files()
    {
        $fileScanner = new FileScanner();

        $file = new SplFileInfo(__DIR__ . '/../../phpunit.xml');
        $this->assertFalse($fileScanner->filterFile($file, []));
    }

    public function test_it_allows_file_when_there_are_no_rules_that_match()
    {
        $fileScanner = new FileScanner();

        $this->assertTrue($fileScanner->filterFileByRules('a/b', [
            'a/b/c' => 'include',
        ]));
    }
    
    public function test_it_allows_file_when_parent_is_included()
    {
        $fileScanner = new FileScanner();

        $this->assertTrue($fileScanner->filterFileByRules('a/b', [
            'a' => 'include',
        ]));
    }

    public function test_it_rejects_file_when_parent_is_excluded()
    {
        $fileScanner = new FileScanner();

        $this->assertFalse($fileScanner->filterFileByRules('a/b', [
            'a' => 'exclude',
        ]));
    }
    
    public function test_it_allows_file_when_parent_is_excluded()
    {
        $fileScanner = new FileScanner();

        $this->assertTrue($fileScanner->filterFileByRules('a/b', [
            'a/b' => 'include',
            'a' => 'exclude',
        ]));
    }
    
    public function test_it_returns_files_with_exclusions()
    {
        config([
            'dotenvgen' => [
                'include_path' => [
                    'a/'
                ],
                'exclude_path' => [
                    'a/b/c/file2.php'
                ],
            ],
        ]);
        
        $fileScanner = new FileScanner();
        $files = $fileScanner->getFiles(__DIR__ . '/files');
        
        $this->assertEquals([
            __DIR__ . '/files/file1.php',
            __DIR__ . '/files/a/b/c/file1.php',
        ], $files->toArray());
    }

    public function test_it_returns_files_with_inclusions()
    {
        config([
            'dotenvgen' => [
                'include_path' => [
                    'a/b/c/file2.php'
                ],
                'exclude_path' => [
                    'a'
                ],
            ],
        ]);

        $fileScanner = new FileScanner();
        $files = $fileScanner->getFiles(__DIR__ . '/files');

        $this->assertEquals([
            __DIR__ . '/files/file1.php',
            __DIR__ . '/files/a/b/c/file2.php',
        ], $files->toArray());
    }


}