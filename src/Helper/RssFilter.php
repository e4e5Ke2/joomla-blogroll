<?php

namespace My\Module\Blogroll\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFilterInterface;
use Joomla\Registry\Registry;

class RssFilter implements FormFilterInterface
{

    public function filter(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        $rssUrls = [];
        foreach (preg_split("/\r\n|\n|\r/", $value) as $url) {
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
        } while ($running > 0 && (time() - $curlExecStart) <= 10);

        $responses = [];
        for ($i = 0; $i < $urlCount; $i++) {
            $response = curl_multi_getcontent($curl_arr[$i]);
            $responses[$rssUrls[$i]] = $response;
        }

        $results = [];
        foreach ($responses as $url => $result) {

            if (!$result) {
                $results[] = '❌ ' . $url;
            } else if (str_starts_with($result, '<?xml') || str_starts_with($result, '<rss')) {
                $results[] = '✔ ' . $url;
            } else {
                $doc = new \DOMDocument();
                libxml_use_internal_errors(true);
                $doc->loadHTML($result);
                $rssUrl = '';
                foreach ($doc->getElementsByTagName("link") as $linkNode) {
                    if ($linkNode->getAttribute('type') == 'application/rss+xml') {
                        $href = $linkNode->getAttribute('href');
                        $rssUrl = str_starts_with($href, 'http') ? $href : $url . $href;
                        break;
                    }
                }
                $results[] = empty($rssUrl) ? '❌ ' . $url : '✔ ' . $rssUrl;
                libxml_use_internal_errors(false);
            }
        }

        return join("\n", array: $results);
    }
}