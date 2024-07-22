/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_glass_parallax(d, l, m) {
	var f = jQuery;
	var i = f(this);
	var j = d.parallax || 0.25;
	var k = f('<div>')
		.css({
			position: 'absolute',
			display: 'none',
			top: 0,
			right: 0,
			width: '100%',
			height: '100%',
			overflow: 'hidden',
		})
		.addClass('ws_effect ws_glass_parallax')
		.appendTo(m);
	var h =
		!d.noCanvas &&
		!window.opera &&
		!!document.createElement('canvas').getContext;
	if (h) {
		try {
			document
				.createElement('canvas')
				.getContext('2d')
				.getImageData(0, 0, 1, 1);
		} catch (q) {
			h = 0;
		}
	}
	function t(e) {
		return Math.round(e * 1000) / 1000;
	}
	var u = f('<div>')
		.css({
			position: 'absolute',
			right: 0,
			top: 0,
			overflow: 'hidden',
			width: '100%',
			height: '100%',
			transform: 'translate3d(0,0,0)',
			zIndex: 1,
		})
		.appendTo(k);
	var s = u.clone().appendTo(k);
	var r = u.clone().css({ width: '20%' }).appendTo(k);
	var c;
	var p = u.clone().appendTo(k).css({ zIndex: 0 });
	this.go = function (C, A, x) {
		var e = f(l.get(A));
		e = {
			position: 'absolute',
			width: e.width(),
			height: e.height(),
			marginTop: e.css('marginTop'),
			marginRight: e.css('marginRight'),
		};
		x = x ? 1 : -1;
		var E = f(l.get(A)).clone().css(e).appendTo(u);
		var z = f(l.get(C)).clone().css(e).appendTo(s);
		var v = f(l.get(C)).clone().css(e).appendTo(r);
		if (x == -1) {
			r.css({ right: 'auto', left: 0 });
			v.css({ right: 'auto', left: 0 });
		} else {
			r.css({ right: 0, left: 'auto' });
			v.css({ right: 0, left: 'auto' });
		}
		var D = (m.width() || d.width) * 1.3;
		var B = m.height() || d.height;
		var y;
		if (h) {
			if (!c) {
				c = f('<canvas>')
					.css({ position: 'absolute', right: 0, top: 0 })
					.attr({ width: e.width, height: e.height })
					.appendTo(p);
			}
			c.css(e).attr({ width: e.width, height: e.height });
			y = o(f(l.get(C)), e, 10, c.get(0));
		}
		if (!h || !y) {
			h = 0;
		}
		wowAnimate(
			function (G) {
				G = f.easing.swing(G);
				var L = t(-x * G * D),
					F = t(-x * (-D + G * D - (1 - G) * D * 0.2)),
					J = t(-x * (-D * 1.4 + G * (D * 1.4 + D / 1.3))),
					w = t(x * D * j * G),
					H = t(-x * D * j * (1 - G)),
					I = t(x * D * (j + 0.2) * G),
					K = t(-x * (-D * 0.2 + G * D * 0.4));
				if (d.support.transform) {
					u.css('transform', 'translate3d(' + L + 'px,0,0)');
					E.css('transform', 'translate3d(' + w + 'px,0,0)');
					s.css('transform', 'translate3d(' + F + 'px,0,0)');
					z.css('transform', 'translate3d(' + H + 'px,0,0)');
					r.css('transform', 'translate3d(' + J + 'px,0,0)');
					v.css('transform', 'scale(1.6) translate3d(' + I + 'px,0,0)');
					p.css('transform', 'translate3d(' + K + 'px,0,0)');
				} else {
					u.css('right', L);
					E.css('margin-right', w);
					s.css('right', F);
					z.css('margin-right', H);
					r.css('right', J);
					v.css('margin-right', I);
					p.css('right', K);
				}
			},
			0,
			1,
			d.duration,
			function () {
				k.hide();
				E.remove();
				z.remove();
				v.remove();
				i.trigger('effectEnd');
			}
		);
	};
	function o(C, A, B, v) {
		var F = (parseInt(C.parent().css('z-index')) || 0) + 1;
		if (h) {
			var I = v.getContext('2d');
			I.drawImage(C.get(0), 0, 0, A.width, A.height);
			if (!a(I, 0, 0, v.width, v.height, B)) {
				return 0;
			}
			return f(v);
		}
		var J = f('<div></div>')
			.css({ position: 'absolute', 'z-index': F, right: 0, top: 0 })
			.css(A)
			.appendTo(v);
		var H = (Math.sqrt(5) + 1) / 2;
		var w = 1 - H / 2;
		for (var z = 0; w * z < B; z++) {
			var D = Math.PI * H * z;
			var e = w * z + 1;
			var G = e * Math.cos(D);
			var E = e * Math.sin(D);
			f(document.createElement('img'))
				.attr('src', C.attr('src'))
				.css({
					opacity: 1 / (z / 1.8 + 1),
					position: 'absolute',
					'z-index': F,
					right: Math.round(G) + 'px',
					top: Math.round(E) + 'px',
					width: '100%',
					height: '100%',
				})
				.appendTo(J);
		}
		return J;
	}
	var g = [
		512, 512, 456, 512, 328, 456, 335, 512, 405, 328, 271, 456, 388, 335, 292,
		512, 454, 405, 364, 328, 298, 271, 496, 456, 420, 388, 360, 335, 312, 292,
		273, 512, 482, 454, 428, 405, 383, 364, 345, 328, 312, 298, 284, 271, 259,
		496, 475, 456, 437, 420, 404, 388, 374, 360, 347, 335, 323, 312, 302, 292,
		282, 273, 265, 512, 497, 482, 468, 454, 441, 428, 417, 405, 394, 383, 373,
		364, 354, 345, 337, 328, 320, 312, 305, 298, 291, 284, 278, 271, 265, 259,
		507, 496, 485, 475, 465, 456, 446, 437, 428, 420, 412, 404, 396, 388, 381,
		374, 367, 360, 354, 347, 341, 335, 329, 323, 318, 312, 307, 302, 297, 292,
		287, 282, 278, 273, 269, 265, 261, 512, 505, 497, 489, 482, 475, 468, 461,
		454, 447, 441, 435, 428, 422, 417, 411, 405, 399, 394, 389, 383, 378, 373,
		368, 364, 359, 354, 350, 345, 341, 337, 332, 328, 324, 320, 316, 312, 309,
		305, 301, 298, 294, 291, 287, 284, 281, 278, 274, 271, 268, 265, 262, 259,
		257, 507, 501, 496, 491, 485, 480, 475, 470, 465, 460, 456, 451, 446, 442,
		437, 433, 428, 424, 420, 416, 412, 408, 404, 400, 396, 392, 388, 385, 381,
		377, 374, 370, 367, 363, 360, 357, 354, 350, 347, 344, 341, 338, 335, 332,
		329, 326, 323, 320, 318, 315, 312, 310, 307, 304, 302, 299, 297, 294, 292,
		289, 287, 285, 282, 280, 278, 275, 273, 271, 269, 267, 265, 263, 261, 259,
	];
	var n = [
		9, 11, 12, 13, 13, 14, 14, 15, 15, 15, 15, 16, 16, 16, 16, 17, 17, 17, 17,
		17, 17, 17, 18, 18, 18, 18, 18, 18, 18, 18, 18, 19, 19, 19, 19, 19, 19, 19,
		19, 19, 19, 19, 19, 19, 19, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20,
		20, 20, 20, 20, 20, 20, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21,
		21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 22, 22, 22, 22, 22,
		22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22,
		22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 23, 23, 23, 23, 23, 23,
		23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23,
		23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23,
		23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 24, 24, 24, 24, 24, 24, 24, 24, 24,
		24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24,
		24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24,
		24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24,
		24, 24, 24, 24, 24, 24, 24, 24,
	];
	function a(am, U, S, v, w, ad) {
		if (isNaN(ad) || ad < 1) {
			return;
		}
		ad |= 0;
		var ah;
		try {
			ah = am.getImageData(U, S, v, w);
		} catch (al) {
			console.log('error:unable to access image data: ' + al);
			return false;
		}
		var C = ah.data;
		var ab, aa, aj, ag, J, M, G, A, B, R, H, T, P, X, ac, K, F, L, N, W;
		var ak = ad + ad + 1;
		var Y = v << 2;
		var I = v - 1;
		var af = w - 1;
		var E = ad + 1;
		var ae = (E * (E + 1)) / 2;
		var V = new b();
		var Q = V;
		for (aj = 1; aj < ak; aj++) {
			Q = Q.next = new b();
			if (aj == E) {
				var D = Q;
			}
		}
		Q.next = V;
		var ai = null;
		var Z = null;
		G = M = 0;
		var O = g[ad];
		var z = n[ad];
		for (aa = 0; aa < w; aa++) {
			X = ac = K = A = B = R = 0;
			H = E * (F = C[M]);
			T = E * (L = C[M + 1]);
			P = E * (N = C[M + 2]);
			A += ae * F;
			B += ae * L;
			R += ae * N;
			Q = V;
			for (aj = 0; aj < E; aj++) {
				Q.r = F;
				Q.g = L;
				Q.b = N;
				Q = Q.next;
			}
			for (aj = 1; aj < E; aj++) {
				ag = M + ((I < aj ? I : aj) << 2);
				A += (Q.r = F = C[ag]) * (W = E - aj);
				B += (Q.g = L = C[ag + 1]) * W;
				R += (Q.b = N = C[ag + 2]) * W;
				X += F;
				ac += L;
				K += N;
				Q = Q.next;
			}
			ai = V;
			Z = D;
			for (ab = 0; ab < v; ab++) {
				C[M] = (A * O) >> z;
				C[M + 1] = (B * O) >> z;
				C[M + 2] = (R * O) >> z;
				A -= H;
				B -= T;
				R -= P;
				H -= ai.r;
				T -= ai.g;
				P -= ai.b;
				ag = (G + ((ag = ab + ad + 1) < I ? ag : I)) << 2;
				X += ai.r = C[ag];
				ac += ai.g = C[ag + 1];
				K += ai.b = C[ag + 2];
				A += X;
				B += ac;
				R += K;
				ai = ai.next;
				H += F = Z.r;
				T += L = Z.g;
				P += N = Z.b;
				X -= F;
				ac -= L;
				K -= N;
				Z = Z.next;
				M += 4;
			}
			G += v;
		}
		for (ab = 0; ab < v; ab++) {
			ac = K = X = B = R = A = 0;
			M = ab << 2;
			H = E * (F = C[M]);
			T = E * (L = C[M + 1]);
			P = E * (N = C[M + 2]);
			A += ae * F;
			B += ae * L;
			R += ae * N;
			Q = V;
			for (aj = 0; aj < E; aj++) {
				Q.r = F;
				Q.g = L;
				Q.b = N;
				Q = Q.next;
			}
			J = v;
			for (aj = 1; aj <= ad; aj++) {
				M = (J + ab) << 2;
				A += (Q.r = F = C[M]) * (W = E - aj);
				B += (Q.g = L = C[M + 1]) * W;
				R += (Q.b = N = C[M + 2]) * W;
				X += F;
				ac += L;
				K += N;
				Q = Q.next;
				if (aj < af) {
					J += v;
				}
			}
			M = ab;
			ai = V;
			Z = D;
			for (aa = 0; aa < w; aa++) {
				ag = M << 2;
				C[ag] = (A * O) >> z;
				C[ag + 1] = (B * O) >> z;
				C[ag + 2] = (R * O) >> z;
				A -= H;
				B -= T;
				R -= P;
				H -= ai.r;
				T -= ai.g;
				P -= ai.b;
				ag = (ab + ((ag = aa + E) < af ? ag : af) * v) << 2;
				A += X += ai.r = C[ag];
				B += ac += ai.g = C[ag + 1];
				R += K += ai.b = C[ag + 2];
				ai = ai.next;
				H += F = Z.r;
				T += L = Z.g;
				P += N = Z.b;
				X -= F;
				ac -= L;
				K -= N;
				Z = Z.next;
				M += v;
			}
		}
		am.putImageData(ah, U, S);
		return true;
	}
	function b() {
		this.r = 0;
		this.g = 0;
		this.b = 0;
		this.a = 0;
		this.next = null;
	}
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'glass_parallax',
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