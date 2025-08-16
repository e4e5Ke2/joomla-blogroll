<?php
/**
 * @package     Blogroll
 * @author      Alexander Bach (e4e5Ke2 on github)
 * @copyright   2025 - now
 * @license     GNU General Public License version 2 or later
 */

namespace My\Module\Blogroll\Site\Helper;

\defined('_JEXEC') or die;

interface Translations {
    public function get(string $id) : string;
    public function getPlural(string $id, int $number) : string;
}