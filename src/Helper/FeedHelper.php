<?php

namespace Joomla\Module\Feed\Site\Helper;

use DateTimeImmutable;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class FeedHelper
{

    public function getFeedInformation($params)
    {

        $urlListString = $params->get('rssurl_list', '');
        $nodes = array_map(fn($url): string => filter_var($url, FILTER_SANITIZE_URL), preg_split("/\r\n|\n|\r/", $urlListString));
        $feeds = [];

        $curlStart = microtime(true);
        $master = curl_multi_init();
        $node_count = count($nodes);
        Log::add('url count: ' . $node_count, Log::DEBUG, 'whatevs');
        $curl_arr = [];

        for ($i = 0; $i < $node_count; $i++) {
            $url = $nodes[$i];
            $curl_arr[$i] = curl_init($url);
            curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);

            // Adding a valid user agent string, otherwise some feed-servers return an error
            curl_setopt($curl_arr[$i], CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0');

            // This one is necessary for redirects, e.g. in case of wordpress
            curl_setopt($curl_arr[$i], CURLOPT_FOLLOWLOCATION, true);
            curl_multi_add_handle($master, $curl_arr[$i]);
        }

        // TODO - add timeout
        do {
            curl_multi_exec($master, $running);
        } while ($running > 0);

        $results = [];
        for ($i = 0; $i < $node_count; $i++) {
            $results[$i] = curl_multi_getcontent($curl_arr[$i]);
        }
        $curlEnd = microtime(TRUE);
        Log::add('multi curl time: ' . floor(($curlEnd - $curlStart) * 1000) . 'ms', Log::DEBUG, 'performance');

        $parseStart = microtime(true);
        $rssParser = new RssParser();
        for ($x = 0; $x < count($nodes); $x++) {

            try {
                libxml_use_internal_errors(true);
                $feed = $rssParser->parse($nodes[$x], $results[$x]);

                if ($feed && $feed->is_data_complete()) {
                    $feeds[] = $feed;
                }
                libxml_use_internal_errors(false);
            } catch (\Exception) {
                // We swallow this.
            }
        }


        $parseEnd = microtime(TRUE);
        Log::add('parse time: ' . floor(($parseEnd - $parseStart) * 1000) . 'ms', Log::DEBUG, 'performance');

        if ($params->get('rsssorting', 1)) {
            usort($feeds, fn($a, $b) => $a->pubDate < $b->pubDate);
        }

        return $feeds;
    }

}
