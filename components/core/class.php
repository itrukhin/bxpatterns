<?php
declare(strict_types=1);
namespace App\Components;

use App\BxKint;

class CoreComponent extends \CBitrixComponent {

    public function executeComponent()
    {
        $this->includeComponentTemplate();

        if(class_exists('\App\BxKint'))
            BxKint::info($this->arResult);
        return $this->arResult;
    }
}