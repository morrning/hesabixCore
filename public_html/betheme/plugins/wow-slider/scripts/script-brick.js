/**
 * WOWslider
 *
 * http://wowslider.com
 */
jQuery.extend(jQuery.easing, {
	easeInBack: function (e, f, a, i, h, g) {
		if (g == undefined) {
			g = 1.70158;
		}
		return i * (f /= h) * f * ((g + 1) * f - g) + a;
	},
	easeOutBack: function (e, f, a, i, h, g) {
		if (g == undefined) {
			g = 1.70158;
		}
		return i * ((f = f / h - 1) * f * ((g + 1) * f + g) + 1) + a;
	},
	easeInBackQ: function (e, f, a, j, i, g) {
		var h = (f /= i) * f;
		return a + j * h * (4 * f * h - 8 * h + 8 * f - 3);
	},
	easeOutBackQ: function (e, f, a, j, i, g) {
		var h = (f /= i) * f;
		return a + j * (4 * h * f * h - 12 * h * h + 16 * h * f - 13 * h + 6 * f);
	},
	easeInBackQ2: function (e, f, a, j, i, g) {
		var h = (f /= i) * f;
		return a + j * h * (1.5 * f * h - 2.5 * h + 5 * f - 3);
	},
	easeOutBackQ2: function (e, f, a, j, i, g) {
		var h = (f /= i) * f;
		return (
			a + j * (1.5 * h * f * h - 5 * h * h + 10 * h * f - 12 * h + 6.5 * f)
		);
	},
});
function ws_brick(f, s, g) {
	var h = jQuery,
		n = h(this),
		a = f.cols || 4,
		r = f.rows || 3,
		H = 2.5,
		c = 2,
		v = f.perspective || 2000,
		u = g.find('.ws_list'),
		G = [],
		b = 30,
		x = {},
		o = h('<div>').addClass('ws_effect ws_brick').appendTo(g),
		q = f.support.transform && f.support.transition && f.support.perspective,
		p =
			/Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent),
		m = /Firefox/.test(navigator.userAgent);
	var y = [
		{
			zIndex: 0,
			rotateX: 360,
			rotateZ: -360,
			rotateY: 180,
			halfScale: 0.5,
			halfLeft: 0.7,
			halfTop: 0.7,
			delay: 0.36,
		},
		{
			zIndex: 1,
			rotateX: -360,
			rotateZ: 360,
			rotateY: 180,
			halfScale: 0.5,
			halfLeft: 0.2,
			halfTop: 0.4,
			delay: 0.81,
		},
		{
			zIndex: 1,
			rotateX: 360,
			rotateZ: -360,
			rotateY: -180,
			halfScale: 0.5,
			halfLeft: -0.2,
			halfTop: 0.4,
			delay: 0.45,
		},
		{
			zIndex: 0,
			rotateX: -360,
			rotateZ: 360,
			rotateY: -180,
			halfScale: 0.5,
			halfLeft: -0.7,
			halfTop: 0.7,
			delay: 0.63,
		},
		{
			zIndex: 1,
			rotateX: -360,
			rotateZ: 360,
			rotateY: -180,
			halfScale: 0.5,
			halfLeft: 0.7,
			halfTop: 0,
			delay: 0.54,
		},
		{
			zIndex: 2,
			rotateX: 360,
			rotateZ: -360,
			rotateY: 180,
			halfScale: 0.5,
			halfLeft: 0.2,
			halfTop: 0,
			delay: 0.38,
		},
		{
			zIndex: 2,
			rotateX: 360,
			rotateZ: -360,
			rotateY: -180,
			halfScale: 0.5,
			halfLeft: -0.2,
			halfTop: 0,
			delay: 0,
		},
		{
			zIndex: 1,
			rotateX: -360,
			rotateZ: 360,
			rotateY: 180,
			halfScale: 0.5,
			halfLeft: -0.7,
			halfTop: 0,
			delay: 0.72,
		},
		{
			zIndex: 0,
			rotateX: -360,
			rotateZ: 360,
			rotateY: 180,
			halfScale: 0.5,
			halfLeft: 0.7,
			halfTop: -0.7,
			delay: 1,
		},
		{
			zIndex: 1,
			rotateX: -360,
			rotateZ: 360,
			rotateY: -180,
			halfScale: 0.5,
			halfLeft: 0.2,
			halfTop: -0.4,
			delay: 0.7,
		},
		{
			zIndex: 1,
			rotateX: 360,
			rotateZ: -360,
			rotateY: 180,
			halfScale: 0.5,
			halfLeft: -0.2,
			halfTop: -0.4,
			delay: 0.57,
		},
		{
			zIndex: 0,
			rotateX: 360,
			rotateZ: -360,
			rotateY: -180,
			halfScale: 0.5,
			halfLeft: -0.7,
			halfTop: -0.7,
			delay: 0.9,
		},
	];
	o.css({
		position: 'absolute',
		top: 0,
		right: 0,
		width: g.width(),
		height: g.height(),
		transform: 'translate3d(0,0,0)',
		transformOrigin: f.width / 2 + 'px ' + f.height / 2 + 'px 0',
		perspective: v,
	}).hide();
	for (var l = 0; l < a * r; l++) {
		var B = l % a,
			A = Math.floor(l / a);
		var E = h('<div>')
				.css({
					position: 'absolute',
					right: (100 * B) / a + '%',
					top: (100 * A) / r + '%',
					outline: '1px solid transparent',
					transformStyle: p || m ? 'flat' : 'preserve-3d',
					zIndex: y[l].zIndex,
					overflow: q ? 'visible' : 'hidden',
				})
				.appendTo(o),
			z = h('<div>')
				.css({
					transform: 'scale(1) rotateX(0) rotateY(0) translate3d(0,0,0)',
					outline: '1px solid transparent',
					transformStyle: 'preserve-3d',
				})
				.appendTo(E),
			w = h('<div>').addClass('ws_front_image').appendTo(z),
			D = q ? h('<div>').addClass('ws_back_image').appendTo(z) : 0;
		w.css({
			position: 'absolute',
			width: '100%',
			height: '100%',
			overflow: 'hidden',
			backfaceVisibility: 'hidden',
			transform: 'translate3d(0,0,0)',
		}).append(
			h('<img>').css({
				right: -B * 100 + '%',
				top: -A * 100 + '%',
				position: 'absolute',
				outline: '1px solid transparent',
			})
		);
		if (q) {
			D.css({
				position: 'absolute',
				width: '100%',
				height: '100%',
				overflow: 'hidden',
				backfaceVisibility: 'hidden',
				transform: 'rotateY(180deg) translate3d(0,0,' + b + 'px)',
			}).append(
				h('<img>').css({
					right: -B * 100 + '%',
					top: -A * 100 + '%',
					position: 'absolute',
					outline: '1px solid transparent',
				})
			);
		}
		var t = { position: 'absolute', outline: '1px solid transparent' };
		G[l] = {
			part: z,
			front: w,
			back: D,
			wrapper: E,
			leftEdge: q ? h('<div>').addClass('ws_left_edge').css(t).appendTo(z) : 0,
			rightEdge: q
				? h('<div>').addClass('ws_right_edge').css(t).appendTo(z)
				: 0,
			topEdge: q ? h('<div>').addClass('ws_top_edge').css(t).appendTo(z) : 0,
			bottomEdge: q
				? h('<div>').addClass('ws_bottom_edge').css(t).appendTo(z)
				: 0,
		};
	}
	function C(N) {
		var J = {},
			L = s.get(N),
			O = f.width / a,
			P = f.height / r;
		for (var K = 0; K < a * r; K++) {
			var N = K % a,
				M = Math.floor(K / a);
			J[K] = F(L, { x: N * O, y: M * P, w: O, h: P });
		}
		return J;
	}
	function I(J, M, j, K, L) {
		for (var i in M) {
			if (typeof G[i] !== 'function') {
				M[i].topEdge.css({
					width: K,
					height: J,
					background: j[i],
					transform:
						'rotateX(90deg) translate3d(0,-' + J / 2 + 'px,' + J / 2 + 'px)',
				});
				M[i].bottomEdge.css({
					width: K,
					height: J,
					background: j[i],
					transform:
						'rotateX(90deg) translate3d(0,-' +
						J / 2 +
						'px,' +
						(-L + J / 2) +
						'px)',
				});
				M[i].rightEdge.css({
					width: J,
					height: L,
					background: j[i],
					transform:
						'rotateY(90deg) translate3d(' + J / 2 + 'px,0,-' + (K - J / 2) + 'px)',
				});
				M[i].leftEdge.css({
					width: J,
					height: L,
					background: j[i],
					transform:
						'rotateY(90deg) translate3d(' + J / 2 + 'px,0,' + J / 2 + 'px)',
				});
			}
		}
	}
	function e(J, K) {
		var i = 0;
		for (var j in J) {
			if (typeof J[j] !== 'function') {
				(function (L, M) {
					wowAnimate(
						function (O) {
							var U,
								S,
								T,
								R = '',
								N = {};
							if (O <= 0.5) {
								U = h.easing.easeInBack(1, O * 2, 0, 1, 1, 1).toFixed(3);
								S = h.easing.easeInBackQ(1, O * 2, 0, 1, 1, 1).toFixed(3);
								T = h.easing.easeInBackQ2(1, O * 2, 0, 1, 1, 1).toFixed(3);
								M[L].back.css('backfaceVisibility', 'hidden');
							} else {
								U = h.easing
									.easeOutBack(1, (O - 0.5) * 2, 0, 1, 1, 1)
									.toFixed(3);
								S = h.easing
									.easeOutBackQ(1, (O - 0.5) * 2, 0, 1, 1, 1)
									.toFixed(3);
								T = h.easing
									.easeOutBackQ2(1, (O - 0.5) * 2, 0, 1, 1, 1)
									.toFixed(3);
								M[L].back.css('backfaceVisibility', 'visible');
							}
							for (var P in M[L].animate[O <= 0.5 ? 'half' : 'end']) {
								var V = M[L].animate[O <= 0.5 ? 'begin' : 'half'][P] || 0,
									Q = M[L].animate[O <= 0.5 ? 'half' : 'end'][P] || 0;
								if (typeof Q !== 'object') {
									if (P === 'scale' || P === 'rotateX' || P === 'rotateY') {
										Q = V + (Q - V) * S;
									} else {
										if (P === 'left' || P === 'top') {
											Q = V + (Q - V) * T;
										} else {
											Q = V + (Q - V) * U;
										}
									}
								}
								if (P === 'rotateX' || P === 'rotateY' || P === 'rotateZ') {
									R += P + '(' + Q + 'deg) ';
								} else {
									if (P === 'scale') {
										R += P + '(' + Q + ') ';
									} else {
										if (P === 'translate3d') {
											R +=
												P +
												'(' +
												(V[0] + (Q[0] - V[0]) * U).toFixed(3) +
												'px,' +
												(V[1] + (Q[1] - V[1]) * U).toFixed(3) +
												'px,' +
												(V[2] + (Q[2] - V[2]) * U).toFixed(3) +
												'px) ';
										} else {
											N[P] = Q;
										}
									}
								}
							}
							M[L].wrapper.css({
								transform:
									'translate3d(' +
									(N.right ? N.right : 0).toFixed(3) +
									'px,' +
									(N.top ? N.top : 0).toFixed(3) +
									'px,0)',
							});
							delete N.right;
							delete N.top;
							if (R) {
								N.transform = R;
							}
							M[L].part.css(N);
						},
						0,
						1,
						M[L].animate.duration,
						M[L].animate.delay,
						function () {
							i++;
							if (i == M.length && K) {
								K();
							}
						}
					);
				})(j, J);
			}
		}
	}
	function k(aa, M, N, P) {
		var X = g.width(),
			W = g.height(),
			V = X / a,
			U = W / r,
			L = f.duration * 0.4 > 1000 ? 1000 : f.duration * 0.4,
			K = f.duration * 0.6,
			Q = [0, 0];
		I(b, aa, x[M], V, U);
		o.css({
			transformOrigin: X / 2 + 'px ' + W / 2 + 'px 0',
			width: X,
			height: W,
		});
		for (var S in aa) {
			if (typeof aa[S] !== 'function') {
				var J = y[S].delay * L;
				if (Q[1] <= J) {
					Q[0] = S;
					Q[1] = J;
				}
				aa[S].part[0].ws_delay = [J, 0];
			}
		}
		aa[Q[0]].part[0].ws_delay[1] = 1;
		for (var S in aa) {
			if (typeof aa[S] !== 'function') {
				var R = aa[S],
					Z = S % a,
					Y = Math.floor(S / a),
					T = (X * Z) / a,
					O = (W * Y) / r;
				R.animate = {
					delay: R.part[0].ws_delay[0],
					duration: K,
					begin: {
						right: 0,
						top: 0,
						width: V,
						height: U,
						scale: 1,
						rotateX: 0,
						rotateY: 0,
						translate3d: [0, 0, p ? b : 0],
					},
					half: {
						right: y[S].halfLeft * V,
						top: y[S].halfTop * U,
						scale: y[S].halfScale,
						rotateX: y[S].rotateX / 2,
						rotateY: y[S].rotateY / 2,
						translate3d: [0, 0, (p ? 1 : 0.5) * b],
					},
					end: {
						right: 0,
						top: 0,
						scale: 1,
						rotateX: y[S].rotateX,
						rotateY: y[S].rotateY,
						translate3d: [0, 0, b],
					},
				};
				R.front.find('img').css(N);
				R.back.css('backfaceVisibility', 'hidden').find('img').css(N);
				R.part.css({
					width: R.animate.begin.width,
					height: R.animate.begin.height,
					right: R.animate.begin.right,
					top: R.animate.begin.top,
				});
			}
		}
		e(aa, P);
	}
	var d;
	this.go = function (X, M) {
		if (d) {
			return M;
		}
		o.show();
		var K = h(s.get(M));
		K = {
			width: K.width(),
			height: K.height(),
			marginTop: parseFloat(K.css('marginTop')),
			marginRight: parseFloat(K.css('marginRight')),
		};
		if (q) {
			G[0].front.find('img').on('load', function () {
				u.hide();
			});
			for (var N in G) {
				if (typeof G[N] !== 'function') {
					G[N].front.find('img').attr('src', s.get(M).src);
					G[N].back.find('img').attr('src', s.get(X).src);
				}
			}
			if (!x[M]) {
				x[M] = C(M);
			}
			d = new k(G, M, K, function () {
				u.show();
				n.trigger('effectEnd');
				o.hide();
				for (var i in G) {
					if (typeof G[i] !== 'function') {
						G[i].part.css({
							transition: '',
							transform: 'rotateX(0) rotateY(0) translate3d(0,0,0)',
						});
					}
				}
				d = 0;
			});
		} else {
			d = true;
			function Y(j, i) {
				return Math.random() * (i - j + 1) + j;
			}
			var T = g.width(),
				W = g.height(),
				S = T / a,
				V = W / r,
				L = T - S * (a - 1),
				U = W - V * (r - 1);
			o.css({ width: T, height: W });
			var J = 0;
			for (var N in G) {
				var R = N % a,
					P = Math.floor(N / a);
				G[N].front.find('img').attr('src', s.get(X).src).css(K);
				var Z = f.duration * (1 - Math.abs((c * H - R * P) / (2 * r * a)));
				var Q = Y(-1, 1) > 0 ? 1 : -1;
				var O = Y(-1, 1) > 0 ? 1 : -1;
				G[N].wrapper.css({ width: S, height: V });
				G[N].part
					.css({
						position: 'absolute',
						top: Q * V,
						right: O * S,
						opacity: 0,
						width: S,
						height: V,
					})
					.animate({ top: 0, right: 0, opacity: 1 }, Z, function () {
						J++;
						if (J == r * a) {
							u.stop(1, 1);
							d = false;
							n.trigger('effectEnd');
						}
					});
			}
		}
	};
	function F(S, J) {
		J = J || {};
		var U = 1,
			M = J.exclude || [],
			R;
		var O = document.createElement('canvas'),
			L = O.getContext('2d'),
			K = (O.width = S.naturalWidth),
			Y = (O.height = S.naturalHeight);
		L.drawImage(S, 0, 0, S.naturalWidth, S.naturalHeight);
		try {
			R = L.getImageData(
				J.x ? J.x : 0,
				J.y ? J.y : 0,
				J.w ? J.w : S.width,
				J.h ? J.h : S.height
			)['data'];
		} catch (T) {
			console.log('error:unable to access image data: ' + T);
			return '#ccc';
		}
		var N = (J.w ? J.w : S.width * J.h ? J.h : S.height) || R.length,
			P = {},
			W = '',
			V = [],
			j = { dominant: { name: '', count: 0 } };
		var Q = 0;
		while (Q < N) {
			V[0] = R[Q];
			V[1] = R[Q + 1];
			V[2] = R[Q + 2];
			W = V.join(',');
			if (W in P) {
				P[W] = P[W] + 1;
			} else {
				P[W] = 1;
			}
			if (M.indexOf(['rgb(', W, ')'].join('')) === -1) {
				var X = P[W];
				if (X > j.dominant.count) {
					j.dominant.name = W;
					j.dominant.count = X;
				}
			}
			Q += U * 4;
		}
		return ['rgb(', j.dominant.name, ')'].join('');
	}
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'brick',
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