<?php
namespace mathiasgrimm\laraveldotenvgen;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Helper\Table;

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
     * @var \Symfony\Component\Console\Helper\ProgressHelper
     */
    protected $progressBar;

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{

        $this->info('preparing files');

        $directory = new \RecursiveDirectoryIterator(base_path());
        $iterator  = new \RecursiveIteratorIterator($directory);
        $regex     = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::ALL_MATCHES);

        $total = 0;
        foreach ($regex as $i=>$v) {
            $total++;
        }

        $this->info('searching files');
        $this->progressBar = $this->getHelperSet()->get('progress');
        $this->progressBar->start($this->output, $total);

        $all      = [];
        $envFound = [];

        foreach ($regex as $i=>$v) {
            $this->progressBar->advance();
            $fgc      = file_get_contents($i);
            $matches  = null;
            $matches2 = null;

            if (preg_match_all('/[^\w-_]env\s*\((\'|").*?(\'|")\)/sim', $fgc, $matches)) {
                foreach ($matches[0] as $match) {
                    preg_match('/\(\s*(\'|")(?P<var>.*?)(\'|").*?\)/', $match, $matches2);
                    $envFound[$matches2['var']] = true;
                    $all[$matches2['var']] = new \stdClass();
                }
            }
        }

        $this->progressBar->finish();
        ksort($envFound);

        $content = '';
        foreach (array_keys($envFound) as $var) {
            $content .= "{$var}=?????\n";
        }

        $this->info('saving all variables the we found to the .env.gen file');

        file_put_contents(base_path() . '/.env.gen', $content);

        $envDefined = [];

        foreach (file(base_path() . '/.env') as $line) {
            if (!preg_match('/=/', $line)) {
                continue;
            }

            $matches = null;
            preg_match('/(.*?)=/', $line, $matches);
            $envDefined[$matches[1]] = true;

            $all[$matches[1]] = new \StdClass;
        }

        ksort($all);

        foreach ($all as $name => $var) {
            $all[$name]->onSource = isset($envFound[$name]);
            $all[$name]->onDotEnv = isset($envDefined[$name]);
        }

        $table = new Table($this->output);

        $table->setHeaders([
            'Name',
            'On .env',
            'On source'
        ]);

        $rows = [];
        foreach ($all as $var => $data) {
            $tmp = [$var];

            if ($data->onDotEnv) {
                $tmp[] = 'Yes';
            } else {
                $tmp[0] = "<question>{$var}</question>";
                $tmp[] = "<error>No</error>";
            }

            if ($data->onSource) {
                $tmp[] = 'Yes';
            } else {
                $tmp[0] = "<question>{$var}</question>";
                $tmp[] = "<comment>No</comment>";
            }

            $rows[] = $tmp;
        }

        $table->setRows($rows);

        $table->render();

        $this->info('done');
	}
}
