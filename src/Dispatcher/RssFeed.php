<?php

namespace Joomla\Module\Feed\Site\Dispatcher;

use DateTimeImmutable;

class RssFeed
{
    public string $feedTitle = '';
    public string $firstEntry = '';
    public string $description = '';
    public DateTimeImmutable $pubDate;
    public string $uri = '';
    public string $feedUri = '';
    public string $imgUri = '';

    // imgUri is optional
    public function is_data_complete()
    {
        return !empty($this->feedTitle) && !empty($this->firstEntry) && !empty($this->description) && isset($this->pubDate) && !empty($this->uri) && !empty($this->feedUri);
    }
}