<?php

use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Model_Data.
 *
 * Dynamic model that extrac and contain the crawled data.
 */
class Model_Data extends Model
{

    /**
     * Recipe.
     *
     * @var array
     */
    protected $recipe;


    /**
     * DomCrawler instance.
     *
     * @var Crawler
     */
    protected $dom;


    /**
     * Memory fields.
     *
     * @var array
     */
    protected $memory = [];


    /**
     * Model_Data constructor.
     *
     * @param array $recipe
     * @param string $html
     */
    public function __construct(array $recipe, string $html)
    {
        $this->recipe = $recipe;
        $this->dom    = new Crawler($html);
    }


    /**
     * Extract data using the recipe.
     *
     * @param array $recipe
     * @param DOMElement|null $parentNode
     * @return array
     */
    public function extractData(
        ?array $recipe = null,
        ?DOMElement $parentNode = null
    ) : array
    {
        $data       = [];
        $recipe     = $recipe ?: $this->recipe['extraction'];
        $parentNode = $parentNode ? new Crawler($parentNode) : $this->dom;

        foreach ($recipe as $key => $value)
        {
            if (!empty($value['from_memory']))
            {
                $data[$value['extract_as']] = $this->memory[$value['extract_as']] ?? null;
                continue;
            }

            $nodes = $parentNode->filterXPath($value['xpath']);

            /**
             * @var $node DOMNode
             */
            foreach ($nodes as $node)
            {
                if (!empty($value['subelements']))
                {
                    $subnodeData = $this->extractData($value['subelements'], $node);

                    if (!empty($subnodeData))
                        $data[] = $subnodeData;
                }
                else if (!empty($value['extract_as']))
                {

                    // Discard subnodes.
                    if (!empty($value['discard']))
                    {
                        foreach ($value['discard'] as $discard)
                        {
                            (new Crawler($node->parentNode))
                                ->filterXPath($discard)
                                ->each(function (Crawler $crawler) {
                                    foreach ($crawler as $node) {
                                        $node->parentNode->removeChild($node);
                                    }
                                });
                        }
                    }

                    $content = trim($node->textContent);

                    if (!empty($value['extract_regex']))
                    {
                        if (preg_match($value['extract_regex'], $content, $matches))
                            $content = $matches[0];
                    }

                    if (!empty($value['cast_as']))
                        $content = Caster::cast($content, $value['cast_as']);

                    if (!empty($value['in_memory']))
                        $this->memory[$value['extract_as']] = $content;
                    else
                        $data[$value['extract_as']] = $content;
                }

            }
        }

        return $data;
    }


    /**
     * Extract the next page.
     *
     * @return string|null
     */
    public function extractNextPageUrl() : ?string
    {
        $xpath = $this->recipe['pagination']['next_xpath'];
        $node = $this->dom->filterXPath($xpath);

        if (!$node->count())
            return null;

        return $node->text();
    }

}