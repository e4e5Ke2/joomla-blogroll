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
        return !empty($this->feedTitle) && !empty($this->itemTitle) && !empty($this->description) && isset($this->pubDate) && !empty($this->itemUri) && !empty($this->feedUri) && !empty($this->timeDifference);
    }
}