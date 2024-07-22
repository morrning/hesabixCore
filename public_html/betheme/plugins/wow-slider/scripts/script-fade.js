/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_fade(c, a, b) {
	var e = jQuery,
		g = e(this),
		d = e('.ws_list', b),
		h = {
			position: 'absolute',
			right: 0,
			top: 0,
			width: '100%',
			height: '100%',
			maxHeight: 'none',
			maxWidth: 'none',
			transform: 'translate3d(0,0,0)',
		},
		f = e('<div>')
			.addClass('ws_effect ws_fade')
			.css(h)
			.css('overflow', 'hidden')
			.appendTo(b);
	this.go = function (i, j) {
		var k = e(a.get(i)),
			m = { width: k.width(), height: k.height() };
		k = k.clone().css(h).css(m).appendTo(f);
		if (!c.noCross) {
			var l = e(a.get(j)).clone().css(h).css(m).appendTo(f);
			wowAnimate(l, { opacity: 1 }, { opacity: 0 }, c.duration, function () {
				l.remove();
			});
		}
		wowAnimate(k, { opacity: 0 }, { opacity: 1 }, c.duration, function () {
			g.trigger('effectEnd');
			k.remove();
		});
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'fade',
	duration: 20 * 100, // change effect transition time
	delay: 20 * 100, // change delay on each slide
	width: 1000,
	height: 450,
	autoPlay: true, // autoplay slides on load
	playPause: false, // show a play & pause button
	stopOnHover: false,
	loop: false,
	bullets: 0,
	caption: false, // use a caption on slides
	controls: false, // use left, right arrows
	fullScreen: true, // show a fullscreen button

	responsive: 1,
	gestures: 2,

	// remove next 3 lines to remove "random" slide order
	// onBeforeStep:function(i, c) {
	// return (i+1 + Math.floor((c-1)*Math.random()))
	// }
});