<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_prettymasthead
 *
 * @copyright   Copyright (C) 2022 TLWebdesign. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

?>
<div class="pretty-masthead">
	<?php
	foreach ($masthead as $m) :
	$imageObj = HTMLHelper::_('cleanImageURL', $m['image']);
		if ($imageObj->url != "" && $imageObj->attributes['width'] != 0 && $imageObj->attributes['height'] != 0) :
			$width = $imageObj->attributes['width'];
			$height = $imageObj->attributes['height'];
			$aspect = $height / $width;
		?>
			<div class="ratio d-flex justify-content-center align-items-center"
			     style="
				     --aspect-ratio: <?php echo round($aspect * 100); ?>%;
				     background:url('<?php echo $imageObj->url; ?>') center center / cover no-repeat;
				     "
			>
				<div class="content d-flex flex-column w-auto h-auto position-relative text-white text-center">
					<div class="h3 title">
						<span class="bg-primary py-2 px-5 mb-3"><?php echo $m['title']; ?></span>
					</div>
					<div class="description mt-2">
						<span class="bg-secondary p-2"><?php echo $m['description']; ?></span>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
</div>
