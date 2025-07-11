<?php

namespace Joomla\Module\Feed\Site\Helper;

use DateTimeImmutable;

class RssFeed
{
    public string $feedTitle = '';
    public string $itemTitle = '';
    public string $feedUri = '';
    public string $itemUri = '';
    public string $description = '';
    public DateTimeImmutable $pubDate;
    public string $timeDifference = '';
    public string $imgUri = '';

    // imgUri is optional
    public function is_data_complete()
    {
         foreach ($this as $key => $value) {
            if ($key == 'imgUri') continue;
            if (!$value || empty($value)) return false; 
         }
         return true;
    }
}