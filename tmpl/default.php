<?php

/**
 * @package     TLWebdesign.Module
 * @subpackage  mod_prettymasthead
 *
 * @copyright   Copyright (C) 2022 TLWebdesign. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

// The static styles are in media/mod_prettymasthead/css/prettymasthead.css, loaded by the Dispatcher

// Escape a value for HTML text and attribute context
$e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

// The helper only returns h1-h6 here
$titleTag = $masthead['titletag'];

// No heading at all when the title is always hidden or empty, so no empty heading ends up in the outline
$showTitle = $masthead['titlevisibility'] !== 'none' && trim((string) $masthead['title']) !== '';

$minHeight = (int) $minHeight;
$maxHeight = (int) $maxHeight;

$hasImage = \is_object($masthead['image']) && $masthead['image']->url !== '';
$hasText  = $showTitle || !empty($masthead['description']) || !empty($masthead['buttons']);

$style = '';

if ($hasImage) {
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

    // --bs-aspect-ratio for Bootstrap 5, --aspect-ratio for Cassiopeia
    $ratio = round($aspectRatio * 100) . '%';
    $style = '--bs-aspect-ratio: ' . $ratio . '; --aspect-ratio: ' . $ratio . ';'
        . ' --pm-image: url(' . $imageUrl . ');';
}

$style = ltrim(
    $style
    . ($minHeight > 0 ? ' min-height: ' . $minHeight . 'px;' : '')
    . ($maxHeight > 0 ? ' max-height: ' . $maxHeight . 'px;' : '')
);
?>
<div class="pretty-masthead">
    <?php
    // Without an image the text is shown on its own, without the image ratio
    if ($hasImage || $hasText) :
    ?>
    <div class="<?php echo $hasImage ? 'ratio ' : ''; ?>d-flex justify-content-<?php echo $e($masthead['position']); ?> align-items-center p-3 p-sm-5 <?php echo $e($mainDivClass); ?>"
         <?php if ($style !== '') : ?>style="<?php echo $e($style); ?>"<?php endif; ?>
    >
        <div
                class="content d-flex flex-column align-items-<?php echo $e($masthead['position']); ?>
                    w-auto h-auto position-relative text-white text-center"
        >
        <?php if ($showTitle) : ?>
            <<?php echo $titleTag; ?> class="title <?php echo $e($masthead['titlevisibilityclass']); ?>">
                <span class="<?php echo $e($masthead['titleclass']); ?>">
                    <?php echo $e($masthead['title']); ?>
                </span>
            </<?php echo $titleTag; ?>>
        <?php endif; ?>
        <?php if (!empty($masthead['description'])) : ?>
            <div class="description mt-sm-2
                                <?php echo $e($masthead['descriptionvisibilityclass']); ?>
                    ">
                        <span class="<?php echo $e($masthead['descriptionclass']); ?>">
                            <?php echo $e($masthead['description']); ?>
                        </span>
            </div>
        <?php endif; ?>
        <?php if (!empty($masthead['buttons'])) : ?>
            <div class="button mt-sm-2 d-flex flex-wrap gap-2 justify-content-<?php echo $e($masthead['position']); ?>">
                <?php foreach ($masthead['buttons'] as $button) : ?>
                    <?php
                    // Bootstrap 5 variant classes like btn-primary only work together with the base btn class
                    $buttonClasses = array_unique(array_merge(['btn'], preg_split('/\s+/', $button['class'], -1, PREG_SPLIT_NO_EMPTY)));
                    ?>
                    <a class="<?php echo $e(implode(' ', $buttonClasses)); ?>" href="<?php echo $e($button['url']); ?>">
                        <?php echo $e($button['text']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
</div>
