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

	if ($feed == false || $feed[0] == null) {
		continue;
	}
	?>

	<div style="direction: <?= $rssrtl ? 'rtl' : 'ltr'; ?>;width:100%;overflow:auto;"
		class="text-<?= $rssrtl ? 'right' : 'left'; ?> feed">

		<!-- Feed image -->
		<?php if ($params->get('rssimage', 1)): ?>
			<div style="float:left;width:60px;">
				<?php
				$src = get_image_path($feed);
				if ($src) {
					echo '<img class="cropped" src=' . $src . '>';
				} ?>
			</div>
		<?php endif; ?>

		<div style="margin-left:60px">

			<!-- Feed title -->
			<?php if ($feed->title !== null && $params->get('rsstitle', 1)): ?>
				<h6 class="<?= $direction; ?>">
					<a href="<?= get_feed_base_url($urls[$i]) ?>
							" target="_blank" rel="noopener">
						<?= $feed->title; ?></a>
				</h6>
			<?php endif; ?>

			<!-- Show first item title -->
			<?php if (!empty($feed)) { ?>
				<?php
				$firstItem = $feed[0];
				$uri = $firstItem->uri || !$firstItem->isPermaLink ? trim($firstItem->uri) : trim($firstItem->guid);
				$uri = !$uri || stripos($uri, 'http') !== 0 ? $rssurl : $uri;
				$text = $firstItem->content !== '' ? trim($firstItem->content) : '';

				$pubDate = new DateTimeImmutable($firstItem->publishedDate);
				$pubDateFormatted = $pubDate->format('d.m.Y');
				?>
				<?php if (!empty($uri)): ?>
					<span class="feed-link">
						<a href="<?= htmlspecialchars($uri, ENT_COMPAT, 'UTF-8'); ?>" target="_blank" rel="noopener">
							<?= trim($firstItem->title); ?></a></span>
					-
					<!--  Feed date -->
					<span style="color:#404040"><?= trim($pubDateFormatted); ?></span>
				<?php else: ?>
					<span class="feed-link"><?= trim($firstItem->title); ?></span>
				<?php endif; ?>
			<?php } ?>
		</div>

		<!-- Divider between items -->
		<?php if ($i < $itemDisplayCount - 1) { ?>
			<hr>
		<?php } ?>
	</div>
<?php }

function get_image_path($feed)
{
	$description = $feed[0]->content;
	if (!empty($description)) {
		$doc = new DOMDocument();
		libxml_use_internal_errors(true);
		$success = $doc->loadHTML($description);
		libxml_use_internal_errors(false);

		if ($success) {
			$xpath = new DOMXPath($doc);
			$src = $xpath->evaluate("string(//img/@src)");

			return $src;
		}
	}
	return null;
}

function get_feed_base_url($rssUrl)
{
	// This seems overly complicated.. but I can't find the feed link anywhere.
	$parsed_url = parse_url($rssUrl);
	$base_url = $parsed_url['scheme'] . "://" . $parsed_url['host'] . "/";
	return htmlspecialchars($base_url, ENT_COMPAT, 'UTF-8');
}
