<?php

use Amp\Loop;
use function Amp\asyncCall;

use League\CLImate\CLImate;
use League\CLImate\Util\Writer\File as FileWriter;
use Symfony\Component\Yaml\Yaml;


/**
 * Default controller entry-point
 */
class Controller_Main extends Controller
{

    /**
     * Climate instance for console output.
     *
     * @var CLImate
     */
    protected $console;


    /**
     * Controller_Main constructor.
     */
    public function __construct()
    {
        $this->console = new CLImate();
    }


    /**
     * Entry point
     */
    public function actionMain()
    {

        // Attach your IPC signals handling below this line
        Hook::instance()->attach('IPC_SIGHUP', Closure::fromCallable([$this, 'actionTerminateBySignal']));
        Hook::instance()->attach('IPC_SIGINT', Closure::fromCallable([$this, 'actionTerminateBySignal']));


        // Display help and validate input
        // ===============================
        if (Params::get('help') || Params::validate()) {
            $this->actionHelp();
            Apprunner::terminate(Apprunner::EXIT_SUCCESS);
        }

        // Read mapfile
        // ============
        if (Params::get('recipe')) {
            if (!File::exists(Params::get('recipe'), File::SCOPE_EXTERNAL))
            {
                $this->console->error('Recipe doesn\'t exists');
                Apprunner::terminate(Apprunner::EXIT_FAILURE);
            }

            $recipe = Yaml::parseFile(
                File::parsePath(
                    Params::get('recipe')
                )
            );
        }

        // Check URL parameter
        // ===================
        $recipe['url'] = Params::get('url') ?: ($recipe['url'] ?? null);

        if (empty($recipe['url']))
            $recipe['url'] = ($this->console->input('🌍 URL?'))->prompt();

        if (filter_var($recipe['url'], FILTER_VALIDATE_URL) === false) {
            $this->console->error('Wrong URL');
            Apprunner::terminate(Apprunner::EXIT_CONFIG);
        }

        // Check extractions parameter
        // ===========================
        if (empty($recipe['extraction'])) {
            $this->console->error('Missing extraction instructions');
            Apprunner::terminate(Apprunner::EXIT_CONFIG);
        }

        // Save recipe on the global container
        // ===================================
        Container::set('recipe', $recipe);


        // Check output file
        // =================
        if ($outputFile = Params::get('file'))
        {
            if (!touch($outputFile))
            {
                $this->console->error('Unable to write in file ' . $outputFile);
                Apprunner::terminate(Apprunner::EXIT_FAILURE);
            }

            $this->console->output->add('out', new FileWriter($outputFile));
        }

        /*
        $this->console->output->add('tty',  new FileWriter('/dev/tty'));
        $this->console->output->defaultTo('tty');
        */

        Container::set('console', $this->console);

        $this->console->info('🚀 Extracting content...');

        $startTime = microtime(true);

        // Initialize event loop
        // =====================
        Loop::run(function () {
            $crawler = new Controller_Crawler();
            asyncCall(\Closure::fromCallable([$crawler, 'actionMain']));
        });

        $this->console->info('🏁 Finished in ' . round(microtime(true) - $startTime, 2) . ' secs.');

        // Finish execution
        // ================
        Apprunner::terminate(Apprunner::EXIT_SUCCESS);
    }


    /**
     * Terminate application when signal.
     *
     * @param int $signal
     */
    protected function actionTerminateBySignal(int $signal) : void
    {

        if (!ignore_user_abort())
        {
            $exit_status = Apprunner::EXIT_FAILURE;

            switch ($signal)
            {
                // SIGHUP
                case 1:
                    $exit_status = Apprunner::EXIT_HUP;
                    break;

                // SIGINT
                case 2:
                    $exit_status = Apprunner::EXIT_CTRLC;
            }

            echo "Exiting...";

            Apprunner::terminate($exit_status);
        }
    }


    /**
     * Display help view.
     *
     * @param string $help_view
     */
    protected function actionHelp($help_view = 'main'): void
    {
        $help = file_get_contents(APPPATH.'View/help.txt');
        $help = str_replace('#{{__EXECUTABLE__}}', basename(Phar::running()), $help);

        $this->console->out($help);
    }

}