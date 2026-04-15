<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Application;

class CustomIblockList extends \CBitrixComponent
{
    public function executeComponent()
    {
        if (!Loader::includeModule("iblock")) {
            return;
        }

        $iblockId = (int)$this->arParams["IBLOCK_ID"];
        if ($iblockId <= 0) return;

        if ($this->startResultCache()) {

            $taggedCache = Application::getInstance()->getTaggedCache();
            $taggedCache->registerTag('iblock_id_' . $iblockId);

            $this->arResult["ITEMS"] = [];

            $dbItems = ElementTable::getList([
                'select' => [
                    'ID',
                    'NAME',
                    'DETAIL_TEXT',
                    'IBLOCK_SECTION_NAME' => 'IBLOCK_SECTION.NAME'
                ],
                'filter' => [
                    '=IBLOCK_ID' => $iblockId,
                    '=ACTIVE' => 'Y'
                ],
                'order' => ['SORT' => 'ASC']
            ]);

            $this->arResult["ITEMS"] = $dbItems->fetchAll();

            $this->includeComponentTemplate();
        }
    }
}
