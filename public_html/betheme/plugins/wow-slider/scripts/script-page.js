/**
 * WOWslider
 *
 * http://wowslider.com
 */
jQuery.extend(jQuery.easing, {
	easeOutOneBounce: function (e, i, c, a, d, g) {
		var j = 0.8;
		var b = 0.2;
		var f = j * j;
		if (i < 0.0001) {
			return 0;
		} else {
			if (i < j) {
				return (i * i) / f;
			} else {
				return 1 - b * b + (i - j - b) * (i - j - b);
			}
		}
	},
	easeOutBounce: function (e, f, a, h, g) {
		if ((f /= g) < 1 / 2.75) {
			return h * (7.5625 * f * f) + a;
		} else {
			if (f < 2 / 2.75) {
				return h * (7.5625 * (f -= 1.5 / 2.75) * f + 0.75) + a;
			} else {
				if (f < 2.5 / 2.75) {
					return h * (7.5625 * (f -= 2.25 / 2.75) * f + 0.9375) + a;
				} else {
					return h * (7.5625 * (f -= 2.625 / 2.75) * f + 0.984375) + a;
				}
			}
		}
	},
});
function ws_page(c, b, a) {
	var e = jQuery;
	var h = c.angle || 17;
	var g = e(this);
	var f = e('<div>')
		.addClass('ws_effect ws_page')
		.css({
			position: 'absolute',
			width: '100%',
			height: '100%',
			top: '0%',
			overflow: 'hidden',
		});
	var d = a.find('.ws_list');
	f.hide().appendTo(a);
	this.go = function (l, j) {
		function o() {
			f.find('div').stop(1, 1);
			f.hide();
			f.empty();
		}
		o();
		d.hide();
		var k = e('<div>')
			.css({
				position: 'absolute',
				right: 0,
				top: 0,
				width: '100%',
				height: '100%',
				overflow: 'hidden',
				'z-index': 9,
			})
			.append(e(b.get(l)).clone())
			.appendTo(f);
		var i = e('<div>')
			.css({
				position: 'absolute',
				right: 0,
				top: 0,
				width: '100%',
				height: '100%',
				overflow: 'hidden',
				outline: '1px solid transparent',
				'z-index': 10,
				'transform-origin': 'top left',
				'backface-visibility': 'hidden',
			})
			.append(e(b.get(j)).clone())
			.appendTo(f);
		f.show();
		if (c.responsive < 3) {
			k.find('img').css('width', '100%');
			i.find('img').css('width', '100%');
		}
		var q = i;
		var p = q.width(),
			m = q.height();
		var n = !document.addEventListener;
		wowAnimate(
			q,
			{ rotate: 0 },
			{ rotate: h },
			n ? 0 : (2 * c.duration) / 3,
			'easeOutOneBounce',
			function () {
				wowAnimate(
					q,
					{ top: 0 },
					{ top: '100%' },
					((n ? 2 : 1) * c.duration) / 3
				);
			}
		);
		wowAnimate(
			k,
			{ top: '-100%' },
			{ top: '-30%' },
			n ? 0 : c.duration / 2,
			function () {
				wowAnimate(
					k,
					{ top: '-30%' },
					{ top: 0 },
					((n ? 2 : 1) * c.duration) / 2,
					'easeOutBounce',
					function () {
						q.hide();
						o();
						g.trigger('effectEnd');
					}
				);
			}
		);
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'page',
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