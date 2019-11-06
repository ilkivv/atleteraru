<style>
	.ipol_header {
		font-size: 16px;
		cursor: pointer;
		display:block;
		color:#2E569C;
	}

	.ipol_inst {
		display:none; 
		margin-left:10px;
		margin-top:10px;
	}
</style>

<?
	$arErrCities = sdekHelper::getErrCities();
	if(!$arErrCities)
		echo GetMessage("IPOLSDEK_NO_CITIES_FOUND");
	else{
?>
		<script>
			function IPOLSDEK_hiddenShow(wat){
				var hndl = $('#'+wat);
				if(hndl.hasClass("IPOLSDEK_hiddenTable"))
					hndl.removeClass("IPOLSDEK_hiddenTable");
				else
					hndl.addClass("IPOLSDEK_hiddenTable");
			}
		</script>
		<style>
			.IPOLSDEK_hiddenTable{
				display:none;
			}
		</style>
		<tr><td style="color:#555;" colspan="2">
			<?sdekOption::placeFAQ('CITYHINT')?>
		</td></tr>
		
		<? // ÍÀÉÄÅÍÍÛÅ
		$allCities = sqlSdekCity::select(array("REGION"=>"ASC"));
		?>
		<tr class="heading"><td colspan="2" valign="top" align="center" onclick='IPOLSDEK_hiddenShow("IPOLSDEK_errCit_success")' style='cursor:pointer;text-decoration:underline'><?=GetMessage("IPOLSDEK_HDR_success")?> (<?=$allCities->nSelectedCount?>)</td></tr>
		<tr><td colspan="2">
				<table class="adm-list-table IPOLSDEK_hiddenTable" id='IPOLSDEK_errCit_success'>
					<thead>
							<tr class="adm-list-table-header">
								<td class="adm-list-table-cell" style='width: 80px;'><?=GetMessage("IPOLSDEK_HDR_BITRIXID")?></td>
								<td class="adm-list-table-cell" style='width: 80px;'><?=GetMessage("IPOLSDEK_HDR_SDEKID")?></td>
								<td class="adm-list-table-cell"><?=GetMessage("IPOLSDEK_HDR_REGION")?></td>
								<td class="adm-list-table-cell"><?=GetMessage("IPOLSDEK_HDR_CITY")?></td>
							</tr>
					</thead>
					<tbody>
				<?
					$allCities = sqlSdekCity::select(array("REGION"=>"ASC"));
					while($element=$allCities->Fetch()){?>
						<tr class="adm-list-table-row">
							<td class="adm-list-table-cell"><?=$element['BITRIX_ID']?></td>
							<td class="adm-list-table-cell"><?=$element['SDEK_ID']?></td>
							<td class="adm-list-table-cell"><?=$element['REGION']?></td>
							<td class="adm-list-table-cell"><?=$element['NAME']?></td>
					<?}?>
					</tbody>
				</table>
		</td></tr>

		<? // ÊÎÍÔËÈÊÒÓÞÙÈÅ
		if(count($arErrCities['many']) > 0){?>
		<tr class="heading"><td colspan="2" valign="top" align="center" onclick='IPOLSDEK_hiddenShow("IPOLSDEK_errCit_many")' style='cursor:pointer;text-decoration:underline'><?=GetMessage("IPOLSDEK_HDR_many")?> (<?=count($arErrCities['many'])?>)</td></tr>
		<tr><td colspan="2">
			<table class="adm-list-table IPOLSDEK_hiddenTable" id='IPOLSDEK_errCit_many'>
				<thead>
						<tr class="adm-list-table-header">
							<td class="adm-list-table-cell"><?=GetMessage("IPOLSDEK_HDR_BITRIXNM")?></td>
							<td class="adm-list-table-cell"><?=GetMessage("IPOLSDEK_HDR_SDEKNM")?></td>
							<td class="adm-list-table-cell"><?=GetMessage("IPOLSDEK_HDR_VARIANTS")?></td>
						</tr>
				</thead>
				<tbody>
					<?foreach($arErrCities['many'] as $bitrixId => $arCities){
						$bitrix = CSaleLocation::GetList(array(),array("ID"=>$bitrixId,"REGION_LID"=>LANGUAGE_ID,"CITY_LID"=>LANGUAGE_ID))->Fetch();
						if(!$bitrix)
							$bitrix = CSaleLocation::GetList(array(),array("ID"=>$bitrixId))->Fetch();
						$location = $bitrix['REGION_NAME'].", ".$bitrix['CITY_NAME']." (".$bitrixId.")";
					?>
					<tr class="adm-list-table-row"><td class="adm-list-table-cell"><?=$location?></td><td class="adm-list-table-cell"><?=$arCities['takenLbl']?></td><td class="adm-list-table-cell">
						<?
							foreach($arCities['sdekCity'] as $sdekId => $descr)
								echo $descr['region'].", ".$descr['name']."<br>";
						?>
					</td></tr>
					<?}?>
				</tbody>
			</table>
		</td></tr>
		<?}?>

		<? // ÍÅ ÍÀÉÄÅÍÍÛÅ
		if(count($arErrCities['notFound']) > 0){?>
		<tr class="heading" onclick='IPOLSDEK_hiddenShow("IPOLSDEK_errCit_errs")' style='cursor:pointer;text-decoration:underline'><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_notFound")?> (<?=count($arErrCities['notFound'])?>)</td></tr>
		<tr><td colspan="2">
				<table class="adm-list-table IPOLSDEK_hiddenTable" id='IPOLSDEK_errCit_errs'>
				<thead>
						<tr class="adm-list-table-header">
							<td class="adm-list-table-cell"><?=GetMessage("IPOLSDEK_HDR_SDEKID")?></td>
							<td class="adm-list-table-cell"><?=GetMessage("IPOLSDEK_HDR_REGION")?></td>
							<td class="adm-list-table-cell"><?=GetMessage("IPOLSDEK_HDR_CITY")?></td>
						</tr>
				</thead>
				<tbody>
					<?foreach($arErrCities['notFound'] as $arCity){?>
					<tr class="adm-list-table-row">
						<td class="adm-list-table-cell"><?=$arCity['sdekId']?></td>
						<td class="adm-list-table-cell"><?=$arCity['region']?></td>
						<td class="adm-list-table-cell"><?=$arCity['name']?></td>
					</tr>
					<?}?>
				</tbody>
			</table>
		</td></tr>
		<?}?>
<?}?>