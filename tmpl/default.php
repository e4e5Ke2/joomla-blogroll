<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

Text::script('MOD_BLOGROLL_SHOW_MORE');
Text::script('MOD_BLOGROLL_SHOW_LESS');

$wa = $app->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('mod_blogroll');
$wa->useScript('mod_blogroll.show-all');
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

	.mod_blogroll_img {
		width: 50px;
		height: 50px;
		margin-top: 0px;
		object-fit: cover;
	}

	.mod_blogroll_showall_container {
		display: none;
	}

	.mod_blogroll_showall_button {
		display: inline;
		margin-top: 0px;
		padding: 0;
		border: 0;
		font: inherit;
		text-decoration: none;
		cursor: pointer;
		background: transparent;
		color: currentColor;
		float: right;
	}

	.mod_blogroll_showall_button:hover,
	.mod_blogroll_showall_button:active {
		color: blue;
	}
</style>

<!-- Feed items -->
<?php
$feedCount = count($feeds);
$itemDisplayCount = min($feedCount, $params->get('rssitems', PHP_INT_MAX));
for ($i = 0; $i < $itemDisplayCount; $i++) {
	$feed = $feeds[$i];
	item_layout($feed, $params);
}
?>

<!-- Collapsible items -->
<div class="mod_blogroll_showall_container">
	<?php
	for ($i = $itemDisplayCount; $i < $feedCount; $i++) {
		$feed = $feeds[$i];
		item_layout($feed, $params, true);
	}
	?>
</div>

<!-- Button to collapse/expand -->
<?php if ($feedCount > $itemDisplayCount) { ?>
	<button class="mod_blogroll_showall_button" type="button"><?= Text::_('MOD_BLOGROLL_SHOW_MORE'); ?></button>
<?php } ?>

<?php
function item_layout($feed, $params, $hideImg = false)
{ ?>
	<div style="width:100%;overflow:auto;">

		<!-- Feed image -->
		<?php if ($params->get('rssimage', 1)): ?>
			<div style="float:left;width:60px;">
				<a href="<?= htmlspecialchars($feed->itemUri, ENT_COMPAT, 'UTF-8'); ?>" target="_blank" rel="noopener">
					<?php
					if ($feed->imgUri) {
						echo '<img class="mod_blogroll_img" ' . ($hideImg ? 'data-' : '') . 'src=' . $feed->imgUri . '>';
					} ?>
				</a>
			</div>
		<?php endif; ?>

		<div style="margin-left:60px">

			<!-- Feed title -->
			<?php if ($feed->feedTitle !== null && $params->get('rsstitle', 1)): ?>
				<h6>
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
			<p style="color:#606060;font-size:14px;margin-top:5px"><?= ($feed->author ? ('von ' . $feed->author . ' | ') : '') . $feed->timeDifference; ?></p>

		</div>
		<hr>
	</div>
<?php } ?>