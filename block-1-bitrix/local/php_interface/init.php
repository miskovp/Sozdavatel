<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Mail\Event;

EventManager::getInstance()->addEventHandler(
    "iblock",
    "OnAfterIBlockElementUpdate",
    ["FaqMailHandler", "onAfterUpdate"]
);

class FaqMailHandler
{
    public static function onAfterUpdate(&$arFields)
    {
        if (!$arFields["RESULT"]) {
            return;
        }

        $faqIblockId = 5; // Укажи актуальный ID
        if ($arFields["IBLOCK_ID"] != $faqIblockId) {
            return;
        }

        $detailText = "";
        if (isset($arFields["DETAIL_TEXT"])) {
            $detailText = is_array($arFields["DETAIL_TEXT"])
                ? $arFields["DETAIL_TEXT"]["TEXT"]
                : $arFields["DETAIL_TEXT"];
        }

        // Очищаем от HTML-сущностей и пробелов для точной проверки на пустоту
        if (empty(trim(strip_tags((string)$detailText)))) {
            return;
        }

        if (!Loader::includeModule("iblock")) {
            return;
        }

        $dbElement = \CIBlockElement::GetList(
            [],
            ["ID" => $arFields["ID"], "IBLOCK_ID" => $faqIblockId],
            false,
            false,
            ["ID", "IBLOCK_ID", "PREVIEW_TEXT", "PROPERTY_EMAIL", "PROPERTY_SENT_ANSWER"]
        );

        if ($element = $dbElement->Fetch()) {
            $userEmail = trim((string)$element["PROPERTY_EMAIL_VALUE"]);
            $isSent = !empty($element["PROPERTY_SENT_ANSWER_VALUE"]);

            // Проверяем, что email не пустой, валидный, и письмо еще не отправлялось
            if (!empty($userEmail) && check_email($userEmail) && !$isSent) {

                // Получаем ID сайта, к которому привязан инфоблок (важно для многосайтовости)
                $siteId = 's1';
                $rsSites = \CIBlock::GetSite($faqIblockId);
                if ($arSite = $rsSites->Fetch()) {
                    $siteId = $arSite['LID'];
                }

                $sendResult = Event::send([
                    "EVENT_NAME" => "FAQ_ANSWER_MAIL",
                    "LID" => $siteId,
                    "C_FIELDS" => [
                        "EMAIL_TO" => $userEmail,
                        "QUESTION" => $element["PREVIEW_TEXT"],
                        "ANSWER" => $detailText,
                    ],
                ]);

                if ($sendResult->isSuccess()) {
                    \CIBlockElement::SetPropertyValuesEx(
                        $arFields["ID"],
                        $faqIblockId,
                        ['SENT_ANSWER' => 'Y']
                    );
                }
            }
        }
    }
}
