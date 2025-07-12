<?php
defined('_JEXEC') or die;

$wa = $app->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('mod_feed');
$wa->useScript('mod_feed.show-all');

$lang = $app->getLanguage();
$myrtl = $params->get('rssrtl', 0);
$direction = ' ';

$isRtl = $lang->isRtl();

if ($isRtl && $myrtl == 0) {
	$direction = ' redirect-rtl';
} elseif ($isRtl && $myrtl == 1) {
	$direction = ' redirect-ltr';
} elseif ($isRtl && $myrtl == 2) {
	$direction = ' redirect-rtl';
} elseif ($myrtl == 0) {
	$direction = ' redirect-ltr';
} elseif ($myrtl == 1) {
	$direction = ' redirect-ltr';
} elseif ($myrtl == 2) {
	$direction = ' redirect-rtl';
}
?>

<style>
	.blogroll:link,
	.blogroll:visited {
		color: currentColor;
		text-decoration: none;
	}

	.blogroll:hover,
	.blogroll:active {
		color: blue;
		text-decoration: underline;
	}

	.cropped {
		width: 50px;
		height: 50px;
		margin-top: 0px;
		object-fit: cover;
	}
</style>

<?php
$itemDisplayCount = min(count($feeds), $params->get('rssitems', PHP_INT_MAX));
for ($i = 0; $i < $itemDisplayCount; $i++) {
	$feed = $feeds[$i];
	if (!empty($feed) && is_string($feed)) {
		echo $feed;
		continue;
	}

	if ($feed == false) {
		continue;
	}
	?>

	<div style="direction: <?= $rssrtl ? 'rtl' : 'ltr'; ?>;width:100%;overflow:auto;"
		class="text-<?= $rssrtl ? 'right' : 'left'; ?> feed">

		<!-- Feed image -->
		<?php if ($params->get('rssimage', 1)): ?>
			<div style="float:left;width:60px;">
				<a href="<?= htmlspecialchars($feed->itemUri, ENT_COMPAT, 'UTF-8'); ?>" target="_blank" rel="noopener">
					<?php
					if ($feed->imgUri) {
						echo '<img class="cropped" src=' . $feed->imgUri . '>';
					} ?>
				</a>
			</div>
		<?php endif; ?>

		<div style="margin-left:60px">

			<!-- Feed title -->
			<?php if ($feed->feedTitle !== null && $params->get('rsstitle', 1)): ?>
				<h6 class="<?= $direction; ?>">
					<a class="blogroll" href="<?= $feed->feedUri ?>
							" target="_blank" rel="noopener">
						<?= $feed->feedTitle; ?></a>
				</h6>
			<?php endif; ?>

			<!-- Show first item title -->
			<a class="blogroll" href="<?= htmlspecialchars($feed->itemUri, ENT_COMPAT, 'UTF-8'); ?>" target="_blank"
				rel="noopener">
				<?= $feed->itemTitle; ?></a>

			<!--  Feed date -->
			<span style="color:#404040"> - <?= $feed->timeDifference; ?></span>
		</div>

		<!-- Divider between items -->
		<?php if ($i < $itemDisplayCount - 1) { ?>
			<hr>
		<?php } ?>
	</div>
<?php } ?>

<button type="button">"PRESS ME"</button>

