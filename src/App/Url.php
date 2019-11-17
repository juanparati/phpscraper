<?php


/**
 * Class Url.
 *
 * URL helper.
 *
 * @package App
 */
class Url
{

    /**
     * Destructured url.
     *
     * @var mixed
     */
    protected $url;


    /**
     * Url constructor.
     *
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = parse_url($url);
    }


    /**
     * Check if URL has hostname.
     *
     * @return bool
     */
    public function hasHostname() : bool
    {
        return !empty($this->url['hostname']);
    }


    /**
     * Obtain the destructured URL.
     *
     * @return array
     */
    public function getUrl() : array
    {
        return $this->url;
    }


    /**
     * Complete URL.
     *
     * @param $secondary
     * @return string
     */
    public function completeURL($secondary) : string
    {
        $secondary = is_string($secondary) ? (new static($secondary))->getUrl() : $secondary;
        $segments  = array_merge($secondary, $this->url);

        $url  = $segments['scheme'] . '://' . $segments['host'];
        $url .= empty($segments['port'])  ? '' : (':' . $segments['port']);
        $url .= empty($segments['path'])  ? '' : $segments['path'];
        $url .= empty($segments['query']) ? '' : ('?' . $segments['query']);

        return $url;
    }

}