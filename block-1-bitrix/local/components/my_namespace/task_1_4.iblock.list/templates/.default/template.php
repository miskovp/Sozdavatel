<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */
?>

<div class="custom-list">
    <?php if (!empty($arResult["ITEMS"])): ?>
        <?php foreach ($arResult["ITEMS"] as $arItem): ?>
            <div class="item" style="margin-bottom: 20px; border-bottom: 1px solid #ccc;">
                <h3><?= htmlspecialcharsbx($arItem["NAME"]) ?></h3>
                <p><strong>Раздел:</strong> <?= htmlspecialcharsbx($arItem["IBLOCK_SECTION_NAME"] ?: 'Без раздела') ?></p>
                <div>
                    <strong>Описание:</strong><br>
                    <?= $arItem["DETAIL_TEXT"] ?: 'Описание отсутствует' ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Элементы не найдены.</p>
    <?php endif; ?>
</div>