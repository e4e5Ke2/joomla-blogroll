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


    public function is_data_complete()
    {
        return !empty($this->title) && !empty($this->firstEntry) && !empty($this->description) && !empty($this->pubDate) && !empty($this->uri);
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
        // $node_count = 10;
        $curl_arr = array();
        $master = curl_multi_init();

        for ($i = 0; $i < $node_count; $i++) {
            $url = $nodes[$i];
            $curl_arr[$i] = curl_init($url);
            curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
            curl_multi_add_handle($master, $curl_arr[$i]);
        }

        do {
            curl_multi_exec($master, $running);
        } while ($running > 0);

        $results = [];
        for ($i = 0; $i < $node_count; $i++) {
            $results[$i] = curl_multi_getcontent($curl_arr[$i]);
        }
        // echo $results[2];

        $reader = new \XMLReader();
        for ($x = 1; $x < count($data['urls']); $x++) {
        // for ($x = 0; $x < 10; $x++) {

            // $feeds[$x] = $feedHelper->getFeedInformation($data['params'], $data['urls'][$x]);
            $feed = new MyFeed();
            // $reader->open($data['urls'][$x]);

            // TODO: suppress errors? , null, LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_NOWARNING)
            // $reader->XML($results[$x]);
            $reader->XML($results[$x], null, LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_NOWARNING);
            
            $firstEntryFound = false;
            while ($reader->read()) {
                if ($reader->nodeType == \XMLReader::ELEMENT) {
                    // TODO: values should only be set once.
                    // TODO: blogspot doesnt have images in the description but in a separate thumbnail node
                    $tag = $reader->name;
                    if ($tag == 'title' && !$firstEntryFound) {
                        $feed->title = $reader->readInnerXml();
                    } else if ($tag == 'pubDate' && $firstEntryFound) {
                        $feed->pubDate = $reader->readInnerXml();
                    } else if ($tag == 'published' && $firstEntryFound) {
                        $feed->pubDate = $reader->readInnerXml();
                    } else if ($tag == 'item') {
                        $firstEntryFound = true;
                    } else if ($tag == 'entry') {
                        $firstEntryFound = true;
                    } else if ($tag == 'title' && $firstEntryFound) {
                        $feed->firstEntry = $reader->readInnerXml();
                    } else if (in_array($tag, ['description', 'summary', 'content'], true) && $firstEntryFound) {
                        $feed->description = $reader->readString();
                    } else if ($tag == 'link' && $firstEntryFound) {
                        if ($reader->getAttribute('rel') === 'alternate') {
                            $feed->uri = $reader->getAttribute('href');
                        } else if ($reader->getAttribute('href') === null) {
                            $feed->uri = $reader->readString();
                        }
                    }
                }

                if ($feed->is_data_complete()) {
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
}
