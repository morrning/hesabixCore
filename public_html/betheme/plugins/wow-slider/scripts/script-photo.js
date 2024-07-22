/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_photo(a, g, j) {
	var c = jQuery,
		e = c(this),
		l = c('.ws_list', j),
		n = /iPhone|iPod|iPad|Android|BlackBerry/.test(navigator.userAgent),
		h = g.length,
		w = a.imagesCount || 30,
		m = 30,
		d = 80,
		q = [];
	var f = c('<div>')
		.addClass('ws_effect ws_photo')
		.css({
			position: 'absolute',
			top: 0,
			right: 0,
			width: '100%',
			height: '100%',
			overflow: 'hidden',
			backgroundColor: '#DDDDDD',
		})
		.appendTo(j);
	if (!n) {
		var o = c('<div>')
			.css({
				position: 'absolute',
				top: 0,
				right: 0,
				width: '100%',
				height: '100%',
				backgroundColor: 'rgba(0,0,0,0.6)',
				zIndex: 4,
			})
			.appendTo(f);
	}
	var r = Math.max(w, g.length);
	for (var t = 0, p = 0; t < r; t++) {
		if (p >= g.length) {
			p -= g.length;
		}
		c('<div>')
			.addClass('ws_photoItem')
			.css({ width: '100%', height: '100%', overflow: 'hidden' })
			.append(
				c('<div>')
					.css({ overflow: 'hidden', position: 'absolute' })
					.append(c(g[p]).clone())
			)
			.appendTo(f);
		if (a.images && t < g.length) {
			q.push(!a.images[p].noimage);
		}
		p++;
	}
	var s = f.children('.ws_photoItem');
	function u(k, i) {
		return parseFloat(Math.random() * (i - k) + k);
	}
	function v(K, D, i, B, z, J) {
		if (!K[0].wowParams) {
			K[0].wowParams = {};
		}
		if (B && D) {
			var L = d,
				G = 50 - L / 2,
				F = 50 - L / 2,
				A = 0,
				E = 5;
		} else {
			var L = m,
				G = u(-10, 90),
				F = u(-10, 90),
				A = u(-30, 30),
				E = B ? (D ? 5 : i ? 3 : 2) : D ? 3 : i ? 4 : 2;
		}
		var I = {
			top: K[0].wowParams.y || 0,
			left: K[0].wowParams.x || 0,
			width: K[0].wowParams.size || 0,
			height: K[0].wowParams.size || 0,
		};
		var H = { top: F, left: G, width: L, height: L };
		if (a.support.transform) {
			I = {
				translate: [
					50 - I.width / 2 - I.left + '%',
					50 - I.width / 2 - I.top + '%',
					0,
				],
				rotate: K[0].wowParams.angle || 0,
				scale: I.width / 100,
			};
			H = {
				translate: [50 - H.width / 2 - H.left, 50 - H.width / 2 - H.top, 0],
				rotate: A || 0,
				scale: H.width / 100,
			};
		} else {
			for (var C in I) {
				I[C] = I[C] + '%';
			}
		}
		wowAnimate(
			K.css({ position: 'absolute', zIndex: E }),
			I,
			H,
			z,
			'swing',
			J || 0
		);
		K[0].wowParams = { size: L, x: G, y: F, angle: A, zIndex: E };
	}
	s.each(function (i) {
		v(c(this), a.startSlide == i, false, true, 0);
	});
	function b() {
		if (a.support.transform) {
			var i = c(g[0]);
			i = {
				width: i.width(),
				height: i.height(),
				marginTop: parseFloat(i.css('marginTop')),
				marginRight: parseFloat(i.css('marginRight')),
			};
			c(s).find('img').css(i);
		} else {
			c(s).find('img').css({ width: '100%' });
		}
	}
	b();
	c(window).on('load resize', b);
	this.go = function (i, y) {
		b();
		if (a.images && !q[i]) {
			q[i] = true;
			var k = i;
			for (;;) {
				c(s[k])
					.find('img')
					.attr('src', a.images[k % g.length].src);
				if (k > r) {
					break;
				}
				k += g.length;
			}
		}
		if (window.XMLHttpRequest) {
			var x = a.duration * 0.5;
			s.each(function (z) {
				v(c(this), i == z, y == z, false, x);
			});
			if (!n) {
				wowAnimate(o, { opacity: 1 }, { opacity: 0 }, x * 0.7, 'swing');
			}
			setTimeout(function () {
				s.each(function (z) {
					v(
						c(this),
						i == z,
						y == z,
						true,
						x,
						y == z
							? function () {
									e.trigger('effectEnd');
							  }
							: 0
					);
				});
				if (!n) {
					wowAnimate(o, { opacity: 0 }, { opacity: 1 }, x * 0.7, 'swing');
				}
			}, x);
		} else {
			l.stop(true).animate(
				{
					right: i ? -i + '00%' : /Safari/.test(navigator.userAgent) ? '0%' : 0,
				},
				a.duration,
				'easeInOutExpo',
				function () {
					e.trigger('effectEnd');
				}
			);
		}
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'photo',
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