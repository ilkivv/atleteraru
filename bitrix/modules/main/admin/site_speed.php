<?
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/site_speed.php");

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Analytics;

Loc::loadMessages(__FILE__);

if (!$USER->CanDoOperation("view_other_settings") || !Analytics\SiteSpeed::isLicenseAccepted())
{
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

CJSCore::Init(array("site_speed", "date"));
$APPLICATION->SetAdditionalCSS("/bitrix/panel/main/site_speed.css");
$APPLICATION->SetTitle(Loc::getMessage("MAIN_SITE_SPEED_TITLE"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");?>

<div class="site-speed-page">
	<?
		$currentHost = preg_replace("/:(80|443)$/", "", $_SERVER["HTTP_HOST"]);
		$currentHost = htmlspecialcharsbx($currentHost);
	?>
	<div class="site-speed-domains">
		<span class="site-speed-domains-label"><?=Loc::getMessage("MAIN_SITE_SPEED_DOMAINS_LABEL")?></span>
		<select class="site-speed-domains-select" id="site-speed-domains">
			<option value="<?=$currentHost?>"><?=$currentHost?></option>
		</select>
		<span class="site-speed-loading" id="site-speed-loading"></span>
		<span class="site-speed-error" id="site-speed-error"></span>
	</div>

	<div class="site-speed-analytics" id="site-speed-analytics">

		<div class="site-speed-indicator-block" id="site-speed-indicator-block">
			<div class="site-speed-index">
				<span class="site-speed-index-label"><?=Loc::getMessage("MAIN_SITE_SPEED_TITLE")?>:</span>
				<span class="site-speed-index-value" id="site-speed-index"></span>
			</div>

			<div class="site-speed-indicator" id="indicator"></div>

			<div class="site-speed-stat">
				<div class="site-speed-stat-item">
					<span class="site-speed-stat-label"><?=Loc::getMessage("MAIN_SITE_SPEED_HITS_LABEL")?>:</span>
					<span class="site-speed-stat-value" id="site-speed-hits"></span>
				</div>
				<div class="site-speed-stat-item">
					<span class="site-speed-stat-label"><?=Loc::getMessage("MAIN_SITE_SPEED_PERIOD_LABEL")?>:</span>
					<span class="site-speed-stat-value" id="site-speed-date"></span>
				</div>
				<div class="site-speed-stat-item">
					<span class="site-speed-stat-label"><?=Loc::getMessage("MAIN_SITE_SPEED_COMPOSITE_HITS")?>:</span>
					<span class="site-speed-stat-value" id="site-speed-composite"></span>
				</div>
			</div>

			<div class="site-speed-explanation">
				<b><?=Loc::getMessage("MAIN_SITE_SPEED_TITLE")?></b> &mdash; <?=Loc::getMessage("MAIN_SITE_SPEED_TITLE_DESC")?>
			</div>

			<div class="site-speed-perf" id="site-speed-perf">
				<?
				if (\Bitrix\Main\ModuleManager::isModuleInstalled("perfmon")):
					$mark = (double)COption::GetOptionString("perfmon", "mark_php_page_rate", "");
				?>
					<a href="/bitrix/admin/perfmon_panel.php?lang=<?=LANGUAGE_ID?>" class="site-speed-perf-label"><?=Loc::getMessage("MAIN_SITE_SPEED_PERF")?></a>:<span class="site-speed-perf-value"><?if ($mark > 0):?><?=$mark?><?else:?><?=Loc::getMessage("MAIN_SITE_SPEED_PERF_NO_RES")?><?endif?></span>
				<?endif?>

				<?
					$compositeStatus = \CHTMLPagesCache::IsCompositeEnabled() ? Loc::getMessage("MAIN_SITE_SPEED_ENABLED") : Loc::getMessage("MAIN_SITE_SPEED_DISABLED");
				?>
				<a href="/bitrix/admin/composite.php?lang=<?=LANGUAGE_ID?>" class="site-speed-perf-label"><?=Loc::getMessage("MAIN_SITE_SPEED_COMPOSITE_SITE")?></a>:<span class="site-speed-perf-value"><?=$compositeStatus?></span>
				<?

				if (\Bitrix\Main\Loader::includeModule("bitrixcloud")):
					$cdnStatus = CBitrixCloudCDN::IsActive() ? Loc::getMessage("MAIN_SITE_SPEED_ENABLED") : Loc::getMessage("MAIN_SITE_SPEED_DISABLED");
				?>
				<a href="/bitrix/admin/bitrixcloud_cdn.php?lang=<?=LANGUAGE_ID?>" class="site-speed-perf-label"><?=Loc::getMessage("MAIN_SITE_SPEED_CDN")?></a>:<span class="site-speed-perf-value"><?=$cdnStatus?></span>
				<?endif?>
			</div>
		</div>

		<div class="site-speed-histogram-block" id="site-speed-histogram-block">
			<h1 class="adm-title"><?=Loc::getMessage("MAIN_SITE_SPEED_HISTO_TITLE")?></h1>
			<div class="site-speed-histogram" id="histogram"></div>
		</div>

		<div class="site-speed-graph-block" id="site-speed-graph-block">
			<h1 class="adm-title"><?=Loc::getMessage("MAIN_SITE_SPEED_GRAPH_TITLE")?></h1>
			<div class="site-speed-graph" id="graph"></div>
		</div>

		<?=BeginNote();?><?=Loc::getMessage("MAIN_SITE_SPEED_NOTES")?><?=EndNote();?>
	</div>

</div>

<script type="text/javascript">

(function() {
	"use strict";
	var siteSpeed = new BX.Main.SiteSpeed("<?=CUtil::JSEscape(Analytics\Counter::getPrivateKey())?>", "<?=CUtil::JSEscape(Analytics\Counter::getAccountId())?>");

	BX.ready(function() {
		BX.bind(BX("site-speed-domains"), "change", drawStatForCurrentHost);
		drawStatForCurrentHost();
	});

	function drawStat(host)
	{
		var lastHitsData = null;
		var histoLoaded = false;
		var graphLoaded = false;

		siteSpeed.getHistoData(
			host,
			function(data) {

				fillDomains(data.domains);

				if (data && data.result !== false)
				{
					var composite = BX.type.isNumber(data.compositeHits) ? data.compositeHits : 0;
					var hits = BX.type.isNumber(data.cnt) ? data.cnt : 0;
					var inverval = siteSpeed.getInverval(data["p50"]);

					BX("site-speed-index").innerHTML = inverval.title +
						" (" + BX.Main.SiteSpeed.formatMilliseconds(data["p50"]) +
						" <?=CUtil::JSEscape(Loc::getMessage("MAIN_SITE_SPEED_SECONDS"))?>)";
					BX("site-speed-hits").innerHTML = hits;
					BX("site-speed-composite").innerHTML = composite + " (" + (composite/hits * 100).toFixed(1)+ "%)";

					var startDate = BX.date.format("j F H:i", BX.date.getNewDate(data["firstHitTs"]));
					var endDate = BX.date.format("j F H:i", BX.date.getNewDate(data["lastHitTs"]));
					BX("site-speed-date").innerHTML = startDate + " - " + endDate;

					BX("site-speed-analytics").style.display = "block";
					BX("site-speed-loading").style.display = "none";

					siteSpeed.drawIndicator(data, "indicator");
					siteSpeed.drawHisto(data, "histogram");

					histoLoaded = true;

					drawGraph();
				}
				else
				{
					var error = "<?=CUtil::JSEscape(Loc::getMessage("MAIN_SITE_SPEED_DOMAIN_NOT_FOUND"))?>";
					if (BX.type.isArray(data.domains) && data.domains.length > 0)
					{
						error += " " + "<?=CUtil::JSEscape(Loc::getMessage("MAIN_SITE_SPEED_CHOOSE_DOMAIN"))?>"
					}

					showError(error);
				}
			},

			function() {
				showError("<?=CUtil::JSEscape(Loc::getMessage("MAIN_SITE_SPEED_CONNECTION_ERROR"))?>");
			}
		);

		siteSpeed.getLastHits(
			host,
			function(data) {
				lastHitsData = data;
				drawGraph();
			},
			function() {

			}
		);

		function drawGraph()
		{
			if (
				BX.type.isArray(lastHitsData) &&
				lastHitsData.length > 0 &&
				graphLoaded === false &&
				histoLoaded === true
			)
			{
				graphLoaded = true;
				BX("site-speed-graph-block").style.display = "block";
				siteSpeed.drawGraph(lastHitsData, "graph");
			}
		}
	}

	function showError(text)
	{
		BX("site-speed-loading").style.display = "none";
		BX("site-speed-error").innerHTML = text;
	}

	function drawStatForCurrentHost()
	{
		if (BX("site-speed-domains").selectedIndex !== 0)
		{
			BX("site-speed-perf").style.display = "none";
		}
		else
		{
			BX("site-speed-perf").style.display = "block";
		}

		BX("site-speed-analytics").style.display = "none";
		BX("site-speed-loading").style.cssText = "";
		BX("site-speed-graph-block").style.cssText = "";
		BX("site-speed-error").innerHTML = "";

		drawStat(BX("site-speed-domains").value);
	}

	function fillDomains(domains)
	{
		if (!BX.type.isArray(domains))
		{
			return;
		}

		var select = BX("site-speed-domains");
		if (!select || select.domainsLoaded === true)
		{
			return;
		}

		for (var i = 0; i < domains.length; i++)
		{
			select.options[i+1] = new Option(domains[i], domains[i]);
		}

		select.domainsLoaded = true;
	}

})();

</script>


<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>