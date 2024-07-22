/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_parallax(k, g, a) {
	var c = jQuery;
	var f = c(this);
	var d = a.find('.ws_list');
	var b = k.parallax || 0.25;
	var e = c('<div>')
		.css({
			position: 'absolute',
			top: 0,
			right: 0,
			width: '100%',
			height: '100%',
			overflow: 'hidden',
		})
		.addClass('ws_effect ws_parallax')
		.appendTo(a);
	function j(l) {
		return Math.round(l * 1000) / 1000;
	}
	var i = c('<div>')
		.css({
			position: 'absolute',
			right: 0,
			top: 0,
			overflow: 'hidden',
			width: '100%',
			height: '100%',
			transform: 'translate3d(0,0,0)',
		})
		.appendTo(e);
	var h = i.clone().appendTo(e);
	this.go = function (l, r, p) {
		var s = c(g.get(r));
		s = {
			width: s.width(),
			height: s.height(),
			marginTop: s.css('marginTop'),
			marginRight: s.css('marginRight'),
		};
		p = p ? 1 : -1;
		var n = c(g.get(r)).clone().css(s).appendTo(i);
		var o = c(g.get(l)).clone().css(s).appendTo(h);
		var m = a.width() || k.width;
		var q = a.height() || k.height;
		d.hide();
		wowAnimate(
			function (v) {
				v = c.easing.swing(v);
				var x = j(p * v * m),
					u = j(p * (-m + v * m)),
					t = j(-p * m * b * v),
					w = j(p * m * b * (1 - v));
				if (k.support.transform) {
					i.css('transform', 'translate3d(' + x + 'px,0,0)');
					n.css('transform', 'translate3d(' + t + 'px,0,0)');
					h.css('transform', 'translate3d(' + u + 'px,0,0)');
					o.css('transform', 'translate3d(' + w + 'px,0,0)');
				} else {
					i.css('right', x);
					n.css('margin-right', t);
					h.css('right', u);
					o.css('margin-right', w);
				}
			},
			0,
			1,
			k.duration,
			function () {
				e.hide();
				n.remove();
				o.remove();
				f.trigger('effectEnd');
			}
		);
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'parallax',
	duration: 20 * 100, // change effect transition time
	delay: 20 * 100, // change delay on each slide
	width: 1600,
	height: 800,
	autoPlay: true, // autoplay slides on load
	playPause: false, // show a play & pause button
	stopOnHover: false,
	loop: false,
	bullets: 0,
	caption: false, // use a caption on slides
	controls: true, // use left, right arrows
	fullScreen: true, // show a fullscreen button

	responsive: 3,
	gestures: 2,

	// remove next 3 lines to remove "random" slide order
	onBeforeStep: function (i, c) {
		return i + 1 + Math.floor((c - 1) * Math.random());
	},
});