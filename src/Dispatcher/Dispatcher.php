<?php

namespace My\Module\Blogroll\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use My\Module\Blogroll\Site\Helper\FeedHelper;
use My\Module\Blogroll\Site\Helper\JoomlaTranslations;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class Dispatcher extends AbstractModuleDispatcher
{

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $feedHelper = new FeedHelper();
        $data['feeds'] = $feedHelper->getFeedInformation($data['params'], new JoomlaTranslations());
        return $data;
    }

}
