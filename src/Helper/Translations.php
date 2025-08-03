<?php

namespace My\Module\Blogroll\Site\Helper;

interface Translations {
    public function get(string $id) : string;
    public function getPlural(string $id, int $number) : string;
}