<?php

namespace Joomla\Module\Feed\Site\Helper;

use DateTimeImmutable;

class RssParser
{

    protected static $itemTags = ['entry', 'item'];

    public function parse($xmlString)
    {
        $feed = new RssFeed();
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
        // $contentEncoded = $itemNode->children('content', TRUE)->encoded;
        // $feed->description = $contentEncoded ?: $this->first_tag_match($itemNode, ['description', 'summary', 'content']);
        $feed->description = $this->first_tag_match($itemNode, ['content:encoded', 'description', 'summary', 'content']);

        // <enclosure url=.. is another format I found..
        $thumbnail = $this->first_tag_match($itemNode, ['media:thumbnail', 'enclosure'], 'url');
        $feed->imgUri = $thumbnail ?: $this->get_image_path($feed->description);

        foreach ($feedNode->link as $link) {
            if (!isset($link['href']) || $link['rel'] == 'alternate') {
                $feed->feedUri = $link['href'] ?: $link;
                break;
            }
        }

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

    protected function first_tag_match($node, $tagArray, $attribute = '')
    {
        foreach ($tagArray as $tag) {

            if (str_contains($tag, ':')) {
                $parts = explode(':', $tag);
                if (count($parts) !== 2)
                    continue;

                $result = $node->children($parts[0], TRUE)->{$parts[1]};
            } else {
                $result = $node->$tag;
            }

            if ($result)
                return empty($attribute) ? $result : $result->attributes()->$attribute;
        }
        return '';
    }
}
