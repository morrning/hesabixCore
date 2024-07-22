/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_cube(p, k, b) {
	var e = jQuery,
		j = e(this),
		a = /WOW Slider/g.test(navigator.userAgent),
		l = !/iPhone|iPod|iPad|Android|BlackBerry/.test(navigator.userAgent) && !a,
		g = e('.ws_list', b),
		c = p.perspective || 2000,
		d = {
			position: 'absolute',
			backgroundSize: 'cover',
			right: 0,
			top: 0,
			width: '100%',
			height: '100%',
			backfaceVisibility: 'hidden',
		};
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
		webkit: function () {
			return (
				/AppleWebKit/.test(navigator.userAgent) &&
				!/Chrome/.test(navigator.userAgent)
			);
		},
	};
	var f = o.cssTransitions() && o.cssTransforms3d(),
		m = o.webkit();
	var i = e('<div>')
		.css(d)
		.css({
			transformStyle: 'preserve-3d',
			perspective: m && !a ? 'none' : c,
			zIndex: 8,
		});
	e('<div>').addClass('ws_effect ws_cube').css(d).append(i).appendTo(b);
	if (!f && p.fallback) {
		return new p.fallback(p, k, b);
	}
	function n(q, r, t, s) {
		return (
			'inset ' +
			(-s * q * 1.2) / 90 +
			'px ' +
			(t * r * 1.2) / 90 +
			'px ' +
			(q + r) / 20 +
			'px rgba(' +
			(t < s ? '0,0,0,.6' : t > s ? '255,255,255,0.8' : '0,0,0,.0') +
			')'
		);
	}
	var h;
	this.go = function (B, y) {
		var t = e(k[y]);
		t = {
			width: t.width(),
			height: t.height(),
			marginTop: parseFloat(t.css('marginTop')),
			marginRight: parseFloat(t.css('marginRight')),
		};
		function s(S, F, H, I, G, E, Q, R, P, O) {
			S.parent().css('perspective', c);
			var N = S.width(),
				K = S.height();
			F.front.css({
				transform: 'translate3d(0,0,0) rotateY(0deg) rotateX(0deg)',
			});
			F.back.css({
				opacity: 1,
				transform:
					'translate3d(0,0,0) rotateY(' + Q + 'deg) rotateX(' + E + 'deg)',
			});
			if (l) {
				var J = e('<div>')
					.css(d)
					.css('boxShadow', n(N, K, 0, 0))
					.appendTo(F.front);
				var M = e('<div>')
					.css(d)
					.css('boxShadow', n(N, K, E, Q))
					.appendTo(F.back);
			}
			if (m && !/WOW Slider/g.test(navigator.userAgent)) {
				S.css({ transform: 'translateZ(-' + H + 'px)' });
			}
			var L = setTimeout(function () {
				var w =
					'all ' + p.duration + 'ms cubic-bezier(0.645, 0.045, 0.355, 1.000)';
				F.front.css({
					transition: w,
					boxShadow: l ? n(N, K, R, P) : '',
					transform: 'rotateX(' + R + 'deg) rotateY(' + P + 'deg)',
					zIndex: 0,
				});
				F.back.css({
					transition: w,
					boxShadow: l ? n(N, K, 0, 0) : '',
					transform: 'rotateY(0deg) rotateX(0deg)',
					zIndex: 20,
				});
				if (l) {
					J.css({ transition: w, boxShadow: n(N, K, R, P) });
					M.css({ transition: w, boxShadow: n(N, K, 0, 0) });
				}
				L = setTimeout(O, p.duration);
			}, 20);
			return {
				stop: function () {
					clearTimeout(L);
					O();
				},
			};
		}
		if (f) {
			if (h) {
				h.stop();
			}
			var C = b.width(),
				z = b.height();
			var x = {
				right: [C / 2, C / 2, 0, 0, 90, 0, -90],
				left: [C / 2, -C / 2, 0, 0, -90, 0, 90],
				down: [z / 2, 0, -z / 2, 90, 0, -90, 0],
				up: [z / 2, 0, z / 2, -90, 0, 90, 0],
			}[
				p.direction ||
					['left', 'right', 'down', 'up'][Math.floor(Math.random() * 4)]
			];
			var D = e('<img>').css(t),
				v = e('<img>').css(t).attr('src', k.get(B).src);
			var q = e('<div>')
				.css({
					overflow: 'hidden',
					transformOrigin: '50% 50% -' + x[0] + 'px',
					zIndex: 20,
				})
				.css(d)
				.append(D)
				.appendTo(i);
			var r = e('<div>')
				.css({
					overflow: 'hidden',
					transformOrigin: '50% 50% -' + x[0] + 'px',
					zIndex: 0,
				})
				.css(d)
				.append(v)
				.appendTo(i);
			D.on('load', function () {
				g.hide();
			});
			D.attr('src', k.get(y).src).load();
			i.parent().show();
			h = new s(
				i,
				{ front: q, back: r },
				x[0],
				x[1],
				x[2],
				x[3],
				x[4],
				x[5],
				x[6],
				function () {
					j.trigger('effectEnd');
					i.empty().parent().hide();
					h = 0;
				}
			);
		} else {
			i.css({
				position: 'absolute',
				display: 'none',
				zIndex: 2,
				width: '100%',
				height: '100%',
			});
			i.stop(1, 1);
			var u = !!((B - y + 1) % k.length) ^ p.revers ? 'right' : 'left';
			var q = e('<div>')
				.css({
					position: 'absolute',
					right: '0%',
					left: 'auto',
					top: 0,
					width: '100%',
					height: '100%',
				})
				.css(u, 0)
				.append(
					e(k[y])
						.clone()
						.css({
							width: (100 * t.width) / b.width() + '%',
							height: (100 * t.height) / b.height() + '%',
							marginRight: (100 * t.marginRight) / b.width() + '%',
						})
				)
				.appendTo(i);
			var A = e('<div>')
				.css({
					position: 'absolute',
					right: '100%',
					left: 'auto',
					top: 0,
					width: '0%',
					height: '100%',
				})
				.append(
					e(k[B])
						.clone()
						.css({
							width: (100 * t.width) / b.width() + '%',
							height: (100 * t.height) / b.height() + '%',
							marginRight: (100 * t.marginRight) / b.width() + '%',
						})
				)
				.appendTo(i);
			i.css({ left: 'auto', right: 'auto', top: 0 }).css(u, 0).show();
			i.show();
			g.hide();
			A.animate(
				{ width: '100%', right: 0 },
				p.duration,
				'easeInOutExpo',
				function () {
					e(this).remove();
				}
			);
			q.animate({ width: 0 }, p.duration, 'easeInOutExpo', function () {
				j.trigger('effectEnd');
				i.empty().hide();
			});
		}
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'cube',
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