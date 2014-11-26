<?php
/**
 * Milkyway Multimedia
 * HasPreviewForFocusArea.php
 *
 * @package milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\FocusArea\Contracts;


interface HasPreviewForFocusArea {
	public function previewHtmlForFocusArea();
}

interface HasPreviewForFocusArea_Link {
	public function previewLinkForFocusArea();
}