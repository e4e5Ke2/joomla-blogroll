<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_feed
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Feed\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_feed
 *
 * @since  5.1.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.1.0
     */
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $urlListString = $data['params']->get('rssurl_list', '');
        $data['urls'] = array_map(fn($url): string => filter_var($url, FILTER_SANITIZE_URL), preg_split("/\r\n|\n|\r/", $urlListString));

        $data['rssrtl'] = $data['params']->get('rssrtl', 0);

        $feedHelper = $this->getHelperFactory()->getHelper('FeedHelper');
        $feeds = [];

        for ($x = 0; $x < count($data['urls']); $x++) {
            $feeds[$x] = $feedHelper->getFeedInformation($data['params'], $data['urls'][$x]);
        }


        // $xml = simplexml_load_string($myXMLData);
        // if ($xml !== false) {
        // }

        // TODO: not sure if this is the right date to use
        // usort($feeds, fn($a, $b) => new DateTimeImmutable($a[0]->publishedDate) < $b->updatedDate);

        $data['feeds'] = $feeds;

        return $data;
    }
}
