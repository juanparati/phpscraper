<?php
use Amp\Artax\Cookie\ArrayCookieJar;
use Amp\Artax\DefaultClient;
use Amp\Artax\Request;
use function Amp\asyncCall;


/**
 * Class Crawler
 */
class Controller_Crawler
{


    /**
     * Climate instance for console output.
     *
     * @var League\CLImate\CLImate
     */
    protected $console;


    /**
     * HTTP Client.
     *
     * @var DefaultClient
     */
    protected $client;


    /**
     * RequestThrottle instance.
     *
     * @var ThreadThrottle|null
     */
    protected $requestThrottle = null;


    /**
     * Crawler constructor.
     */
    public function __construct()
    {
        $this->console = Container::get('console');

        $this->client = new DefaultClient(new ArrayCookieJar());

        $this->client->setOptions([
            DefaultClient::OP_DEFAULT_HEADERS  => ['User-Agent' => Params::get('user-agent')],
            DefaultClient::OP_TRANSFER_TIMEOUT => Params::get('timeout'),
            DefaultClient::OP_MAX_REDIRECTS    => Params::get('max-redirects'),
        ]);
    }


    /**
     * Controller entry point.
     *
     * @param string $entrypoint
     * @return Generator
     */
    public function actionMain()
    {
        $this->console->info('🕸 Initialized crawling process');

        $recipe = Container::get('recipe');
        $url    = $recipe['url'];

        $maxRequestSec = (int) Params::get('max-req-sec');

        if (!empty($maxRequestSec))
            $this->requestThrottle = new ThreadThrottle((int) $maxRequestSec);

        $this->crawlContent($url, $recipe);
    }


    /**
     * Crawl and output content.
     *
     * @param string $url
     * @param array $recipe
     */
    protected function crawlContent(string $url, array $recipe)
    {

        $this->client->request(new Request($url))->onResolve(function ($error, $response) use ($url, $recipe) {

            if ($error)
            {
                $this->console->out('Unable to process request');
                Apprunner::terminate(Apprunner::EXIT_FAILURE);
            }

            $content = yield $response->getBody();

            $data = new Model_Data($recipe, $content);
            $next_url = $data->extractNextPageUrl();

            if ($next_url)
            {
                asyncCall(function () use ($url, $next_url, $recipe) {

                    $new_url = new Url($next_url);

                    if (!$new_url->hasHostname())
                        $new_url = $new_url->completeURL($url);
                    else
                        $new_url = $next_url;

                    if ($this->requestThrottle)
                        yield $this->requestThrottle->addOperations();

                    $this->crawlContent($new_url, $recipe);
                });
            }

            $data = $data->extractData();

            $this->console->out("- $url");

            foreach ($data as $record)
            {
                $this->console->to('out')->out(
                    json_encode($record,
                        JSON_NUMERIC_CHECK
                        | JSON_UNESCAPED_UNICODE
                        | JSON_PRESERVE_ZERO_FRACTION)
                );
            }

        });


    }
}