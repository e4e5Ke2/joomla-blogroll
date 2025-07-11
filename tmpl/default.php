<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_feed
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filter\OutputFilter;

// Check if feed URL has been set
if (empty($feeds)) {
	echo '<div>' . Text::_('MOD_FEED_ERR_NO_URL') . '</div>';

	return;
}

$lang = $app->getLanguage();
$app->getLanguage()->load('mod_feed', JPATH_BASE . '/modules/mod_feed');
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
<?php }

