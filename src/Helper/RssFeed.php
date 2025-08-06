<?php

namespace My\Module\Blogroll\Site\Helper;

use DateTime;

class RssFeed
{
    public string $feedTitle = '';
    public string $itemTitle = '';
    public string $feedUri = '';
    public string $itemUri = '';
    public string $description = '';
    public DateTime $pubDate;
    public string $timeDifference = '';
    public string $imgUri = '';
    public string $author = '';
    public string $authorDateLabel = '';

    protected $optionalKeys = ['imgUri', 'author', 'authorDateLabel', 'timeDifference'];

    public function is_data_complete()
    {
         foreach ($this as $key => $value) {
            if (in_array($key, $this->optionalKeys)) continue;
            if (!$value || empty($value)) return false; 
         }
         return true;
    }
}