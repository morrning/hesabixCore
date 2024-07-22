/**
 * WOWslider
 *
 * http://wowslider.com
 */
jQuery.extend(jQuery.easing, {
	easeOutBack2: function (f, g, e, j, i) {
		var h = (g /= i) * g;
		var a = h * g;
		return e + j * (5 * a * h + -15 * h * h + 19 * a + -14 * h + 6 * g);
	},
	easeOutCubic: function (e, f, a, h, g) {
		return h * ((f = f / g - 1) * f * f + 1) + a;
	},
	easeInCubic: function (e, f, a, h, g) {
		return h * (f /= g) * f * f + a;
	},
});
function ws_tv(m, i, b) {
	var d = jQuery;
	var g = d(this);
	var k = m.noCanvas || !document.createElement('canvas').getContext;
	var j = m.width,
		e = m.height;
	var f = d('<div>')
		.css({
			position: 'absolute',
			top: 0,
			right: 0,
			width: '100%',
			height: '100%',
			overflow: 'hidden',
		})
		.addClass('ws_effect ws_tv')
		.appendTo(b);
	if (!k) {
		var c = d('<canvas>')
			.css({
				position: 'absolute',
				right: 0,
				top: 0,
				width: '100%',
				height: '100%',
			})
			.appendTo(f);
		var l = c.get(0).getContext('2d');
	}
	function a(n, h, o) {
		return n + (h - n) * o;
	}
	this.go = function (h, o) {
		if (k) {
			b.find('.ws_list')
				.css('transform', 'translate3d(0,0,0)')
				.stop(true)
				.animate(
					{
						right: h
							? -h + '00%'
							: /Safari/.test(navigator.userAgent)
							? '0%'
							: 0,
					},
					m.duration,
					'easeInOutExpo',
					function () {
						g.trigger('effectEnd');
					}
				);
		} else {
			j = b.width();
			e = b.height();
			c.attr({ width: j, height: e });
			var n = d(i.get(h))
				.clone()
				.css({ opacity: 0, zIndex: 2, maxHeight: 'none' })
				.appendTo(f);
			wowAnimate(
				function (p) {
					l.clearRect(0, 0, j, e);
					var r = j;
					if (p >= 0.95) {
						r *= 1 - (p - 0.95) / (1 - 0.95);
					}
					l.fillStyle = '#111';
					l.fillRect(0, 0, j, e);
					var q = l.createLinearGradient(0, (p * e) / 2, 0, e - (p * e) / 2);
					q.addColorStop(0, '#111');
					q.addColorStop(a(0, 0.5, p), '#fff');
					q.addColorStop(0.5, '#fff');
					q.addColorStop(a(1, 0.5, p), '#fff');
					q.addColorStop(1, '#111');
					l.fillStyle = q;
					l.fillRect((j - r) / 2, (p * e) / 2, r, e * (1 - p));
				},
				0,
				1,
				m.duration * 0.3,
				'easeOutCubic',
				function () {
					wowAnimate(
						n,
						{ scale: [0.9, 0], opacity: 0.5 },
						{ scale: [1, 1], opacity: 1 },
						m.duration * 0.3,
						m.duration * 0.4,
						'easeOutBack2',
						function () {
							b.find('.ws_list').css({
								right: h
									? -h + '00%'
									: /Safari/.test(navigator.userAgent)
									? '0%'
									: 0,
							});
							g.trigger('effectEnd');
							setTimeout(function () {
								l.fillStyle = '#111';
								l.clearRect(0, 0, j, e);
								n.remove();
							}, 1);
						}
					);
				}
			);
		}
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'tv',
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