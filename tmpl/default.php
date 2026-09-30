<?php

/**
 * @package     TLWebdesign.Module
 * @subpackage  mod_prettymasthead
 *
 * @copyright   Copyright (C) 2022 TLWebdesign. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

// Escape a value for HTML text and attribute context
$e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

// The helper only returns h1-h6 here
$titleTag = $masthead['titletag'];

$minHeight = (int) $minHeight;
$maxHeight = (int) $maxHeight;
?>
<div class="pretty-masthead">
    <?php
    if (\is_object($masthead['image']) && $masthead['image']->url !== '') :
    $width = (int) $masthead['image']->attributes['width'];
    $height = (int) $masthead['image']->attributes['height'];
    $aspectRatio = 0.25;

    if ($width > 0 && $height > 0) {
        $aspectRatio = $height / $width;
    }

    // Percent-encode the characters an unquoted CSS url() may not contain, and drop control characters.
    // Unquoted, because the SEF plugin does not recognise &quot; and would prefix the URL with the base path.
    $imageUrl = str_replace(
        ['"', "'", '\\', '(', ')', ' '],
        ['%22', '%27', '%5C', '%28', '%29', '%20'],
        preg_replace('/[\x00-\x1F\x7F]/', '', (string) $masthead['image']->url)
    );

    $style = '--aspect-ratio: ' . round($aspectRatio * 100) . '%;'
        . ' --pm-image: url(' . $imageUrl . ');'
        . ' background: var(--pm-image) center center / cover no-repeat;'
        . ($minHeight > 0 ? ' min-height: ' . $minHeight . 'px;' : '')
        . ($maxHeight > 0 ? ' max-height: ' . $maxHeight . 'px;' : '');
    ?>
    <div class="ratio d-flex justify-content-<?php echo $e($masthead['position']); ?> align-items-center p-3 p-sm-5 <?php echo $e($mainDivClass); ?>"
         style="<?php echo $e($style); ?>"
    >
        <div
                class="content d-flex flex-column align-items-<?php echo $e($masthead['position']); ?>
                    w-auto h-auto position-relative text-white text-center"
        >
            <<?php echo $titleTag; ?> class="title">
            <span
                    class="<?php echo $e($masthead['titleclass']); ?>
                                   <?php echo $e($masthead['titlevisibilityclass']); ?>
                            "
                    style="-webkit-box-decoration-break:clone;box-decoration-break:clone;"
            >
                        <?php echo $e($masthead['title']); ?>
                    </span>
        </<?php echo $titleTag; ?>>
        <?php if (!empty($masthead['description'])) : ?>
            <div class="description mt-sm-2
                                <?php echo $e($masthead['descriptionvisibilityclass']); ?>
                    ">
                        <span
                                class="<?php echo $e($masthead['descriptionclass']); ?>"
                                style="-webkit-box-decoration-break:clone;box-decoration-break:clone;"
                        >
                            <?php echo $e($masthead['description']); ?>
                        </span>
            </div>
        <?php endif; ?>
        <?php if (!empty($masthead['buttontext'])) : ?>
            <div class="button mt-sm-2">
                <a class="<?php echo $e($masthead['buttonclass']); ?>" href="<?php echo $e($masthead['buttonurl']); ?>">
                    <?php echo $e($masthead['buttontext']); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
</div>
