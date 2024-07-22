/**
 * WOWslider
 *
 * http://wowslider.com
 */
jQuery.extend(jQuery.easing, {
	easeInOutSine: function (j, i, b, c, d) {
		return (-c / 2) * (Math.cos((Math.PI * i) / d) - 1) + b;
	},
});
function ws_domino(m, i, k) {
	$ = jQuery;
	var h = $(this);
	var c = m.columns || 5,
		l = m.rows || 2,
		d = m.centerRow || 2,
		g = m.centerColumn || 2;
	var f = $('<div>')
		.addClass('ws_effect ws_domino')
		.css({
			position: 'absolute',
			width: '100%',
			height: '100%',
			top: 0,
			overflow: 'hidden',
		})
		.appendTo(k);
	var b = $('<div>').addClass('ws_zoom').appendTo(f);
	var j = $('<div>').addClass('ws_parts').appendTo(f);
	var e = k.find('.ws_list');
	var a;
	this.go = function (y, x) {
		function z() {
			j.find('img').stop(1, 1);
			j.empty();
			b.empty();
			a = 0;
		}
		z();
		var s = $(i.get(x));
		s = {
			width: s.width(),
			height: s.height(),
			marginTop: parseFloat(s.css('marginTop')),
			marginRight: parseFloat(s.css('marginRight')),
		};
		var D = $(i.get(x))
			.clone()
			.appendTo(b)
			.css({ position: 'absolute', top: 0, right: 0 })
			.css(s);
		var p = f.width();
		var o = f.height();
		var w = Math.floor(p / c);
		var v = Math.floor(o / l);
		var t = p - w * (c - 1);
		var E = o - v * (l - 1);
		function I(L, K) {
			return Math.random() * (K - L + 1) + L;
		}
		e.hide();
		var u = [];
		for (var C = 0; C < l; C++) {
			u[C] = [];
			for (var B = 0; B < c; B++) {
				var q = m.duration * (1 - Math.abs((d * g - C * B) / (2 * l * c)));
				var F = B < c - 1 ? w : t;
				var n = C < l - 1 ? v : E;
				u[C][B] = $('<div>').css({
					width: F,
					height: n,
					position: 'absolute',
					top: C * v,
					right: B * w,
					overflow: 'hidden',
				});
				var H = I(C - 2, C + 2);
				var G = I(B - 2, B + 2);
				u[C][B].appendTo(j);
				var J = $(i.get(y)).clone().appendTo(u[C][B]).css(s);
				var A = { top: -H * v, right: G * w, opacity: 0 };
				var r = { top: -C * v, right: B * w, opacity: 1 };
				if (m.support.transform && m.support.transition) {
					A.translate = [A.right, A.top, 0];
					r.translate = [r.right, r.top, 0];
					delete A.top;
					delete A.right;
					delete r.top;
					delete r.right;
				}
				wowAnimate(
					J.css({ position: 'absolute' }),
					A,
					r,
					q,
					'easeInOutSine',
					function () {
						a++;
						if (a == l * c) {
							z();
							e.stop(1, 1);
							h.trigger('effectEnd');
						}
					}
				);
			}
		}
		wowAnimate(
			D,
			{ scale: 1 },
			{ scale: 1.6 },
			m.duration,
			m.duration * 0.2,
			'easeInOutSine'
		);
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'domino',
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