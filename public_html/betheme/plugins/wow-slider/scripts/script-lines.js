/**
 * WOWslider
 *
 * http://wowslider.com
 */
jQuery.extend(jQuery.easing, {
	easeOutBack: function (e, f, a, i, h, g) {
		if (g == undefined) {
			g = 1.70158;
		}
		return i * ((f = f / h - 1) * f * ((g + 1) * f + g) + 1) + a;
	},
	easeOutBackCubic: function (e, f, a, j, i, g) {
		var h = (f /= i) * f;
		return (
			a + j * (-1.5 * h * f * h + 2 * h * h + 4 * h * f + -9 * h + 5.5 * f)
		);
	},
	easeOutCubic: function (e, f, a, h, g) {
		return h * ((f = f / g - 1) * f * f + 1) + a;
	},
	easeOutExpo: function (e, f, a, h, g) {
		return f == g ? a + h : h * (-Math.pow(2, (-10 * f) / g) + 1) + a;
	},
});
function ws_lines(d, l, m) {
	var e = jQuery;
	var f = e(this);
	var i = d.noCanvas || !document.createElement('canvas').getContext;
	var k = d.width,
		r = d.height;
	var g = e('<div>')
		.css({
			position: 'absolute',
			top: 0,
			right: 0,
			width: '100%',
			height: '100%',
			overflow: 'hidden',
		})
		.addClass('ws_effect ws_lines')
		.appendTo(m);
	if (!i) {
		var b = e('<canvas>')
			.css({
				position: 'absolute',
				right: 0,
				top: 0,
				width: '100%',
				height: '100%',
			})
			.appendTo(g);
		var o = b.get(0).getContext('2d');
	}
	var s = [
		['rgb(187,187,187)', 0.1, 0.3],
		['rgb(179,179,179)', 0.9, 0.8],
		['rgb(182,182,182)', 0.68, 0.4],
		['rgb(185,185,185)', 0.25, 0.4],
		['rgb(204,204,204)', 0.11, 0.7],
		['rgb(195,195,195)', 0.18, 0.1],
		['rgb(198,198,198)', 0.4, 0.2],
		['rgb(201,201,201)', 0.55, -0.04],
		['rgb(211,211,211)', 0, 0.95],
		['rgb(214,214,214)', 0.5, 0.8],
		['rgb(217,217,217)', 0.9, 0.1],
	];
	var a = [
		[0.5, 0.4, 0.3, 0.2, 0.1, 0, 0.1, 0.2, 0.3, 0.4, 0.5],
		[0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.4, 0.3, 0.2, 0.1, 0],
		[0, 0.05, 0.1, 0.15, 0.2, 0.25, 0.3, 0.35, 0.4, 0.45, 0.5],
		[0.5, 0.45, 0.4, 0.35, 0.3, 0.25, 0.2, 0.15, 0.1, 0.05, 0],
		[0.7, 0.3, 0, 0.1, 0.5, 0.3, 0.4, 0.1, 0.6, 0.2, 0],
	];
	var q = [
		'from-top',
		'from-bottom',
		'width-from-center',
		'height-from-center',
		'fill-half-fill-full',
	];
	var j = ['easeOutExpo', 'easeOutCubic', 'easeOutBackCubic', 'easeOutBack'];
	var p = [45, -45, 0, 180, 90, -90];
	function n(h) {
		h.save();
		h.setTransform(1, 0, 0, 1, 0, 0);
		h.clearRect(0, 0, k, r);
		h.restore();
	}
	function c(G, D, I, w, C, y, z) {
		var u = k;
		var E = r;
		if (z == 45 || z == -45) {
			u = E = Math.sqrt(k * k + r * r);
		}
		if (z == 90 || z == -90) {
			u = r;
			E = k;
		}
		var B = (u - k) / 2;
		var v = (E - r) / 2;
		n(G);
		for (var x = 0, A = I.length; x < A; x++) {
			var F = I[x] * (1 - D);
			var h = Math.max(0, Math.min(1, D - F));
			G.beginPath();
			G.fillStyle = s[x][0];
			if (w) {
				G.fillStyle = s[x][0]
					.replace(/rgb/g, 'rgba')
					.replace(/\)/g, ',' + Math.min(D + 0.1, 1) + ')');
			}
			var H = { x: 0, y: 0, w: 0, h: 0 };
			switch (C) {
				case 'from-top':
					if (y && e.easing[y]) {
						h = e.easing[y](1, h, 0, 1, 1, 1);
					}
					H.w = Math.ceil(0.5 + u / A);
					H.h = E;
					H.x = ((A - x - 1) * u) / A - B;
					H.y = -1.5 * E * (1 - h) - v;
					break;
				case 'from-bottom':
					if (y && e.easing[y]) {
						h = e.easing[y](1, h, 0, 1, 1, 1);
					}
					H.w = Math.ceil(0.5 + u / A);
					H.h = E;
					H.x = ((A - x - 1) * u) / A - B;
					H.y = 1.5 * E * (1 - h) - v;
					break;
				case 'width-from-center':
					if (y && e.easing[y]) {
						h = e.easing[y](1, h, 0, 1, 1, 1);
					}
					H.w = Math.ceil(0.5 + u / A) * h;
					H.h = E;
					H.x = ((A - x - 1) * u) / A + ((1 - h) * u) / A / 2 - B;
					H.y = -v;
					break;
				case 'height-from-center':
					if (y && e.easing[y]) {
						h = e.easing[y](1, h, 0, 1, 1, 1);
					}
					H.w = Math.ceil(0.5 + u / A);
					H.h = E * h;
					H.x = ((A - x - 1) * u) / A - B;
					H.y = ((1 - h) * E) / 2 - v;
					break;
				case 'fill-half-fill-full':
					if (h < 0.5) {
						if (y && e.easing[y]) {
							h = e.easing[y](0.5, h, 0, 0.5, 0.5, 0.5);
						}
					}
					H.w = Math.ceil(0.5 + u / A);
					H.h = E * h;
					H.x = ((A - x - 1) * u) / A - B;
					H.y = ((1 - h) * E) / 2 - v;
					break;
			}
			G.fillRect(H.x, H.y, H.w, H.h);
		}
	}
	this.go = function (C, x) {
		if (i) {
			m.find('.ws_list')
				.css('transform', 'translate3d(0,0,0)')
				.stop(true)
				.animate(
					{
						right: C
							? -C + '00%'
							: /Safari/.test(navigator.userAgent)
							? '0%'
							: 0,
					},
					d.duration,
					'easeInOutExpo',
					function () {
						f.trigger('effectEnd');
					}
				);
		} else {
			k = m.width();
			r = m.height();
			var w = a[Math.floor(Math.random() * a.length)];
			var E = a[Math.floor(Math.random() * a.length)];
			b.attr({ width: k, height: r });
			var A = l.get(x);
			for (var y = 0, B = s.length; y < B; y++) {
				var v = Math.abs(s[y][1]),
					h = Math.abs(s[y][2]);
				s[y][0] = t(A, { x: v * k, y: h * r, w: 2, h: 2 }) || s[y][0];
			}
			var D = q[Math.floor(Math.random() * q.length)];
			var z = j[Math.floor(Math.random() * j.length)];
			var u = p[Math.floor(Math.random() * p.length)];
			o.translate(k / 2, r / 2);
			o.rotate((u * Math.PI) / 180);
			o.translate(-k / 2, -r / 2);
			wowAnimate(
				function (F) {
					c(o, F, w, true, D, z, u);
				},
				0,
				1,
				d.duration / 2,
				function () {
					m.find('.ws_list').css({
						right: C
							? -C + '00%'
							: /Safari/.test(navigator.userAgent)
							? '0%'
							: 0,
					});
					D = q[Math.floor(Math.random() * q.length)];
					z = j[Math.floor(Math.random() * j.length)];
					wowAnimate(
						function (F) {
							c(o, 1 - F, E, false, D, z, u);
						},
						0,
						1,
						d.duration / 2,
						d.duration * 0.15,
						function () {
							n(o);
							f.trigger('effectEnd');
						}
					);
				}
			);
		}
	};
	function t(D, u) {
		u = u || {};
		var F = 1,
			x = u.exclude || [],
			C;
		var z = document.createElement('canvas'),
			w = z.getContext('2d'),
			v = (z.width = D.naturalWidth),
			J = (z.height = D.naturalHeight);
		w.drawImage(D, 0, 0, D.naturalWidth, D.naturalHeight);
		try {
			C = w.getImageData(
				u.x ? u.x : 0,
				u.y ? u.y : 0,
				u.w ? u.w : D.width,
				u.h ? u.h : D.height
			)['data'];
		} catch (E) {
			return false;
		}
		var y = (u.w ? u.w : D.width * u.h ? u.h : D.height) || C.length,
			A = {},
			H = '',
			G = [],
			h = { dominant: { name: '', count: 0 } };
		var B = 0;
		while (B < y) {
			G[0] = C[B];
			G[1] = C[B + 1];
			G[2] = C[B + 2];
			H = G.join(',');
			if (H in A) {
				A[H] = A[H] + 1;
			} else {
				A[H] = 1;
			}
			if (x.indexOf(['rgb(', H, ')'].join('')) === -1) {
				var I = A[H];
				if (I > h.dominant.count) {
					h.dominant.name = H;
					h.dominant.count = I;
				}
			}
			B += F * 4;
		}
		return ['rgb(', h.dominant.name, ')'].join('');
	}
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'lines',
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