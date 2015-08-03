<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	mod_bfjssorslider
 * @copyright	Copyright (C) 2015 Jonathan Brain. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$imglist = $params->get('list_images');
if (empty($imglist)) {
  $imglist = new stdClass();
}
else {
  $imglist = json_decode($imglist);
}
if (empty($imglist->images) || !is_array($imglist->images)) {
  $imglist->images = array();
}
$imgfolder = trim($params->get('imagefolder'), '/');
if (!empty($imgfolder)) {
  jimport('joomla.filesystem.folder');
  $extras = JFolder::files(JPATH_SITE . '/' . $imgfolder, $filter = '(.png)|(.jpg)|(.gif)');
  if (!empty($extras)) {
    foreach($extras as $extra) {
      $imglist->images[] = $imgfolder . '/' . $extra;
    }
  }
}
if (empty($imglist->images)) {
  return;
}

$base = JURI::base();
$doc = JFactory::getDocument();

$autoplay = ($params->get('sliderautostart', 1) ? 'true' : 'false');
$startindex = 0;
if ($params->get('slideronofftickbox')) {
  global $bfslideronoffcontrol;
  if (empty($bfslideronoffcontrol)) {
    echo '<div class="bfslideronoffcontrol" title="When ticked will scroll through images.">';
    echo '<input type="checkbox" id="bfslideronoffcontrol" name="bfslideronoffcontrol" value="1"><label for="bfslideronoffcontrol"> Enable slider</label>';
    echo '</div>';
    echo '<div style="clear:both;"></div>';
    $bfslideronoffcontrol = true;   // Can only have one tick box for all sliders on that page
  }
}
    
if (JDEBUG || $params->get('debug')) {
  $doc->addScript($base . '/modules/mod_bfjssorslider/Jssor.Slider.FullPack/js/jssor.js');
  $doc->addScript($base . '/modules/mod_bfjssorslider/Jssor.Slider.FullPack/js/jssor.slider.js');
}
else {
  $doc->addScript($base . '/modules/mod_bfjssorslider/Jssor.Slider.FullPack/js/jssor.slider.mini.js');
}
$doc->addScript($base . '/modules/mod_bfjssorslider/js/mod_bfjssorslider.js');

$slidername = $params->get('slidername', 'bfjssorslider_' . $module->id);
$JSslidername = preg_replace('/[^_0-9a-zA-Z]+/', '', $slidername);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$width = $params->get('width', '960');
$height = $params->get('height', '240');

$scaleslider = 'ScaleSlider_' . $JSslidername;
$slideshowtransitions = '_SlideshowTransitions_' . $JSslidername;
$slideroptions = '_options_' . $JSslidername;

$sliderScript = '
  var ' . $JSslidername . ';
  JSslidernames[JSslidernames.length] = "' . $JSslidername . '";
  jQuery(document).ready(function ($) {
    if (' . $autoplay . ') {
      jQuery("#bfslideronoffcontrol").prop("checked", true);
    }
    jQuery("#bfslideronoffcontrol").change(function() {
      bfjssorslideronoff(jQuery("#bfslideronoffcontrol").prop("checked"));
    } );

    //Define an array of slideshow transition code
    var ' . $slideshowtransitions . ' = [
      //Fade
      { $Duration: ' . $params->get('transitionduration', 200) . ',
        $Opacity: ' . $params->get('transitionopacity', 2) . '
      }
    ];
    var ' . $slideroptions . ' = {
        $AutoPlay: ' . $autoplay . ',
        $StartIndex: ' . $startindex . ',
        $FillMode: 4,
        $PauseOnHover: ' . $params->get('pauseonhover', 0) . ',
        $DragOrientation: ' . $params->get('dragorientation', 0) . ',
        $SlideshowOptions: {
                $Class: $JssorSlideshowRunner$,
                $Transitions: ' . $slideshowtransitions . ',
                $TransitionsOrder: 1,
                $ShowLink: true
            }
    };
    ' . $JSslidername . ' = new $JssorSlider$("' . $JSslidername . '", ' . $slideroptions . ');';
if ($params->get('responsive')) {
  $sliderScript .= '
    //responsive code begin
    function ' . $scaleslider . '() {
      var parentWidth = jQuery("#' . $JSslidername . '").parent().width();
      if (parentWidth) {
        ' . $JSslidername . '.$ScaleWidth(parentWidth);
      }
      else
        window.setTimeout(' . $scaleslider . ', 30);
    }

     window.setTimeout(' . $scaleslider . ', 30);
  
    jQuery(window).bind("load", ' . $scaleslider . ');
    jQuery(window).bind("resize", ' . $scaleslider . ');
    jQuery(window).bind("orientationchange", ' . $scaleslider . ');
    //responsive code end';
}
$sliderScript .= '
    jQuery("#' . $JSslidername . ' > div").css("position", "relative");
  });
';
$doc->addScriptDeclaration($sliderScript);

$images = array();
$mx_width = 0;
$mx_height = 0;
foreach($imglist->images as $key=>$image) {
  if (empty($image)) {
    continue;
  }
  $imageinfo = new stdClass();
  $th_width = $width;
  $th_height = $height;
  if (substr($image, 0, 7) != 'http://' &&
      substr($image, 0, 8) != 'https://') {
    if ($params->get('imagecache')) {
      if (class_exists('plgContentBFSIGPlus')) {
        if ($params->get('imagecrop')) {
          $crop = true; 
        }
        else {
          $crop = false; 
        }
        plgContentBFSIGPlus::createThumbnail($image, $th_width, $th_height, $crop, $params->get('imagequality', 85));
      }
    }
    $imageinfo->image = $base . '/' . trim($image, '/');
  }
  else {
    $imageinfo->image = $image;
  }
  if($mx_width < $th_width) {
    $mx_width = $th_width;
  }
  if($mx_height < $th_height) {
    $mx_height = $th_height;
  }
  $imageinfo->title = @$imglist->titles[$key];
  $imageinfo->width = $th_width;
  $imageinfo->height = $th_height;
  $images[$key] = $imageinfo;
}
    
if (empty($mx_width)) {
  $mx_width = $width;
}
if (empty($mx_height)) {
  $mx_height = $height;
}

$margin  = 'margin-right: auto;margin-left: auto;';
$size = 'width: ' . $mx_width . 'px; height: ' . $mx_height . 'px;';

echo '<div class="mod_bfjssorslider_wrap" style="' . $margin . 'max-width: ' . $mx_width . 'px;">';
 echo '<div id="' . $JSslidername . '" class="bfjssorslider' . $moduleclass_sfx . '" style="' . $size . '">';
  // Slides Container
  echo '<div u="slides" style="' . $size . '">';

  $imgtitleposn = $params->get('image_title_position', 'above');
  $imgtitlehdr = $params->get('image_title_header', 'h2');
  foreach($images as $key=>$imageinfo) {
    $imagetitle = '<div>' .
                    '<' . $imgtitlehdr . ' style="margin:0;">' .
                        $imageinfo->title .
                    '</' . $imgtitlehdr . '>' .
                  '</div>';
    echo '<div class="sliderimg">';
    if ($imgtitleposn == 'above') {
      echo $imagetitle;
    }
    echo '<div>' .
           '<img u="image" src="' . $imageinfo->image . '"/>' .
         '</div>';
    if ($imgtitleposn == 'below') {
      echo $imagetitle;
    }
    echo '</div>';
  }
  echo '</div>';
 echo '</div>';
echo '</div>';
?>