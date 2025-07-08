<?php

namespace Joomla\Module\Feed\Site\Helper;

use DateTimeImmutable;

class RssParser
{

    protected static $itemTags = ['entry', 'item'];

    public function parse($feedUri, $xmlString)
    {
        $feed = new RssFeed();
        $feed->feedUri = $this->get_base_url($feedUri);

        $simpleXML = new \SimpleXMLElement($xmlString);
        $feedNode = match ($simpleXML->getName()) {
            'rss' => $simpleXML->channel,
            'feed' => $simpleXML,
            default => null
        };

        if (!$feedNode)
            return;

        $itemNode = $this->first_tag_match($feedNode, RssParser::$itemTags);     

        $feed->feedTitle = $feedNode->title;
        $feed->itemTitle = $itemNode->title;
        $feed->pubDate = new DateTimeImmutable($this->first_tag_match($itemNode, ['pubDate', 'published']));

        // Order is important here. Some blogs have content encoded and description. We want content encoded if available.
        $contentEncoded = $itemNode->children('content', TRUE)->encoded;
        $feed->description = $contentEncoded ?: $this->first_tag_match($itemNode, ['description', 'summary', 'content']);

        $thumbnail = $itemNode->children('media', TRUE)->thumbnail;
        $feed->imgUri = $thumbnail ? $thumbnail->attributes()->url : $this->get_image_path($feed->description);

        foreach ($itemNode->link as $link) {
            if (!isset($link['href']) || $link['rel'] == 'alternate') {
                $feed->itemUri = $link['href'] ?: $link;
                break;
            }
        }

        return $feed;
    }

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

    protected function first_tag_match($node, $tagArray)
    {
        foreach ($tagArray as $tag) {
            if (isset($node->$tag)) {
                return $node->$tag;
            }
        }
        return '';
    }

    protected function get_base_url($url)
    {
        $parsed_url = parse_url($url);
        $base_url = $parsed_url['scheme'] . "://" . $parsed_url['host'] . "/";
        return htmlspecialchars($base_url, ENT_COMPAT, 'UTF-8');
    }
}