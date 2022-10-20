<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 20.10.2022
 * Time: 11:53
 */

namespace CheckDomain;


class Response
{
    public function __construct(string $url)
    {
        $this->setUrl($url);
    }

    /**
     * @var int
     */
    private $status;
    /**
     * @var array
     */
    private $redirects = null;

    /**
     * @var string
     */
    private $url;
    /**
     * @var int
     */
    private $total = 0;
    /**
     * @var bool
     */
    private $result = false;
    /**
     * @var array
     */
    private $urls;

    public function setStatus(int $status)
    {
        $this->status = $status;
        return $this;
    }

    public function setRedirects(array $redirects)
    {
        $this->redirects = $redirects;
        return $this;
    }

    public function status()
    {
        return $this->status;
    }

    public function redirects()
    {
        return $this->redirects;
    }

    public function preparationResults()
    {
        if (!$this->result) {
            $this->addUrl($this->url(), $this->url(), false, $this->status());
            if ($redirects = $this->redirects()) {
                $this->resetUrls();
                $total = 0;
                foreach ($redirects as $redirect) {
                    $this->addUrl($redirect['from'], $redirect['to'], true, $redirect['status_code']);
                    $total++;
                }
                $this->setTotal($total);
                $this->addUrl($redirect['from'], $redirect['to'], false, $this->status());
            }
            $this->result = true;
        }
    }

    public function toArray()
    {
        return [
            'status' => $this->status(),
            'urls' => $this->urls(),
            'total' => $this->total(),
        ];
    }

    protected function addUrl(string $from, string $to, bool $redirect, int $status)
    {
        $data = parse_url($to);
        $this->urls[] = [
            'from' => $from,
            'to' => $to,
            'redirect' => $redirect,
            'status_code' => $status,
            'schema' => $data['scheme']
        ];
        return $this;
    }

    protected function resetUrls()
    {
        $this->urls = [];
        return $this;
    }

    public function urls()
    {
        return $this->urls;
    }

    protected function setUrl(string $url)
    {
        $this->url = $url;
        return $this;
    }

    public function url()
    {
        return $this->url;
    }


    protected function setTotal(int $total)
    {
        $this->total = $total;
        return $this;
    }

    public function total()
    {
        return $this->total;
    }

}
