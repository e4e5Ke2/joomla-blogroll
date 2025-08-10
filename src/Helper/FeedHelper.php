<?php

namespace My\Module\Blogroll\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class FeedHelper
{

    public function multicurl($urlListString, $timeout) {
        $rssUrls = [];
        foreach (preg_split("/\r\n|\n|\r/", $urlListString) as $url) {
            if (trim($url) !== '') {
                $rssUrls[] = filter_var($url, FILTER_SANITIZE_URL);
            }
        }

        $master = curl_multi_init();
        $urlCount = count($rssUrls);
        $curl_arr = [];

        for ($i = 0; $i < $urlCount; $i++) {
            $url = $rssUrls[$i];
            $curl_arr[$i] = curl_init($url);
            curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);

            // Adding a valid user agent string, otherwise some feed-servers return an error
            curl_setopt($curl_arr[$i], CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0');

            // This one is necessary for redirects, e.g. in case of wordpress
            curl_setopt($curl_arr[$i], CURLOPT_FOLLOWLOCATION, true);
            curl_multi_add_handle($master, $curl_arr[$i]);
        }

        $curlExecStart = time();
        do {
            curl_multi_exec($master, $running);
        } while ($running > 0 && (time() - $curlExecStart) <= $timeout);

        $responses = [];
        for ($i = 0; $i < $urlCount; $i++) {
            $response = curl_multi_getcontent($curl_arr[$i]);
            $responses[$rssUrls[$i]] = $response;
        }

        return $responses;
    }

    public function getFeeds($params, Translations $translations)
    {
        $urlListString = $params->get('rssurl_list', '');
        $responses = $this->multicurl($urlListString, $params->get('rss_timeout', 5));

        $rssParser = new RssParser();
        $feeds = [];
        foreach ($responses as $response) {

            if (!$response)
                continue;

            try {
                libxml_use_internal_errors(true);
                $feed = $rssParser->parse($response, $params, $translations);

                if ($feed?->is_data_complete()) {
                    $feeds[] = $feed;
                }
                libxml_use_internal_errors(false);
            } catch (\Exception) {
                // We swallow this.
            }
        }

        if ($params->get('rsssorting', 1)) {
            usort($feeds, fn($a, $b) => $a->pubDate < $b->pubDate ? 1 : -1);
        }

        return $feeds;
    }

}
