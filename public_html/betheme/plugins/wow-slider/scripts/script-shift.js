/**
 * WOWslider
 *
 * http://wowslider.com
 */
jQuery.extend(jQuery.easing, {
	easeInOutCubic: function (e, f, a, h, g) {
		if ((f /= g / 2) < 1) {
			return (h / 2) * f * f * f + a;
		}
		return (h / 2) * ((f -= 2) * f * f + 2) + a;
	},
});
function ws_shift(k, i, c) {
	var d = jQuery;
	var h = d(this);
	var b = c.find('li');
	var f = c.find('.ws_list');
	var e = {
		position: 'absolute',
		top: 0,
		right: 0,
		width: '100%',
		height: '100%',
		overflow: 'hidden',
	};
	var g = d('<div>').addClass('ws_effect ws_shift').css(e).appendTo(c);
	var a = d('<div>').css(e).css({ display: 'none', zIndex: 4 }).appendTo(g);
	var j = d('<div>').css(e).css({ display: 'none', zIndex: 3 }).appendTo(g);
	this.go = function (l, p, n) {
		var m = c.width();
		var o = c.height();
		a.append(d(i.get(l)).clone());
		j.append(d(i.get(p)).clone());
		if (k.responsive < 3) {
			a.find('img').css('width', '100%');
			j.find('img').css('width', '100%');
		}
		f.stop(true, true)
			.hide()
			.css({ right: -l + '00%' });
		var q = {
			right: [{ left: -m }, { left: 0 }],
			left: [{ left: m }, { left: 0 }],
			down: [{ top: o }, { top: 0 }],
			up: [{ top: -o }, { top: 0 }],
		}[
			k.direction ||
				['left', 'right', 'down', 'up'][Math.floor(Math.random() * 4)]
		];
		if (k.support.transform) {
			if (q[0].left) {
				q[0] = { translate: [q[0].left, 0, 0] };
			} else {
				q[0] = { translate: [0, q[0].top, 0] };
			}
			q[1] = { translate: [0, 0, 0] };
		}
		a.show();
		j.show();
		wowAnimate(a, q[0], q[1], k.duration, 'easeInOutCubic', function () {
			f.show();
			a.hide().html('');
			j.hide().html('');
			h.trigger('effectEnd');
		});
		wowAnimate(
			j,
			{ scale: 1, translate: [0, 0, 0] },
			{ scale: 0.5, translate: [0, 0, 0] },
			k.duration,
			'easeInOutCubic'
		);
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'shift',
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