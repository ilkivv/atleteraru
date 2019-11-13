<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

CJSCore::Init(array("fx"));

?>


<div class="products-filter">
<form name="filter" action="./" method="GET" class="j-filter-form">
<input type="hidden" name="sort" value="<?php echo $_GET['sort']?>"/>
<input type="hidden" name="order" value="<?php echo $_GET['order']?>"/>
<input type="hidden" name="brand" value="">
</form>

                    <div class="products-sort">
                        <strong>Сортировать по:</strong>
                        
                      <?php /*?>  <a class="<?php 
                            if ($_GET['sort'] == 'popular'){
                                $sort = '';
                                if ($_GET['order'] == 'asc') {
                                    $sort = 'desc';
                                } else {
                                    $sort = 'asc';
                                }
                            } else {
                                $sort = 'asc';
                            }
                        ?>" href="?sort=popular&order=<?php echo $sort;?>"><span>популярности</span></a> <em>|</em>
                        */?>
                        <a class="<?php 
                            if ($_GET['sort'] == 'name'){
                                $sort = '';
                                if ($_GET['order'] == 'asc') {
                                    $sort = 'desc';
                                } else {
                                    $sort = 'asc';
                                }
                            }else {
                                $sort = 'asc';
                            }
                        ?>" href="?sort=name&order=<?php echo $sort;?>"><span>наименованию</span></a> <em>|</em>
                        
                        <a class="<?php 
                            if ($_GET['sort'] == 'price'){
                                $sort = '';
                                if ($_GET['order'] == 'asc') {
                                    $sort = 'desc';
                                } else {
                                    $sort = 'asc';
                                }
                            }else {
                                $sort = 'asc';
                            }
                        ?>" href="?sort=price&order=<?php echo $sort;?>"><span>цене</span></a> <!--em>|</em-->
                        <?php /*?>
                        <a class="<?php 
                            if ($_GET['sort'] == 'novelty'){
                                $sort = '';
                                if ($_GET['order'] == 'asc') {
                                $sort = 'desc';
                                } else {
                                $sort = 'asc';
                                }
                            }else {
                                $sort = 'asc';
                            }
                        ?>"  href="?sort=novelty&order=<?php echo $sort;?>"><span>новинкам</span></a>
                        */?>
                    </div>

                    <div class="products-cat">

                        <label>Бренд</label>
                        <?php 
                        $selected_brand = array('ID'=>0,'VALUE'=>'Все');
                        CModule::IncludeModule("iblock");

                        $property_enums = CIBlockPropertyEnum::GetList(Array("VALUE"=>"ASC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>45, "CODE"=>"CML2_MANUFACTURER"));

                        $brandFilter = CIBlockElement::GetList(Array("ID"=>"ASC"), Array("IBLOCK_ID"=>45, "ACTIVE"=>"Y",'SECTION_ID' => $arParams['SECTION_ID']), array('PROPERTY_CML2_MANUFACTURER'),array("nPageSize"=>50000), array());
                        while($brand = $brandFilter->Fetch()){
                            if ($brand['PROPERTY_CML2_MANUFACTURER_ENUM_ID'] !== NULL){
                                $arBrandsProductCurrentCategory[] = $brand['PROPERTY_CML2_MANUFACTURER_ENUM_ID'];
                            }
                        }

                        while($enum_fields = $property_enums->GetNext())
                        {
                            if (in_array($enum_fields['ID'], $arBrandsProductCurrentCategory)){
                                $brands[] = $enum_fields;
                            }


                            if ($enum_fields['ID'] == $_GET['brand']) {
                            $selected_brand = $enum_fields;
                            }
                        }
                        ?>
                        <div class="choose-taste j-choose-taste j-brand-selector">
                            <div data-value="<?=$selected_brand['ID']?>" class="choose-taste-link j-choose-taste-link"><span><?=$selected_brand['VALUE']?></span><em></em></div>
                            <div class="choose-taste-list-container">
                                <div class="choose-taste-list j-choose-taste-list">
                                    <div class="item" data-value="0">Все</div><?
                                    foreach ($brands as $lk => $enum_fields)
                                    {
                                        //var_dump($enum_fields);
                                        /*if (CModule::IncludeModule('catalog')) {
                                            $catalogProduct = CCatalogProduct::GetList();
                                            foreach ($products = $catalogProduct->Fetch() as $product){
                                                //var_dump($products);die;
                                            }

                                        }*/
                                        //var_dump($enum_fields);
                                        //if ($enum_fields['ID'] != $selected_brand['ID']) {
                                        echo '<div class="item" data-value="'.$enum_fields["ID"].'">'.$enum_fields["VALUE"].'</div>';
                                       // }
                                    }
                                ?></div>
                            </div>
                        </div>

                    </div>

                </div>