<?php

namespace My\Module\Blogroll\Site\Helper;

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

        $validatedUrls = [];
        foreach ($responses as $url => $response) {

            if (!$response) {
                $validatedUrls[] = '❌ ' . $url;
            } else if ($this->isRss($response)) {
                $validatedUrls[] = '✔ ' . $url;
            } else {
                $rssUrl = $this->retrieveRssUrl($response, $url);
                $validatedUrls[] = empty($rssUrl) ? '❌ ' . $url : '✔ ' . $rssUrl;
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
        $rssUrl = '';
        try {
            $doc = new \DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($input);
            foreach ($doc->getElementsByTagName("link") as $linkNode) {
                if ($linkNode->getAttribute('type') == 'application/rss+xml') {
                    $href = $linkNode->getAttribute('href');
                    $rssUrl = str_starts_with($href, 'http') ? $href : $baseUrl . $href;
                    break;
                }
            }
            libxml_use_internal_errors(false);
        } catch (\Exception) {
            // We swallow this.
        }
        return $rssUrl;
    }
}