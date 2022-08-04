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
			$aspectRatio = $height / $width;
			?>
            <div class="ratio d-flex justify-content-<?php echo $m['position']; ?> align-items-center p-3 p-sm-5"
                 style="
                         --aspect-ratio: <?php echo round($aspectRatio * 100); ?>%;
                         background:url('<?php echo $imageObj->url; ?>') center center / cover no-repeat;
                         min-height: 200px;
                         "
            >
                <div class="content d-flex flex-column align-items-<?php echo $m['position']; ?> w-auto h-auto position-relative text-white text-center">
                    <<?php echo $m['titletag']; ?> class="title">
                        <span class="<?php echo $m['titleclass']; ?>"><?php echo $m['title']; ?></span>
                    </<?php echo $m['titletag']; ?>>
                    <?php if (!empty($m['description'])) : ?>
                        <div class="description mt-sm-2">
                            <span class="<?php echo $m['descriptionclass']; ?>"><?php echo $m['description']; ?></span>
                        </div>
                    <?php endif; // if description is not empty ?>
                </div>
            </div>
		<?php endif; ?>
	<?php endforeach; ?>
</div>