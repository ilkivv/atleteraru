<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>



<? if (!($arResult["SKIP_FIRST_STEP"] == "Y" && $arResult["SKIP_SECOND_STEP"] == "Y" && $arResult["SKIP_THIRD_STEP"] == "Y")) {
    ?>
    <input class="hidden-input" id='BackBtn' type="submit" name="backButton" value="&lt;&lt; <?
    echo GetMessage("SALE_BACK_BUTTON") ?>">
    <div class="breadcrumbs">
        <a href="#" onclick="$('#BackBtn').trigger('click');return false;">Назад</a>
    </div>
    <?
}
?>

<div class="ordering-step" style="display:none;">Оформление заказа: шаг 3 из 3</div>

<h1>Способ оплаты</h1>

<div class="ordering-info">
    <span><strong>Ваш заказ:</strong> <?php echo count($arResult['BASKET_ITEMS']) . ' ' . declension(count($arResult['BASKET_ITEMS']), array('товар', "товара", "товаров")); ?>
        на сумму: <?= round($arResult['ORDER_DISCOUNT_PRICE'], 2) ?></span>
    <span><strong>Доставка:</strong> <?= $arResult['DELIVERY']['NAME'] ?> <?= $arResult['DELIVERY']['ID'] ?></span>
    <em><strong>Итого:</strong> <?= round($arResult['ORDER_DISCOUNT_PRICE'] + $arResult['DELIVERY_PRICE'], 2) ?> р.</em>
</div>
<table class="ordering-table">
    <?
    foreach ($arResult["PAY_SYSTEM"] as $arPaySystem) {
        if ($arResult['DELIVERY']['ID'] == 2 ||$arResult['DELIVERY']['ID'] == 3) {
            ?>
            <tr>
                <td>
                    <div class="radio">
                        <input type="radio" id="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>" name="PAY_SYSTEM_ID"
                               value="<?= $arPaySystem["ID"] ?>"<? if ($arPaySystem["CHECKED"] == "Y") echo " checked"; ?>>
                        <label for="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>">
                            <span><?= $arPaySystem["PSA_NAME"] ?></span>
                        </label>
                    </div>
                    <?
                    if (strlen($arPaySystem["DESCRIPTION"]) > 0)
                        echo "<br /><span style='font-size:14px;margin-left:30px;'>" . $arPaySystem["DESCRIPTION"] . '</span>';
                    ?>
                </td>
            </tr>
            <?
        } else {
            if ($arPaySystem["ID"] != 1) {
                ?>
                <tr>
                    <td>
                        <div class="radio">
                            <input type="radio" id="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>" name="PAY_SYSTEM_ID"
                                   value="<?= $arPaySystem["ID"] ?>"<?
                            if ($arPaySystem["CHECKED"] == "Y") echo " checked"; ?>>
                            <label for="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>">
                                <span><?= $arPaySystem["PSA_NAME"] ?></span>
                            </label>
                        </div>
                        <?
                        if (strlen($arPaySystem["DESCRIPTION"]) > 0)
                            echo "<br /><span style='font-size:14px;margin-left:30px;'>" . $arPaySystem["DESCRIPTION"] . '</span>';
                        ?>
                    </td>
                </tr>
                <?
            }
        }

    }
    ?>

</table>

<div class="ordering-button">
    <a href="#" class="screw-button" onclick="$('#submitReg').trigger('click');return false;"><span>Далее</span></a>
    <input type="submit" class="hidden-input" value="<?= GetMessage("SALE_CONTINUE") ?> &gt;&gt;" id="submitReg"
           name="contButton"/>
</div>

<?php return; ?>


<table border="0" cellspacing="0" cellpadding="5" width="100%">
    <tr>
        <td valign="top" width="60%" align="right">
            <input type="submit" name="contButton" value="<?= GetMessage("SALE_CONTINUE") ?> &gt;&gt;">
        </td>
        <td valign="top" width="5%" rowspan="3">&nbsp;</td>
        <td valign="top" width="35%" rowspan="3">
            <? echo GetMessage("STOF_PRIVATE_NOTES") ?>
        </td>
    </tr>
    <tr>
        <td valign="top" width="60%">
            <b><? echo GetMessage("STOF_PAYMENT_WAY") ?></b><br/><br/>
            <?
            if ($arResult["PAY_FROM_ACCOUNT"] == "Y") {
                ?>
                <!--<input type="hidden" name="PAY_CURRENT_ACCOUNT" value="Y">-->
                <input type="checkbox" name="PAY_CURRENT_ACCOUNT" id="PAY_CURRENT_ACCOUNT" value="Y"<?
                if ($arResult["PAY_CURRENT_ACCOUNT"] != "N") echo " checked"; ?>> <label for="PAY_CURRENT_ACCOUNT"><b><?
                        echo GetMessage("STOF_PAY_FROM_ACCOUNT") ?></b></label><br/>
                <?= GetMessage("STOF_ACCOUNT_HINT1") ?> <b><?= $arResult["CURRENT_BUDGET_FORMATED"] ?></b> <?
                echo GetMessage("STOF_ACCOUNT_HINT2") ?>
                <br/><br/>
                <?
            }
            ?>
            <?
            if (count($arResult["PAY_SYSTEM"]) > 0) {
                ?>
                <table class="sale_order_full_table"
                       id="sale_order_full_table" <? if ($arResult["PAY_FROM_ACCOUNT"] == "Y") echo "style=\"display:none_\""; ?>>
                    <tr>
                        <td colspan="2">

                            <?
                            echo GetMessage("STOF_PAYMENT_HINT") ?><br/><br/>

                        </td>
                    </tr>
                    <?
                    foreach ($arResult["PAY_SYSTEM"] as $arPaySystem) {
                        ?>
                        <tr>
                            <td valign="top" width="0%">
                                <input type="radio" id="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>" name="PAY_SYSTEM_ID"
                                       value="<?= $arPaySystem["ID"] ?>"<?
                                if ($arPaySystem["CHECKED"] == "Y") echo " checked"; ?>>
                            </td>
                            <td valign="top" width="100%">
                                <label for="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>">
                                    <b><?= $arPaySystem["PSA_NAME"] ?></b><br/>
                                    <?
                                    if (strlen($arPaySystem["DESCRIPTION"]) > 0)
                                        echo $arPaySystem["DESCRIPTION"] . "<br />";
                                    ?>
                                </label>
                            </td>
                        </tr>
                        <?
                    }
                    ?>
                </table>
                <?
            }
            if ($arResult["HaveTaxExempts"] == "Y") {
                ?>
                <br/>
                <input type="checkbox" name="TAX_EXEMPT" value="Y" checked> <b><?
                    echo GetMessage("STOF_TAX_EX") ?></b><br/>
                <?
                echo GetMessage("STOF_TAX_EX_PROMT") ?>
                <br/><br/>
                <?
            }
            ?>
        </td>
    </tr>
    <tr>
        <td valign="top" width="60%" align="right">

        </td>
    </tr>
</table>
