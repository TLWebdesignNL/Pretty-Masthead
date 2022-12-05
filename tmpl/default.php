<?php

/**
 * @package  Joomla.Site
 * @subpackage  mod_prettymasthead
 *
 * @copyright   Copyright (C) 2022 TLWebdesign. All rights reserved.
 * @license  GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

?>
<div class="pretty-masthead">
    <?php
    if ($masthead['image']->url != "") :
        $width = $masthead['image']->attributes['width'];
        $height = $masthead['image']->attributes['height'];
        $aspectRatio = 0.25;

        if ($width != 0 && $height != 0) {
            $aspectRatio = $height / $width;
        }
        ?>
        <div class="ratio d-flex justify-content-<?php echo $masthead['position']; ?> align-items-center p-3 p-sm-5 <?php echo $mainDivClass; ?>"
             style="
                     --aspect-ratio: <?php echo round($aspectRatio * 100); ?>%;
                     background:url('<?php echo $masthead['image']->url; ?>') center center / cover no-repeat;
             <?php echo ($minHeight && $minHeight != 0) ? "min-height: ".$minHeight."px;" : ""; ?>
             <?php echo ($maxHeight && $maxHeight != 0) ? "max-height: ".$maxHeight."px;" : ""; ?>
                     "
        >
            <div
                    class="content d-flex flex-column align-items-<?php echo $masthead['position']; ?>
                    w-auto h-auto position-relative text-white text-center"
            >
                <<?php echo $masthead['titletag']; ?> class="title">
                    <span
                            class="<?php echo $masthead['titleclass']; ?>"
                            style="-webkit-box-decoration-break:clone;box-decoration-break:clone;"
                    >
                        <?php echo $masthead['title']; ?>
                    </span>
                </<?php echo $masthead['titletag']; ?>>
                <?php if (!empty($masthead['description'])) : ?>
                    <div class="description mt-sm-2">
                        <span
                                class="<?php echo $masthead['descriptionclass']; ?>"
                                style="-webkit-box-decoration-break:clone;box-decoration-break:clone;"
                        >
                            <?php echo $masthead['description']; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
