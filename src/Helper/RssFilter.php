<?php
/**
 * @package     Blogroll
 * @author      Alexander Bach (e4e5Ke2 on github)
 * @copyright   2025 - now
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Module\Blogroll\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFilterInterface;
use Joomla\Registry\Registry;

class RssFilter implements FormFilterInterface
{

    public function filter(\SimpleXMLElement $element, $urlListString, $group = null, Registry $input = null, Form $form = null)
    {
        $feedHelper = new FeedHelper();
        $responses = $feedHelper->multicurl($urlListString, 5);

        $formatSuccess = fn($url) => '✅ ' . $url;
        $formatFailure = fn($url) => '❌ ' . $url;

        $validatedUrls = [];
        foreach ($responses as $url => $response) {

            if (!$response) {
                $validatedUrls[] = $formatFailure($url);
            } else if ($this->isRss($response)) {
                $validatedUrls[] = $formatSuccess($url);
            } else {
                // Try to extract rss link from website
                $rssUrl = $this->retrieveRssUrl($response, $url);
                $validatedUrls[] = empty($rssUrl) ? $formatFailure($url) : $formatSuccess($rssUrl);
            }
        }

        return join("\n", array: $validatedUrls);
    }

    private function isRss($input): bool
    {
        return str_starts_with($input, '<?xml') || str_starts_with($input, '<rss');
    }

    private function retrieveRssUrl($input, $baseUrl): string
    {
        try {
            $doc = new \DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($input);
            foreach ($doc->getElementsByTagName("link") as $linkNode) {
                if ($linkNode->getAttribute('type') == 'application/rss+xml') {
                    $href = $linkNode->getAttribute('href');
                    $rssUrl = str_starts_with($href, 'http') ? $href : $baseUrl . $href;
                    return $rssUrl;
                }
            }
            libxml_use_internal_errors(false);
        } catch (\Exception) {
            // We swallow this.
        }
        return '';
    }
}