/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_bubbles(b, l, n) {
	var e = jQuery;
	var f = e(this);
	var i = b.noCanvas || !document.createElement('canvas').getContext;
	var k = b.width,
		p = b.height;
	var g = e('<div>')
		.css({
			position: 'absolute',
			top: 0,
			right: 0,
			width: '100%',
			height: '100%',
			overflow: 'hidden',
		})
		.addClass('ws_effect ws_bubbles')
		.appendTo(n);
	if (!i) {
		var a = e('<canvas>')
			.css({
				position: 'absolute',
				right: 0,
				top: 0,
				width: '100%',
				height: '100%',
			})
			.appendTo(g);
		var o = a.get(0).getContext('2d');
	}
	var j = {
		easeOutBack: function (u, v, h, z, y, w) {
			if (w == undefined) {
				w = 1.70158;
			}
			return z * ((v = v / y - 1) * v * ((w + 1) * v + w) + 1) + h;
		},
		easeOutBackCubic: function (u, v, h, A, z, w) {
			var y = (v /= z) * v;
			return (
				h + A * (-1.5 * y * v * y + 2 * y * y + 4 * y * v + -9 * y + 5.5 * v)
			);
		},
		easeOutCubic: function (u, v, h, y, w) {
			return y * ((v = v / w - 1) * v * v + 1) + h;
		},
		easeOutExpo: function (u, v, h, y, w) {
			return v == w ? h + y : y * (-Math.pow(2, (-10 * v) / w) + 1) + h;
		},
	};
	var s = [
		['#bbbbbb', 0.5, 0.5],
		['#b3b3b3', 0.2, 0.2],
		['#b6b6b6', 0.5, 0.2],
		['#b9b9b9', 0.8, 0.2],
		['#cccccc', 0.2, 0.8],
		['#c3c3c3', 0.5, 0.8],
		['#c6c6c6', 0.8, 0.8],
	];
	var c = [
		[
			[0.5, 0.5, 0.7, 0.15],
			[0.5, 0.5, 0.6, 0.3],
			[0.5, 0.5, 0.5, 0.45],
			[0.5, 0.5, 0.4, 0.6],
			[0.5, 0.5, 0.3, 0.75],
			[0.5, 0.5, 0.2, 0.9],
			[0.5, 0.5, 0.1, 1],
		],
		[
			[0.5, 0.5, 0.7, 1],
			[0.5, 0.5, 0.6, 0.9],
			[0.5, 0.5, 0.5, 0.75],
			[0.5, 0.5, 0.4, 0.6],
			[0.5, 0.5, 0.3, 0.45],
			[0.5, 0.5, 0.2, 0.3],
			[0.5, 0.5, 0.1, 0.15],
		],
	];
	var m = [
		[
			[0.5, 0.5, 0, 1],
			[0.5, 0.5, 0, 0.9],
			[0.5, 0.5, 0, 0.75],
			[0.5, 0.5, 0, 0.6],
			[0.5, 0.5, 0, 0.45],
			[0.5, 0.5, 0, 0.3],
			[0.5, 0.5, 0, 0.15],
		],
		[
			[0.5, 0.5, 0, 0.15],
			[0.5, 0.5, 0, 0.3],
			[0.5, 0.5, 0, 0.45],
			[0.5, 0.5, 0, 0.6],
			[0.5, 0.5, 0, 0.75],
			[0.5, 0.5, 0, 0.9],
			[0.5, 0.5, 0, 1],
		],
		[
			[0.5, 7.5, 0.7, 0.75],
			[0.5, 7.5, 0.6, 0.15],
			[0.5, 7.5, 0.5, 1],
			[0.5, 7.5, 0.4, 0.3],
			[0.5, 7.5, 0.3, 0.45],
			[0.5, 7.5, 0.2, 0.6],
			[0.5, 7.5, 0.1, 0.9],
		],
		[
			[0.5, 7.5, 0.7, 1],
			[0.5, 7.5, 0.6, 0.9],
			[0.5, 7.5, 0.5, 0.75],
			[0.5, 7.5, 0.4, 0.6],
			[0.5, 7.5, 0.3, 0.45],
			[0.5, 7.5, 0.2, 0.3],
			[0.5, 7.5, 0.1, 0.15],
		],
	];
	function d(u) {
		if (Object.prototype.toString.call(u) === '[object Array]') {
			return u[Math.floor(Math.random() * u.length)];
		} else {
			var h;
			var t = 0;
			for (var v in u) {
				if (Math.random() < 1 / ++t) {
					h = v;
				}
			}
			return /linear|swing/g.test(h) ? d(u) : h;
		}
	}
	function q(B, A, v, z, t) {
		B.clearRect(0, 0, k, p);
		for (var u = 0, y = v.length; u < y; u++) {
			var h = Math.max(0, Math.min(1, A - v[u][3] * (1 - A)));
			if (t && j[t]) {
				h = j[t](1, h, 0, 1, 1, 1);
			}
			var w = k;
			if (k / p <= 1.8 && k / p > 0.7) {
				w *= 2;
			} else {
				if (k / p <= 0.7) {
					w = p * 2;
				}
			}
			var x = v[u][2] * h * w;
			if (z) {
				x = (v[u][2] + (z[u][2] - v[u][2]) * h) * w;
			}
			x = Math.max(0, x);
			B.beginPath();
			B.arc(
				(v[u][0] + ((z ? z[u][0] : 0.5) - v[u][0]) * h) * k,
				(v[u][1] + ((z ? z[u][1] : 0.5) - v[u][1]) * h) * p,
				x,
				0,
				2 * Math.PI,
				false
			);
			B.fillStyle = s[u][0];
			B.fill();
		}
	}
	this.go = function (B, w) {
		if (i) {
			n.find('.ws_list')
				.css('transform', 'translate3d(0,0,0)')
				.stop(true)
				.animate(
					{
						right: B
							? -B + '00%'
							: /Safari/.test(navigator.userAgent)
							? '0%'
							: 0,
					},
					b.duration,
					'easeInOutExpo',
					function () {
						f.trigger('effectEnd');
					}
				);
		} else {
			k = n.width();
			p = n.height();
			a.attr({ width: k, height: p });
			var z = l.get(w);
			for (var x = 0, A = s.length; x < A; x++) {
				var u = Math.abs(s[x][1]),
					h = Math.abs(s[x][2]);
				s[x][0] = r(z, { x: u * k, y: h * p, w: 2, h: 2 }) || s[x][0];
			}
			var t = d(c);
			var v = d(m);
			var y = d(j);
			wowAnimate(
				function (C) {
					q(o, C, t, 0, y);
				},
				0,
				1,
				b.duration / 2,
				function () {
					n.find('.ws_list').css({
						right: B
							? -B + '00%'
							: /Safari/.test(navigator.userAgent)
							? '0%'
							: 0,
					});
					y = d(j);
					wowAnimate(
						function (C) {
							q(o, 1 - C, v, t, y);
						},
						0,
						1,
						b.duration / 2,
						function () {
							o.clearRect(0, 0, k, p);
							f.trigger('effectEnd');
						}
					);
				}
			);
		}
	};
	function r(C, t) {
		t = t || {};
		var E = 1,
			w = t.exclude || [],
			B;
		var y = document.createElement('canvas'),
			v = y.getContext('2d'),
			u = (y.width = C.naturalWidth),
			I = (y.height = C.naturalHeight);
		v.drawImage(C, 0, 0, C.naturalWidth, C.naturalHeight);
		try {
			B = v.getImageData(
				t.x ? t.x : 0,
				t.y ? t.y : 0,
				t.w ? t.w : C.width,
				t.h ? t.h : C.height
			)['data'];
		} catch (D) {
			return false;
		}
		var x = (t.w ? t.w : C.width * t.h ? t.h : C.height) || B.length,
			z = {},
			G = '',
			F = [],
			h = { dominant: { name: '', count: 0 } };
		var A = 0;
		while (A < x) {
			F[0] = B[A];
			F[1] = B[A + 1];
			F[2] = B[A + 2];
			G = F.join(',');
			if (G in z) {
				z[G] = z[G] + 1;
			} else {
				z[G] = 1;
			}
			if (w.indexOf(['rgb(', G, ')'].join('')) === -1) {
				var H = z[G];
				if (H > h.dominant.count) {
					h.dominant.name = G;
					h.dominant.count = H;
				}
			}
			A += E * 4;
		}
		return ['rgb(', h.dominant.name, ')'].join('');
	}
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'bubbles',
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