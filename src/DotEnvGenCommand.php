<?php namespace MathiasGrimm\LaravelDotEnvGen;

use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;

class DotEnvGenCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'env:gen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a `.env.gen` file based on environment variables used throughout the project.';

    /**
     * @var \Symfony\Component\Console\Helper\ProgressBar
     */
    protected $progressBar;

    /**
     * @var \RegexIterator
     */
    protected $iterator;

    /**
     * @var array
     */
    protected $all = [];

    /**
     * @var array
     */
    protected $found = [];

    /**
     * @var array
     */
    protected $defined = [];

    /**
     * @var array
     */
    protected $defaults = [];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->gatherFiles();
        $this->scanFiles();
        $this->scanEnv();
        $this->generateFile();
        $this->info('Done. Results:');
        $this->showResults();

        $this->info('By Laravel Dot Env Generator - https://github.com/mathiasgrimm/laravel-dot-env-gen');
    }

    protected function gatherFiles()
    {
        $this->info('Gathering PHP files...');
        $this->info('You can speed up this process by excluding folders. Check the README for more info.');

        $directory = new \RecursiveDirectoryIterator(base_path());
        $iterator  = new \RecursiveIteratorIterator($directory);
        $rules     = \Config::get('dotenvgen.rules');
        $ignore    = implode('|', array_map(function ($path) use ($rules) {
            if (!empty($rules[$path])) {
                $excludes = $rules[$path];

                if (is_array($excludes)) {
                    $excluded = implode('|', array_map(function ($sub) {
                        return preg_quote(DIRECTORY_SEPARATOR . $sub, '/');
                    }, array_filter($excludes)));
                } else {
                    $excluded = preg_quote(DIRECTORY_SEPARATOR . $excludes, '/');
                }
            }

            return preg_quote($path, '/') . (!empty($excluded) ? '(?!' . $excluded . ')' : '');
        }, array_filter(array_keys($rules))));

        if (!empty($ignore)) {
            $regex = '/^(?!' . preg_quote(base_path() . DIRECTORY_SEPARATOR, '/') . '(' . $ignore . ')' . ').+\.php$/i';
        } else {
            $regex = '/^.+\.php$/i';
        }

        $this->iterator = new \RegexIterator($iterator, $regex, \RecursiveRegexIterator::GET_MATCH);
    }

    protected function scanFiles()
    {
        $count = iterator_count($this->iterator);

        $this->info("Scanning $count files...");

        $this->progressBar = new ProgressBar($this->output, $count);
        $this->progressBar->start();

        foreach ($this->iterator as $i => $v) {
            $this->progressBar->advance();

            $contents = file_get_contents($i);
            $matches  = null;

            if (preg_match_all('/[^\w_]env\s*\((\'|").*?(\'|")\s*.*?\)/sim', $contents, $matches)) {
                foreach ($matches[0] as $match) {
                    $matches2 = null;

                    preg_match('/\(\s*(\'|")(?P<name>.*?)(\'|")(,(?P<default>.*))?\)/', $match, $matches2);

                    $this->found[$matches2['name']]    = '';
                    $this->defaults[$matches2['name']] = isset($matches2['default']) ? trim($matches2['default']) : null;
                }
            }
        }

        $this->progressBar->finish();
        
        $this->info('');
    }

    protected function scanEnv()
    {
        $this->info('Scanning `.env` file...');

        if (!file_exists(base_path('.env'))) {
            return;
        }

        foreach (file(base_path('.env')) as $line) {
            if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            list($name, $value) = array_map('trim', explode('=', $line, 2));

            $this->defined[$name] = $value;
        }
    }

    protected function generateFile()
    {
        $this->info('Generating `.env.gen` file...');

        $this->all = array_merge($this->found, $this->defined);

        ksort($this->all);

        $content = '';

        foreach ($this->all as $key => $val) {
            $content .= "$key=$val\n";
        }

        file_put_contents(base_path('.env.gen'), $content);
    }

    protected function showResults()
    {
        $table = new Table($this->output);

        $table->setHeaders([
            'Name',
            'In .env',
            'In source',
            'Default'
        ]);

        $rows = [];

        foreach ($this->all as $key => $val) {
            $row = [$key];

            if (array_key_exists($key, $this->defined)) {
                $row[] = 'Yes';
            } else {
                $row[0] = "<question>$key</question>";
                $row[]  = '<error>No</error>';
            }

            if (array_key_exists($key, $this->found)) {
                $row[] = 'Yes';
            } else {
                $row[0] = "<question>$key</question>";
                $row[]  = '<comment>No</comment>';
            }

            $row[]  = array_get($this->defaults, $key);
            $rows[] = $row;
        }

        $table->setRows($rows);
        $table->render();
    }
}
