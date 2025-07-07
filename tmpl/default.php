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
if (empty($urls)) {
	echo '<div>' . Text::_('MOD_FEED_ERR_NO_URL') . '</div>';

	return;
}

$lang = $app->getLanguage();
$myrtl = $params->get('rssrtl', 0);
$direction = ' ';

$isRtl = $lang->isRtl();

if ($isRtl && $myrtl == 0) {
	$direction = ' redirect-rtl';
} elseif ($isRtl && $myrtl == 1) {
	// Feed description
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

<!-- TODO: these override all links on the website, not -->
<style>
	a:link {
		color: black;
		text-decoration: none;
	}

	a:visited {
		color: black;
		text-decoration: none;
	}

	a:hover {
		color: blue;
		text-decoration: underline;
	}

	a:active {
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
				<?php
				if ($feed->imgUri) {
					echo '<img class="cropped" src=' . $feed->imgUri . '>';
				} ?>
			</div>
		<?php endif; ?>

		<div style="margin-left:60px">

			<!-- Feed title -->
			<?php if ($feed->title !== null && $params->get('rsstitle', 1)): ?>
				<h6 class="<?= $direction; ?>">
					<a href="<?= get_feed_base_url($feed->feedUri) ?>
							" target="_blank" rel="noopener">
						<?= $feed->title; ?></a>
				</h6>
			<?php endif; ?>

			<!-- Show first item title -->
			<?php
			// $uri = $feed->uri || !$feed->isPermaLink ? trim($feed->uri) : trim($feed->guid);
			// $uri = !$uri || stripos($uri, 'http') !== 0 ? $rssurl : $uri;
		
			$pubDate = new DateTimeImmutable($feed->pubDate);
			$pubDateFormatted = $pubDate->format('d.m.Y');
			?>
			<span class="feed-link">
				<a href="<?= htmlspecialchars($feed->uri, ENT_COMPAT, 'UTF-8'); ?>" target="_blank" rel="noopener">
					<?= trim($feed->firstEntry); ?></a></span>
			-
			<!--  Feed date -->
			<span style="color:#404040"><?= trim($pubDateFormatted); ?></span>
		</div>

		<!-- Divider between items -->
		<?php if ($i < $itemDisplayCount - 1) { ?>
			<hr>
		<?php } ?>
	</div>
<?php }

// TODO: move out of here
function get_feed_base_url($rssUrl)
{
	// This seems overly complicated.. but I can't find the feed link anywhere.
	$parsed_url = parse_url($rssUrl);
	$base_url = $parsed_url['scheme'] . "://" . $parsed_url['host'] . "/";
	return htmlspecialchars($base_url, ENT_COMPAT, 'UTF-8');
}
