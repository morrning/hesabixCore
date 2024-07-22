/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_rotate(i, h, a) {
	var d = jQuery;
	var g = d(this);
	var e = d('.ws_list', a);
	var b = { position: 'absolute', right: 0, top: 0 };
	var f = d('<div>')
		.addClass('ws_effect ws_rotate')
		.css(b)
		.css({ height: '100%', width: '100%', overflow: 'hidden' })
		.appendTo(a);
	var c;
	this.go = function (j, k) {
		var m = d(h[0]);
		m = {
			width: m.width(),
			height: m.height(),
			marginTop: parseFloat(m.css('marginTop')),
			marginRight: parseFloat(m.css('marginRight')),
			maxHeight: 'none',
			maxWidth: 'none',
		};
		if (c) {
			c.stop(true, true);
		}
		c = d(h.get(j)).clone().css(b).css(m).appendTo(f);
		if (!i.noCross) {
			var l = d(h.get(k)).clone().css(b).css(m).appendTo(f);
			wowAnimate(
				l,
				{ opacity: 1, rotate: 0, scale: 1 },
				{ opacity: 0, rotate: i.rotateOut || 180, scale: i.scaleOut || 10 },
				i.duration,
				'easeInOutExpo',
				function () {
					l.remove();
				}
			);
		}
		wowAnimate(
			c,
			{ opacity: 1, rotate: -(i.rotateIn || 180), scale: i.scaleIn || 10 },
			{ opacity: 1, rotate: 0, scale: 1 },
			i.duration,
			'easeInOutExpo',
			function () {
				c.remove();
				c = 0;
				g.trigger('effectEnd');
			}
		);
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'rotate',
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
	onBeforeStep: function (i, c) {
		return i + 1 + Math.floor((c - 1) * Math.random());
	},
});