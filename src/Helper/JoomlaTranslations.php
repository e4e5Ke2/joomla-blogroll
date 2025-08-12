<?php

namespace My\Module\Blogroll\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

class JoomlaTranslations implements Translations  {

    public function get(string $id): string {
        return Text::_($id);
    }

     public function getPlural(string $id, int $number): string {
        return Text::plural($id, $number);
    }
}
