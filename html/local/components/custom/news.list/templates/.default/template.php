<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

if (empty($arResult["ITEMS"])) {
    return;
}
?>

<div class="custom-news-list">
    <?php if ($arResult["GROUPED"]): ?>
        <?php foreach ($arResult["ITEMS"] as $iblockId => $arIBlockData): ?>
            <div class="news-iblock-group" data-iblock-id="<?= $iblockId ?>">
                <h2 class="news-iblock-title">
                    <?= htmlspecialcharsEx($arIBlockData["IBLOCK_INFO"]["NAME"]) ?>
                </h2>
                
                <?php if (!empty($arIBlockData["IBLOCK_INFO"]["DESCRIPTION"])): ?>
                    <div class="news-iblock-description">
                        <?= $arIBlockData["IBLOCK_INFO"]["DESCRIPTION"] ?>
                    </div>
                <?php endif; ?>

                <div class="news-items">
                    <?php foreach ($arIBlockData["ITEMS"] as $arItem): ?>
                        <?php
                        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
                        ?>
                        <div class="news-item" id="<?= $this->GetEditAreaId($arItem['ID']); ?>">
                            <?php if (!empty($arItem["PREVIEW_PICTURE"])): ?>
                                <div class="news-item-image">
                                    <img src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>" 
                                         alt="<?= htmlspecialcharsEx($arItem["NAME"]) ?>"
                                         title="<?= htmlspecialcharsEx($arItem["NAME"]) ?>">
                                </div>
                            <?php endif; ?>

                            <div class="news-item-content">
                                <h3 class="news-item-title">
                                    <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>">
                                        <?= htmlspecialcharsEx($arItem["NAME"]) ?>
                                    </a>
                                </h3>

                                <?php if (!empty($arItem["DISPLAY_ACTIVE_FROM"])): ?>
                                    <div class="news-item-date">
                                        <?= $arItem["DISPLAY_ACTIVE_FROM"] ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($arItem["PREVIEW_TEXT"])): ?>
                                    <div class="news-item-preview">
                                        <?= $arItem["PREVIEW_TEXT"] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>

        <div class="news-items">
            <?php foreach ($arResult["ITEMS"] as $arItem): ?>
                <?php
                $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
                ?>
                <div class="news-item" id="<?= $this->GetEditAreaId($arItem['ID']); ?>">
                    <?php if (!empty($arItem["PREVIEW_PICTURE"])): ?>
                        <div class="news-item-image">
                            <img src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>" 
                                 alt="<?= htmlspecialcharsEx($arItem["NAME"]) ?>"
                                 title="<?= htmlspecialcharsEx($arItem["NAME"]) ?>">
                        </div>
                    <?php endif; ?>

                    <div class="news-item-content">
                        <h3 class="news-item-title">
                            <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>">
                                <?= htmlspecialcharsEx($arItem["NAME"]) ?>
                            </a>
                        </h3>

                        <?php if (!empty($arItem["DISPLAY_ACTIVE_FROM"])): ?>
                            <div class="news-item-date">
                                <?= $arItem["DISPLAY_ACTIVE_FROM"] ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($arItem["PREVIEW_TEXT"])): ?>
                            <div class="news-item-preview">
                                <?= $arItem["PREVIEW_TEXT"] ?>
                            </div>
                        <?php endif; ?>

                        <div class="news-item-iblock">
                            Источник: <?= htmlspecialcharsEx($arResult["IBLOCKS"][$arItem["IBLOCK_ID"]]["NAME"]) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>