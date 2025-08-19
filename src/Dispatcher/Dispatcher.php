<?php
/**
 * @package     Blogroll
 * @author      Alexander Bach (e4e5Ke2 on github)
 * @copyright   2025 - now
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Module\Blogroll\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\Module\Blogroll\Site\Helper\FeedHelper;
use Joomla\Module\Blogroll\Site\Helper\JoomlaTranslations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;


class Dispatcher extends AbstractModuleDispatcher
{

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $feedHelper = new FeedHelper();
        $translations = new JoomlaTranslations();
        $data['feeds'] = $feedHelper->getFeeds($data['params'], $translations);
        $data['translations'] = $translations;
        Text::script('MOD_BLOGROLL_SHOW_MORE');
        Text::script('MOD_BLOGROLL_SHOW_LESS');

        $app = Factory::getApplication();
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('mod_blogroll');
        $wa->useScript('mod_blogroll.show-all');
        $wa->useStyle('mod_blogroll.blogroll_style');

        return $data;
    }

}
