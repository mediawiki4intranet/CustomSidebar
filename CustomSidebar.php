<?php

/**
 * MediaWiki Custom Sidebar extension
 *
 * Copyright © 2012+ Vitaliy Filippov
 * http://wiki.4intra.net/CustomSidebar
 *
 **
 * Allows to add blocks with custom wikitext to Mediawiki Sidebar
 * USAGE: create MediaWiki:CustomSidebar page with following content:
 *     Pagename|Title
 * Title will be the title of block, and its content will be taken
 * from MediaWiki:Pagename. Title can be a message name, then it will be
 * translated.
 **
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

if (!defined('MEDIAWIKI'))
{
    ?>
<p>This is the CustomSidebar extension. To enable it, put</p>
<pre>require_once("$IP/extensions/CustomSidebar/CustomSidebar.php");</pre>
<p>at the bottom of your LocalSettings.php.</p>
    <?php
    exit(1);
}

$wgHooks['SkinBuildSidebar'][] = 'addCustomSidebar';
$wgExtensionCredits['other'][] = array(
	'name'        => 'CustomSidebar',
	'description' => 'Allows to add custom wikitext to sidebar',
	'version'     => '1.12+',
	'author'      => 'Vitaliy Filippov',
	'url'         => 'http://wiki.4intra.net/CustomSidebar',
);

function addCustomSidebar($skin, &$sidebar)
{
	global $wgParser, $wgCustomSidebarEnable, $wgUser;

	$parserOptions = new ParserOptions();

	if (wfEmptyMsg('CustomSidebar'))
		return true;

	$text = wfMsg('CustomSidebar');
	$text = trim(preg_replace(array('/<!--(.*)-->/s'), array(''), $text));
	$blocks = explode("\n", $text);

	foreach ($blocks as $block)
	{
		$line = explode('|', $block);

		// silently ignore lines that have more than one '|':
		if (count($line) > 2 || !$line)
			continue;

		// first, we need a title object
		$title = Title::newFromText($line[0], NS_MEDIAWIKI);
		if (!$title)
			continue;

		// return false if MediaWiki:CustomSidebar does not exist
		if (!$title->exists())
		{
			if ($title->quickUserCan('edit'))
				$html = $skin->makeKnownLinkObj($title, 'edit', 'action=edit');
			else
			{
				unset($sidebar[$blockTitle]);
				continue;
			}
		}
		else
		{
			// get article and content
			$article = new Article($title);
			$content = $article->getContent();

			// parse wikitext
			$parserOutput = $wgParser->parse($content, $title, $parserOptions);
			$html = $parserOutput->getText();
		}

		// make a sidebar block
		$sidebar[$line[1]] = $html;
	}

	return true;
}
