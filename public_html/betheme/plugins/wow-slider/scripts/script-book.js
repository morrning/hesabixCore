/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_book(p, n, b) {
	var f = jQuery;
	var m = f(this);
	var i = f('.ws_list', b);
	var k = f('<div>')
			.addClass('ws_effect ws_book')
			.css({
				position: 'absolute',
				top: 0,
				right: 0,
				width: '100%',
				height: '100%',
			})
			.appendTo(b),
		e = p.duration,
		d = p.perspective || 0.4,
		g = p.shadow || 0.35,
		a = p.noCanvas || false,
		l = p.no3d || false;
	var o = {
		domPrefixes: ' Webkit Moz ms O Khtml'.split(' '),
		testDom: function (r) {
			var q = this.domPrefixes.length;
			while (q--) {
				if (
					typeof document.body.style[this.domPrefixes[q] + r] !== 'undefined'
				) {
					return true;
				}
			}
			return false;
		},
		cssTransitions: function () {
			return this.testDom('Transition');
		},
		cssTransforms3d: function () {
			var r =
				typeof document.body.style.perspectiveProperty !== 'undefined' ||
				this.testDom('Perspective');
			if (r && /AppleWebKit/.test(navigator.userAgent)) {
				var t = document.createElement('div'),
					q = document.createElement('style'),
					s = 'Test3d' + Math.round(Math.random() * 99999);
				q.textContent = '@media (-webkit-transform-3d){#' + s + '{height:3px}}';
				document.getElementsByTagName('head')[0].appendChild(q);
				t.id = s;
				document.body.appendChild(t);
				r = t.offsetHeight === 3;
				q.parentNode.removeChild(q);
				t.parentNode.removeChild(t);
			}
			return r;
		},
		canvas: function () {
			if (typeof document.createElement('canvas').getContext !== 'undefined') {
				return true;
			}
		},
	};
	if (!l) {
		l = o.cssTransitions() && o.cssTransforms3d();
	}
	if (!a) {
		a = o.canvas();
	}
	var j;
	this.go = function (r, q, E) {
		if (j) {
			return -1;
		}
		var v = n.get(r),
			G = n.get(q);
		if (E == undefined) {
			E = (q == 0 && r != q + 1) || r == q - 1;
		} else {
			E = !E;
		}
		var s = f('<div>').appendTo(k);
		var t = f(v);
		t = {
			width: t.width(),
			height: t.height(),
			marginRight: parseFloat(t.css('marginRight')),
			marginTop: parseFloat(t.css('marginTop')),
		};
		if (l) {
			var y = {
				background: '#000',
				position: 'absolute',
				right: 0,
				top: 0,
				width: '100%',
				height: '100%',
				transformStyle: 'preserve-3d',
				zIndex: 3,
				outline: '1px solid transparent',
			};
			perspect = b.width() * (3 - d * 2);
			s.css(y).css({ perspective: perspect, transform: 'translate3d(0,0,0)' });
			var z = 90;
			var D = f('<div>')
				.css(y)
				.css({
					position: 'relative',
					float: 'right',
					width: '50%',
					overflow: 'hidden',
				})
				.append(
					f('<img>')
						.attr('src', (E ? v : G).src)
						.css(t)
				)
				.appendTo(s);
			var C = f('<div>')
				.css(y)
				.css({
					position: 'relative',
					float: 'right',
					width: '50%',
					overflow: 'hidden',
				})
				.append(
					f('<img>')
						.attr('src', (E ? G : v).src)
						.css(t)
						.css({ marginRight: -t.width / 2 })
				)
				.appendTo(s);
			var I = f('<div>')
				.css(y)
				.css({
					display: E ? 'block' : 'none',
					width: '50%',
					transform: 'rotateY(-' + (E ? 0.1 : z) + 'deg)',
					transition: (E ? 'ease-in ' : 'ease-out ') + e / 2000 + 's',
					transformOrigin: 'left',
					overflow: 'hidden',
				})
				.append(
					f('<img>')
						.attr('src', (E ? G : v).src)
						.css(t)
				)
				.appendTo(s);
			var F = f('<div>')
				.css(y)
				.css({
					display: E ? 'none' : 'block',
					right: '50%',
					width: '50%',
					transform: 'rotateY(' + (E ? z : 0.1) + 'deg)',
					transition: (E ? 'ease-out ' : 'ease-in ') + e / 2000 + 's',
					transformOrigin: 'right',
					overflow: 'hidden',
				})
				.append(
					f('<img>')
						.attr('src', (E ? v : G).src)
						.css(t)
						.css({ marginRight: -t.width / 2 })
				)
				.appendTo(s);
		} else {
			if (a) {
				var x = f('<div>')
					.css({
						position: 'absolute',
						top: 0,
						right: E ? 0 : '50%',
						width: '50%',
						height: '100%',
						overflow: 'hidden',
						zIndex: 6,
					})
					.append(
						f(n.get(r))
							.clone()
							.css({
								position: 'absolute',
								height: '100%',
								left: E ? 'auto' : 0,
								right: E ? 0 : 'auto',
							})
					)
					.appendTo(s)
					.hide();
				var B = f('<div>')
					.css({
						position: 'absolute',
						width: '100%',
						height: '100%',
						right: 0,
						top: 0,
						zIndex: 8,
					})
					.appendTo(s)
					.hide();
				var H = f('<canvas>')
					.css({
						position: 'absolute',
						zIndex: 2,
						right: 0,
						top: (-B.height() * d) / 2,
					})
					.attr({ width: B.width(), height: B.height() * (d + 1) })
					.appendTo(B);
				var A = H.clone()
					.css({ top: 0, zIndex: 1 })
					.attr({ width: B.width(), height: B.height() })
					.appendTo(B);
				var w = H.get(0).getContext('2d');
				var u = A.get(0).getContext('2d');
			} else {
				i.stop(true).animate(
					{
						right: r
							? -r + '00%'
							: /Safari/.test(navigator.userAgent)
							? '0%'
							: 0,
					},
					e,
					'easeInOutExpo'
				);
			}
		}
		if (!l && a) {
			var D = w;
			var C = u;
			var I = G;
			var F = v;
		}
		j = new h(E, z, D, C, I, F, B, H, A, x, t, function () {
			m.trigger('effectEnd');
			s.remove();
			j = 0;
		});
	};
	function c(G, s, A, v, u, E, D, C, B, t, r) {
		(numSlices = u / 2),
			(widthScale = u / B),
			(heightScale = (1 - E) / numSlices);
		G.clearRect(0, 0, r.width(), r.height());
		for (var q = 0; q < numSlices + widthScale; q++) {
			var z = D
				? (q * p.width) / u + p.width / 2
				: ((numSlices - q) * p.width) / u;
			var H = A + (D ? 2 : -2) * q,
				F = v + (t * heightScale * q) / 2;
			if (z < 0) {
				z = 0;
			}
			if (H < 0) {
				H = 0;
			}
			if (F < 0) {
				F = 0;
			}
			G.drawImage(s, z, 0, 2.5, p.height, H, F, 2, t * (1 - heightScale * q));
		}
		G.save();
		G.beginPath();
		G.moveTo(A, v);
		G.lineTo(
			A + (D ? 2 : -2) * (numSlices + widthScale),
			v + (t * heightScale * (numSlices + widthScale)) / 2
		);
		G.lineTo(
			A + (D ? 2 : -2) * (numSlices + widthScale),
			t * (1 - heightScale * (numSlices + widthScale)) +
				v +
				(t * heightScale * (numSlices + widthScale)) / 2
		);
		G.lineTo(A, v + t);
		G.closePath();
		G.clip();
		G.fillStyle = 'rgba(0,0,0,' + Math.round(C * 100) / 100 + ')';
		G.fillRect(0, 0, r.width(), r.height());
		G.restore();
	}
	function h(A, r, C, B, y, x, v, w, u, z, t, E) {
		if (l) {
			if (!A) {
				r *= 1;
				var D = B;
				B = C;
				C = D;
				D = x;
				x = y;
				y = D;
			}
			setTimeout(function () {
				C.children('img')
					.css('opacity', g)
					.animate({ opacity: 1 }, e / 2);
				y.css('transform', 'rotateY(' + r + 'deg)')
					.children('img')
					.css('opacity', 1)
					.animate({ opacity: g }, e / 2, function () {
						y.hide();
						x.show()
							.css('transform', 'rotateX(0deg) rotateY(0deg)')
							.children('img')
							.css('opacity', g)
							.animate({ opacity: 1 }, e / 2);
						B.children('img')
							.css('opacity', 1)
							.animate({ opacity: g }, e / 2);
					});
			}, 0);
			setTimeout(E, e);
		} else {
			if (a) {
				v.show();
				var q = new Date();
				var s = true;
				wowAnimate(
					function (F) {
						var I = jQuery.easing.easeInOutQuint(1, F, 0, 1, 1),
							H = jQuery.easing.easeInOutCubic(1, F, 0, 1, 1),
							L = !A;
						if (F < 0.5) {
							I *= 2;
							H *= 2;
							var G = y;
						} else {
							L = A;
							I = (1 - I) * 2;
							H = (1 - H) * 2;
							var G = x;
						}
						var J = (v.height() * d) / 2,
							N = ((1 - I) * v.width()) / 2,
							M = 1 + H * d,
							K = v.width() / 2;
						c(C, G, K, J, N, M, L, H * g, K, v.height(), w);
						if (s) {
							z.show();
							s = false;
						}
						B.clearRect(0, 0, u.width(), u.height());
						B.fillStyle = 'rgba(0,0,0,' + (g - H * g) + ')';
						B.fillRect(L ? K : 0, 0, u.width() / 2, u.height());
					},
					0,
					1,
					e,
					E
				);
			}
		}
	}
}
jQuery.extend(jQuery.easing, {
	easeInOutCubic: function (e, f, a, h, g) {
		if ((f /= g / 2) < 1) {
			return (h / 2) * f * f * f + a;
		}
		return (h / 2) * ((f -= 2) * f * f + 2) + a;
	},
	easeInOutQuint: function (e, f, a, h, g) {
		if ((f /= g / 2) < 1) {
			return (h / 2) * f * f * f * f * f + a;
		}
		return (h / 2) * ((f -= 2) * f * f * f * f + 2) + a;
	},
});

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'book',
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