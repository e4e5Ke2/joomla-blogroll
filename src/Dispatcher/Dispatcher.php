<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_feed
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Feed\Site\Dispatcher;

use DateTime;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class MyFeed
{
    public string $title = '';
    public string $firstEntry = '';
    public string $description = '';
    public string $pubDate = '';
    public string $uri = '';
    public string $feedUri = '';
    public string $imgUri = '';

    // imgUri is optional
    public function is_data_complete()
    {
        return !empty($this->title) && !empty($this->firstEntry) && !empty($this->description) && !empty($this->pubDate) && !empty($this->uri) && !empty($this->feedUri);
    }
}

/**
 * Dispatcher class for mod_feed
 *
 * @since  5.1.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    // TODO: refine to search for low res images?
    protected function get_image_path($description)
    {
        if (!empty($description)) {
            $doc = new \DOMDocument();
            libxml_use_internal_errors(true);
            $success = $doc->loadHTML($description);
            libxml_use_internal_errors(false);

            if ($success) {
                $xpath = new \DOMXPath($doc);
                $src = $xpath->evaluate("string(//img/@src)");

                // echo 'src: ' . $src;
                return $src;
            }
        }
        return '';
    }


    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.1.0
     */
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $urlListString = $data['params']->get('rssurl_list', '');
        $data['urls'] = array_map(fn($url): string => filter_var($url, FILTER_SANITIZE_URL), preg_split("/\r\n|\n|\r/", $urlListString));

        $data['rssrtl'] = $data['params']->get('rssrtl', 0);

        $feeds = [];

        $nodes = $data['urls'];
        $master = curl_multi_init();
        $node_count = count($nodes);
        $curl_arr = [];
        $master = curl_multi_init();

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

        $reader = new \XMLReader();
        for ($x = 0; $x < count($nodes); $x++) {

            $feed = new MyFeed();
            $feed->feedUri = $nodes[$x];

            $simpleXML = new \SimpleXMLElement($results[$x]);
            $rssFeed = match ($simpleXML->getName()) {
                'rss' => $simpleXML->channel,
                'feed' => $simpleXML,
            };

            $item = $this->first_tag_match($rssFeed, ['entry', 'item']);

            $feed->title = $rssFeed->title;
            $feed->firstEntry = $item->title;
            $feed->pubDate = $this->first_tag_match($item, ['pubDate', 'published']);

            // Order is important here. Some blogs have content encoded and description. We want content encoded if available.
            $contentEncoded = $item->children('content', TRUE)->encoded;
            $feed->description = $contentEncoded ?: $this->first_tag_match($item, ['description', 'summary', 'content']);

            $thumbnail = $item->children('media', TRUE)->thumbnail;
            $feed->imgUri = $thumbnail ? $thumbnail->attributes()->url : $this->get_image_path($feed->description);

            foreach ($item->link as $link) {    
                if (!isset($link['href']) || $link['rel'] == 'alternate') {
                    $feed->uri = $link['href'] ?: $link;
                    break;
                }
            }

            if ($feed->is_data_complete()) {
                $feeds[] = $feed;
            }
        }
        $reader->close();

        // TODO: not sure if this is the right date to use
        // usort($feeds, fn($a, $b) => new DateTimeImmutable($a[0]->publishedDate) < $b->updatedDate);

        $data['feeds'] = $feeds;

        return $data;
    }

    protected function first_tag_match($node, $tagArray)
    {
        foreach ($tagArray as $tag) {
            if (isset($node->$tag)) {
                return $node->$tag;
            }
        }
        return '';
    }
}
