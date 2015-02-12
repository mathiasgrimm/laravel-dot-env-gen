<?php
namespace mathiasgrimm\laraveldotenvgen;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class EnvGen extends Command 
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
	protected $description = 'Searches for environment variables into the project and generates a .env.gen file';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
        $this->info('searching files');

        $directory = new \RecursiveDirectoryIterator(base_path());
        $iterator  = new \RecursiveIteratorIterator($directory);
        $regex     = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::ALL_MATCHES);

        $envFound = [];

        foreach ($regex as $i=>$v) {
            $fgc      = file_get_contents($i);
            $matches  = null;
            $matches2 = null;

            if (preg_match_all('/[^\w-_]env\s*\((\'|").*?(\'|")\)/sim', $fgc, $matches)) {
                foreach ($matches[0] as $match) {
                    preg_match('/\(\s*(\'|")(?P<var>.*?)(\'|").*?\)/', $match, $matches2);
                    $envFound[$matches2['var']] = true;
                }
            }
        }

        ksort($envFound);

        $content = '';
        foreach (array_keys($envFound) as $var) {
            $content .= "{$var}=?????\n";
        }

        $this->info('saving all variables found to the .env.gen file');

        file_put_contents(base_path() . '/.env.gen', $content);

        $envDefined = [];

        foreach (file(base_path() . '/.env') as $line) {
            if (!preg_match('/=/', $line)) {
                continue;
            }

            $matches = null;
            preg_match('/(.*?)=/', $line, $matches);
            $envDefined[$matches[1]] = true;
        }

        $this->info('checking if a found variable doest not exists on the .env file');

        foreach (array_keys($envFound) as $var) {
            if (!isset($envDefined[$var])) {
                $this->comment("variable [{$var}] does not exist on the .env file");
            }
        }

        $this->info('checking if an existent variable from the .env file is not being used anywhere');

        foreach (array_keys($envDefined) as $var) {
            if (!isset($envFound[$var])) {
                $this->comment("variable [{$var}] from .env file is not being used anywhere");
            }
        }

        $this->info('done');
	}
}
