/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	mod_bfjssorslider
 * @copyright	Copyright (C) 2015 Jonathan Brain. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

var JSslidernames = [];

function bfjssorslideronoff(on) {
  var cmd;
  if (on) {
    cmd = 'Play';
  }
  else {
    cmd = 'Pause';
  }

  for	(var i=0; i<JSslidernames.length; i++) {
    eval(JSslidernames[i] + '.$' + cmd + '();');
  } 
}
