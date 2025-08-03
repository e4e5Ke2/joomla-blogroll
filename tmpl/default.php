<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

Text::script('MOD_BLOGROLL_SHOW_MORE');
Text::script('MOD_BLOGROLL_SHOW_LESS');

$wa = $app->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('mod_blogroll');
$wa->useScript('mod_blogroll.show-all');
$wa->useStyle('mod_blogroll.blogroll_style');
?>

<!-- Feed items -->
<?php
$feedCount = count($feeds);
$limitItems = $params['rssitems_limit'] ?? 0;
$itemDisplayCount = $limitItems ? $params['rssitems_limit_count'] : $feedCount;
for ($i = 0; $i < $feedCount; $i++) {

	if ($i == $itemDisplayCount) {
		echo '<div class="mod_blogroll_showall_container">';
	}

	$feed = $feeds[$i];
	$hideImg = $i >= $itemDisplayCount;
	?>

	<div>
		<div style="display:flex">

			<!-- Feed image -->
			<?php if ($params['rssimage'] ?? 1): ?>
				<div style="width:60px;flex-shrink:0">
					<a href="<?= htmlspecialchars($feed->itemUri, ENT_COMPAT, 'UTF-8'); ?>" target="_blank" rel="noopener">
						<?php
						if ($feed->imgUri) {
							echo '<img class="mod_blogroll_img" ' . ($hideImg ? 'data-' : '') . 'src=' . $feed->imgUri . '>';
						} ?>
					</a>
				</div>
			<?php endif; ?>

			<div style="flex-grow:1;overflow:auto;">

				<!-- Feed title -->
				<h6>
					<a class="mod_blogroll" href="<?= $feed->feedUri ?>
							" target="_blank" rel="noopener">
						<?= $feed->feedTitle; ?></a>
				</h6>

				<!-- Show first item title -->
				<a class="mod_blogroll" href="<?= htmlspecialchars($feed->itemUri, ENT_COMPAT, 'UTF-8'); ?>" target="_blank"
					rel="noopener">
					<?= $feed->itemTitle; ?></a>

				<!--  Feed author / date -->
				<?php
				if ($feed->authorDateLabel) { ?>
					<p class="mod_blogroll_author_date_label">
						<?= $feed->authorDateLabel; ?>
					</p>
				<?php } ?>
			</div>
		</div>
		<hr>
	</div>
<?php } ?>

<!-- Button to collapse/expand -->
<?php if ($feedCount > $itemDisplayCount) { ?>
	</div>
	<button class="mod_blogroll_showall_button" type="button"><?= Text::_('MOD_BLOGROLL_SHOW_MORE'); ?></button>
<?php } ?>