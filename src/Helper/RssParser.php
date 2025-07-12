<?php

namespace Joomla\Module\Blogroll\Site\Helper;

use DateTimeImmutable;
use Joomla\CMS\Language\Text;

class RssParser
{

    protected static $itemTags = ['entry', 'item'];
    protected static $pubDateTags = ['pubDate', 'published'];
    protected static $descriptionTags = ['content:encoded', 'description', 'summary', 'content'];
    protected static $thumbnailTags = ['media:thumbnail', 'enclosure'];

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

        $feed->pubDate = new DateTimeImmutable($this->first_tag_match($itemNode, RssParser::$pubDateTags));
        // TODO - keep as well and make configurable
        // $pubDateFormatted = $feed->pubDate->format('d.m.Y');
        $feed->timeDifference = $this->get_time_difference($feed->pubDate);

        // Order is important here. Some blogs have content encoded and description. We want content encoded if available.
        $feed->description = $this->first_tag_match($itemNode, RssParser::$descriptionTags);

        // If the item doesnt have an explicit thumbnail tag, we extract the first picture we find in the description.
        $thumbnailUrl = $this->first_tag_match($itemNode, RssParser::$thumbnailTags, 'url');
        $feed->imgUri = $thumbnailUrl ?: $this->get_image_path($feed->description);
        $feed->feedUri = $this->get_uri_from_links($feedNode->link);
        $feed->itemUri = $this->get_uri_from_links($itemNode->link);

        return $feed;
    }

    protected function get_uri_from_links($links)
    {
        foreach ($links as $link) {
            if (!isset($link['href']) || $link['rel'] == 'alternate') {
                return $link['href'] ?: $link;
            }
        }
        return '';
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
                return $src;
            }
        }
        return '';
    }

    protected function first_tag_match($node, array $tagArray, $attribute = '')
    {
        foreach ($tagArray as $tag) {
            $parts = explode(':', $tag);
            $result = count($parts) == 2 ? $node->children($parts[0], TRUE)->{$parts[1]} : $node->$tag;

            if ($result)
                return empty($attribute) ? $result : $result->attributes()->$attribute;
        }
        return '';
    }

    protected function get_time_difference($pubDate)
    {
        $now = new DateTimeImmutable();
        $interval = $now->diff($pubDate);

        $timeDiff = match (true) {
            $interval->y > 0 => Text::plural('MOD_BLOGROLL_N_YEARS_AGO', $interval->y),
            $interval->m > 0 => Text::plural('MOD_BLOGROLL_N_MONTHS_AGO', $interval->m),
            $interval->d > 0 => Text::plural('MOD_BLOGROLL_N_DAYS_AGO', $interval->d),
            $interval->h > 0 => Text::plural('MOD_BLOGROLL_N_HOURS_AGO', $interval->h),
            default => Text::plural('MOD_BLOGROLL_N_MINS_AGO', $interval->i)
        };

        return $timeDiff;
    }
}
