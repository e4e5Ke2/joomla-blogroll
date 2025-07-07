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

        // $feedHelper = $this->getHelperFactory()->getHelper('FeedHelper');
        $feeds = [];

        $nodes = $data['urls'];
        $master = curl_multi_init();
        $node_count = count($nodes);
        // $node_count = 2;
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
        // echo $results[0];

        $reader = new \XMLReader();
        for ($x = 0; $x < count($nodes); $x++) {
            // for ($x = 0; $x < 0; $x++) {

            // $feeds[$x] = $feedHelper->getFeedInformation($data['params'], $data['urls'][$x]);
            $feed = new MyFeed();
            $feed->feedUri = $nodes[$x];
            // $reader->open($data['urls'][$x]);

            // TODO: suppress errors? , null, LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_NOWARNING)
            // $reader->XML($results[$x]);
            $reader->XML($results[$x], null, LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_NOWARNING);

            $firstEntryFound = false;
            while ($reader->read()) {
                if ($reader->nodeType == \XMLReader::ELEMENT) {
                    // TODO: values should only be set once.
                    $tag = $reader->name;
                    if ($tag == 'title' && !$firstEntryFound) {
                        $feed->title = $reader->readInnerXml();
                    } else if ($tag == 'pubDate' && $firstEntryFound) {
                        $feed->pubDate = $reader->readInnerXml();
                    } else if ($tag == 'published' && $firstEntryFound) {
                        $feed->pubDate = $reader->readInnerXml();
                    } else if (in_array($tag, ['item', 'entry'], true) && !$firstEntryFound) {
                        $firstEntryFound = true;
                    } else if ($tag == 'title' && $firstEntryFound) {
                        $feed->firstEntry = $reader->readInnerXml();
                    }
                    // Some blogs have description and content:encoded tags, so this only works if the order is correct
                    else if (in_array($tag, ['description', 'summary', 'content', 'content:encoded'], true) && $firstEntryFound) {
                        $feed->description = $reader->readString();
                    } else if ($tag == 'link' && $firstEntryFound) {
                        if ($reader->getAttribute('rel') === 'alternate') {
                            $feed->uri = $reader->getAttribute('href');
                        } else if ($reader->getAttribute('href') === null) {
                            $feed->uri = $reader->readString();
                        }
                    } else if ($tag == 'media:thumbnail' && $firstEntryFound) {
                        $feed->imgUri = $reader->getAttribute('url');
                    }
                    // Stop when we encounter the 2nd item in the feed.
                    else if (in_array($tag, ['item', 'entry'], true) && $firstEntryFound) {
                        break;
                    }
                }

            }

            if (!$feed->imgUri) {
                $feed->imgUri = $this->get_image_path($feed->description);
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
}
