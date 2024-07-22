/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_blast(q, j, m) {
	var e = jQuery;
	var i = e(this);
	var f = m.find('.ws_list');
	var a = q.distance || 1;
	var g = e('<div>').addClass('ws_effect ws_blast');
	var c = e('<div>').addClass('ws_zoom').appendTo(g);
	var k = e('<div>').addClass('ws_parts').appendTo(g);
	m.css({ overflow: 'visible' }).append(g);
	g.css({
		position: 'absolute',
		right: 0,
		top: 0,
		width: '100%',
		height: '100%',
		'z-index': 8,
	});
	var d = q.cols;
	var p = q.rows;
	var l = [];
	var b = [];
	function h(u, r, s, t) {
		if (q.support.transform && q.support.transition) {
			if (typeof r.right === 'number' || typeof r.top === 'number') {
				r.transform =
					'translate3d(-' +
					(typeof r.right === 'number' ? r.right : 0) +
					'px,' +
					(typeof r.top === 'number' ? r.top : 0) +
					'px,0)';
			}
			delete r.right;
			delete r.top;
			if (s) {
				r.transition = 'all ' + s + 'ms ease-in-out';
			} else {
				r.transition = '';
			}
			u.css(r);
			if (t) {
				u.on(
					'transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd',
					function () {
						t();
						u.off(
							'transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd'
						);
					}
				);
			}
		} else {
			delete r.transfrom;
			delete r.transition;
			if (s) {
				u.animate(r, {
					queue: false,
					duration: q.duration,
					complete: t ? t : 0,
				});
			} else {
				u.stop(1).css(r);
			}
		}
	}
	function n(r) {
		var w = Math.max((q.width || g.width()) / (q.height || g.height()) || 3, 3);
		d = d || Math.round(w < 1 ? 3 : 3 * w);
		p = p || Math.round(w < 1 ? 3 / w : 3);
		for (var u = 0; u < d * p; u++) {
			var v = u % d;
			var t = Math.floor(u / d);
			e([
				(b[u] = document.createElement('div')),
				(l[u] = document.createElement('div')),
			])
				.css({ position: 'absolute', overflow: 'hidden' })
				.appendTo(k)
				.append(e('<img>').css({ position: 'absolute' }));
		}
		l = e(l);
		b = e(b);
		o(l, r);
		o(b, r, true);
		var s = {
			position: 'absolute',
			top: 0,
			right: 0,
			width: '100%',
			height: '100%',
			overflow: 'hidden',
		};
		c.css(s).append(e('<img>').css(s));
	}
	function o(t, u, s, r, w, z) {
		var v = g.width();
		var x = g.height();
		var y = {
			left: e(window).scrollLeft(),
			top: e(window).scrollTop(),
			width: e(window).width(),
			height: e(window).height(),
		};
		e(t).each(function (F) {
			var E = F % d;
			var C = Math.floor(F / d);
			if (s) {
				var I = a * v * (2 * Math.random() - 1) + v / 2;
				var G = a * x * (2 * Math.random() - 1) + x / 2;
				var H = g.offset();
				H.left += I;
				H.top += G;
				if (H.left < y.left) {
					I -= H.left + y.left;
				}
				if (H.top < y.top) {
					G -= H.top + y.top;
				}
				var D = y.left + y.width - H.left - v / d;
				if (0 > D) {
					I += D;
				}
				var B = y.top + y.height - H.top - x / p;
				if (0 > B) {
					G += B;
				}
			} else {
				var I = (v * E) / d;
				var G = (x * C) / p;
			}
			e(this)
				.find('img')
				.css({
					right: -((v * E) / d) + u.marginRight,
					top: -((x * C) / p) + u.marginTop,
					width: u.width,
					height: u.height,
				});
			var A = { right: I, top: G, width: v / d, height: x / p };
			if (w) {
				e.extend(A, w);
			}
			if (r) {
				h(e(this), A, q.duration, F === 0 && z ? z : 0);
			} else {
				h(e(this), A);
			}
		});
	}
	this.go = function (s, u) {
		var v = e(j[u]),
			r = {
				width: v.width(),
				height: v.height(),
				marginTop: parseFloat(v.css('marginTop')),
				marginRight: parseFloat(v.css('marginRight')),
			};
		if (!l.length) {
			n(r);
		}
		l.find('img').attr('src', j.get(u).src);
		h(l, { opacity: 1, zIndex: 3 });
		b.find('img').attr('src', j.get(s).src);
		h(b, { opacity: 0, zIndex: 2 });
		c.find('img').attr('src', j.get(u).src);
		h(c.find('img'), { transform: 'scale(1)' });
		g.show();
		f.hide();
		o(b, r, false, true, { opacity: 1 });
		o(l, r, true, true, { opacity: 0 }, function () {
			i.trigger('effectEnd');
			g.hide();
		});
		h(c.find('img'), { transform: 'scale(2)' }, q.duration, 0);
		var t = b;
		b = l;
		l = t;
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'blast',
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