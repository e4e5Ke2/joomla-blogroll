<?php

namespace Joomla\Module\Blogroll\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\Module\Blogroll\Site\Helper\FeedHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class Dispatcher extends AbstractModuleDispatcher
{

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $data['rssrtl'] = $data['params']->get('rssrtl', 0);
        $feedHelper = new FeedHelper();
        $data['feeds'] = $feedHelper->getFeedInformation($data['params']);
        return $data;
    }

}
