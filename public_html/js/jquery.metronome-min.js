/*
 Copyright (c) 2010 Petr Vostrel (http://petr.vostrel.cz/)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.

 Version: 0.1
 Created during SVN outage on 2010-11-25

 Requires jQuery 1.0 or higher
*/
jQuery.metronome||function(d,e){var a=jQuery.metronome={event:"tick",tempo:2,start:function(f){a.ticking&&a.stop();b=1E3/(f||a.tempo||1);a.tick();a.ticking=true;return a},stop:function(){clearTimeout(c);a.ticking=false;return a},tick:function(){a.trigger();c=setTimeout(a.tick,b);return a},trigger:function(){d(e).trigger(a.event);return a},ticking:false},c,b}(jQuery,document);
