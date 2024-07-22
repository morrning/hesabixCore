/**
 * WOWslider
 *
 * http://wowslider.com
 */
jQuery.extend(jQuery.easing, {
	easeInOutBack: function (e, f, a, i, h, g) {
		if (g == undefined) {
			g = 1.70158;
		}
		if ((f /= h / 2) < 1) {
			return (i / 2) * (f * f * (((g *= 1.525) + 1) * f - g)) + a;
		}
		return (i / 2) * ((f -= 2) * f * (((g *= 1.525) + 1) * f + g) + 2) + a;
	},
});
function ws_cube_over(m, k, b) {
	var e = jQuery,
		j = e(this),
		a = /WOW Slider/g.test(navigator.userAgent),
		g = e('.ws_list', b),
		c =
			m.perspective || /MSIE|Trident/g.test(navigator.userAgent) ? 1000 : 2000,
		d = {
			position: 'absolute',
			backgroundSize: 'cover',
			right: 0,
			top: 0,
			width: '100%',
			height: '100%',
			backfaceVisibility: 'hidden',
		};
	var l =
		/AppleWebKit/.test(navigator.userAgent) &&
		!/Chrome/.test(navigator.userAgent);
	var i = e('<div>')
		.css(d)
		.css({
			transformStyle: 'preserve-3d',
			perspective: l && !a ? 'none' : c,
			zIndex: 8,
		});
	e('<div>').addClass('ws_effect ws_cube_over').css(d).append(i).appendTo(b);
	if (!m.support.transform && m.fallback) {
		return new m.fallback(m, k, b);
	}
	var h;
	this.go = function (y, u) {
		var q = e(k[u]);
		q = {
			width: q.width(),
			height: q.height(),
			marginTop: parseFloat(q.css('marginTop')),
			marginRight: parseFloat(q.css('marginRight')),
		};
		function p(C, G, F, H) {
			C.parent().css('perspective', c);
			var D = C.width(),
				E = C.height();
			wowAnimate(
				C,
				{ scale: 1, translate: [0, 0, l && !a ? F : 0] },
				{ scale: 0.85, translate: [0, 0, l && !a ? F : 0] },
				m.duration * 0.4,
				'easeInOutBack',
				function () {
					wowAnimate(
						C,
						{ scale: 0.85, translate: [0, 0, l && !a ? F : 0] },
						{ scale: 1, translate: [0, 0, l && !a ? F : 0] },
						m.duration * 0.4,
						m.duration - m.duration * 0.8,
						'easeInOutBack',
						H
					);
				}
			);
			wowAnimate(
				G.front.item,
				{ rotateY: 0, rotateX: 0 },
				{ rotateY: G.front.rotateY, rotateX: G.front.rotateX },
				m.duration,
				'easeInOutBack'
			);
			wowAnimate(
				G.back.item,
				{ rotateY: G.back.rotateY, rotateX: G.back.rotateX },
				{ rotateY: 0, rotateX: 0 },
				m.duration,
				'easeInOutBack'
			);
			wowAnimate(
				G.side.item,
				{ rotateY: G.side.rotateY, rotateX: G.side.rotateX },
				{ rotateY: -G.side.rotateY, rotateX: -G.side.rotateX },
				m.duration,
				'easeInOutBack'
			);
		}
		if (m.support.transform && m.support.perspective) {
			if (h) {
				h.stop();
			}
			var A = b.width(),
				v = b.height();
			var t = {
				right: [A / 2, 0, 0, 180, 0, -180],
				left: [A / 2, 0, 0, -180, 0, 180],
				down: [v / 2, -v / 2, 180, 0, -180, 0],
				up: [v / 2, v / 2, -180, 0, 180, 0],
			}[
				m.direction ||
					['left', 'right', 'down', 'up'][Math.floor(Math.random() * 4)]
			];
			var B = e('<img>').css(q),
				s = e('<img>').css(q).attr('src', k.get(y).src);
			var n = e('<div>')
				.css({ overflow: 'hidden', transformOrigin: '50% 50% -' + t[0] + 'px' })
				.css(d)
				.append(B)
				.appendTo(i);
			var o = e('<div>')
				.css({ overflow: 'hidden', transformOrigin: '50% 50% -' + t[0] + 'px' })
				.css(d)
				.append(s)
				.appendTo(i);
			var z = e('<div class="ws_cube_side">')
				.css({
					transformOrigin: '50% 50% -' + t[0] + 'px',
					background: m.staticColor
						? ''
						: f(s[0], { exclude: ['0,0,0', '255,255,255'] }),
				})
				.css(d)
				.appendTo(i);
			B.on('load', function () {
				g.hide();
			});
			B.attr('src', k.get(u).src).load();
			i.parent().show();
			h = new p(
				i,
				{
					front: { item: n, rotateY: t[5], rotateX: t[4] },
					back: { item: o, rotateY: t[3], rotateX: t[2] },
					side: { item: z, rotateY: t[3] / 2, rotateX: t[2] / 2 },
				},
				-t[0],
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
			var r = !!((y - u + 1) % k.length) ^ m.revers ? 'right' : 'left';
			var n = e('<div>')
				.css({
					position: 'absolute',
					right: '0%',
					left: 'auto',
					top: 0,
					width: '100%',
					height: '100%',
				})
				.css(r, 0)
				.append(
					e(k[u])
						.clone()
						.css({
							width: (100 * q.width) / b.width() + '%',
							height: (100 * q.height) / b.height() + '%',
							marginRight: (100 * q.marginRight) / b.width() + '%',
						})
				)
				.appendTo(i);
			var x = e('<div>')
				.css({
					position: 'absolute',
					right: '100%',
					left: 'auto',
					top: 0,
					width: '0%',
					height: '100%',
				})
				.append(
					e(k[y])
						.clone()
						.css({
							width: (100 * q.width) / b.width() + '%',
							height: (100 * q.height) / b.height() + '%',
							marginRight: (100 * q.marginRight) / b.width() + '%',
						})
				)
				.appendTo(i);
			i.css({ left: 'auto', right: 'auto', top: 0 }).css(r, 0).show();
			i.show();
			g.hide();
			x.animate(
				{ width: '100%', right: 0 },
				m.duration,
				'easeInOutExpo',
				function () {
					e(this).remove();
				}
			);
			n.animate({ width: 0 }, m.duration, 'easeInOutExpo', function () {
				j.trigger('effectEnd');
				i.empty().hide();
			});
		}
	};
	function f(x, o) {
		o = o || {};
		var z = 1,
			r = o.exclude || [],
			w;
		var t = document.createElement('canvas'),
			q = t.getContext('2d'),
			p = (t.width = x.naturalWidth),
			D = (t.height = x.naturalHeight);
		q.drawImage(x, 0, 0, x.naturalWidth, x.naturalHeight);
		try {
			w = q.getImageData(
				o.x ? o.x : 0,
				o.y ? o.y : 0,
				o.w ? o.w : x.width,
				o.h ? o.h : x.height
			)['data'];
		} catch (y) {
			console.log('error:unable to access image data: ' + y);
			return '#ccc';
		}
		var s = (o.w ? o.w : x.width * o.h ? o.h : x.height) || w.length,
			u = {},
			B = '',
			A = [],
			n = { dominant: { name: '', count: 0 } };
		var v = 0;
		while (v < s) {
			A[0] = w[v];
			A[1] = w[v + 1];
			A[2] = w[v + 2];
			B = A.join(',');
			if (B in u) {
				u[B] = u[B] + 1;
			} else {
				u[B] = 1;
			}
			if (r.indexOf(['rgb(', B, ')'].join('')) === -1) {
				var C = u[B];
				if (C > n.dominant.count) {
					n.dominant.name = B;
					n.dominant.count = C;
				}
			}
			v += z * 4;
		}
		return ['rgb(', n.dominant.name, ')'].join('');
	}
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'cube_over',
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