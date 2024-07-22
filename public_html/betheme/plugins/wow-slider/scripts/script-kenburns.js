/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_kenburns(d, l, m) {
	var e = jQuery;
	var g = e(this);
	var f = document.createElement('canvas').getContext;
	var i = e('<div>')
		.css({
			position: 'absolute',
			top: 0,
			right: 0,
			width: '100%',
			height: '100%',
			overflow: 'hidden',
		})
		.addClass('ws_effect ws_kenburns')
		.appendTo(m);
	var o = d.paths || [
		{ from: [0, 0, 1], to: [0, 0, 1.2] },
		{ from: [0, 0, 1.2], to: [0, 0, 1] },
		{ from: [1, 0, 1], to: [1, 0, 1.2] },
		{ from: [0, 1, 1.2], to: [0, 1, 1] },
		{ from: [1, 1, 1], to: [1, 1, 1.2] },
		{ from: [0.5, 1, 1], to: [0.5, 1, 1.3] },
		{ from: [1, 0.5, 1.2], to: [1, 0.5, 1] },
		{ from: [1, 0.5, 1], to: [1, 0.5, 1.2] },
		{ from: [0, 0.5, 1.2], to: [0, 0.5, 1] },
		{ from: [1, 0.5, 1.2], to: [1, 0.5, 1] },
		{ from: [0.5, 0.5, 1], to: [0.5, 0.5, 1.2] },
		{ from: [0.5, 0.5, 1.3], to: [0.5, 0.5, 1] },
		{ from: [0.5, 1, 1], to: [0.5, 0, 1.15] },
	];
	function c(h) {
		return o[
			h ? Math.floor(Math.random() * (f ? o.length : Math.min(5, o.length))) : 0
		];
	}
	var k = d.width,
		p = d.height;
	var j, b;
	var a, r;
	function n() {
		a = e('<div style="width:100%;height:100%"></div>')
			.css({ 'z-index': 8, position: 'absolute', right: 0, top: 0 })
			.appendTo(i);
	}
	n();
	function s(w, t, h) {
		var u = { width: 100 * w[2] + '%' };
		u[t ? 'rightt' : 'right'] = -100 * (w[2] - 1) * (t ? 1 - w[0] : w[0]) + '%';
		u[h ? 'bottom' : 'top'] = -100 * (w[2] - 1) * (h ? 1 - w[1] : w[1]) + '%';
		if (!f) {
			for (var v in u) {
				if (/\%/.test(u[v])) {
					u[v] =
						((/right|left|width/.test(v) ? k : p) * parseFloat(u[v])) / 100 +
						'px';
				}
			}
		}
		return u;
	}
	function q(w, z, A) {
		var t = e(w);
		t = {
			width: t.width(),
			height: t.height(),
			marginTop: t.css('marginTop'),
			marginRight: t.css('marginRight'),
		};
		if (f) {
			if (b) {
				b.stop(1);
			}
			b = j;
		}
		if (r) {
			r.remove();
		}
		r = a;
		n();
		if (A) {
			a.hide();
			r.stop(true, true);
		}
		if (f) {
			var y, x;
			var u, h;
			u = e('<canvas width="' + k + '" height="' + p + '"/>');
			u.css({ position: 'absolute', right: 0, top: 0 }).css(t).appendTo(a);
			y = u.get(0).getContext('2d');
			h = u.clone().appendTo(a);
			x = h.get(0).getContext('2d');
			j = wowAnimate(
				function (B) {
					var D = [
						z.from[0] * (1 - B) + B * z.to[0],
						z.from[1] * (1 - B) + B * z.to[1],
						z.from[2] * (1 - B) + B * z.to[2],
					];
					x.drawImage(
						w,
						-k * (D[2] - 1) * D[0],
						-p * (D[2] - 1) * D[1],
						k * D[2],
						p * D[2]
					);
					y.clearRect(0, 0, k, p);
					var C = y;
					y = x;
					x = C;
				},
				0,
				1,
				d.duration + d.delay * 2
			);
		} else {
			k = t.width;
			p = t.height;
			var v = e('<img src="../../wow-slider/engine1/' + w.src + '"/>')
				.css({
					position: 'absolute',
					left: 'auto',
					right: 'auto',
					top: 'auto',
					bottom: 'auto',
				})
				.appendTo(a)
				.css(s(z.from, z.from[0] > 0.5, z.from[1] > 0.5))
				.animate(s(z.to, z.from[0] > 0.5, z.from[1] > 0.5), {
					easing: 'linear',
					queue: false,
					duration: 1.5 * d.duration + d.delay,
				});
		}
		if (A) {
			a.fadeIn(d.duration);
		}
	}
	if (d.effect.length == 1) {
		e(function () {
			l.each(function (h) {
				e(this).css({ visibility: 'hidden' });
				if (h == d.startSlide) {
					q(this, c(h), 0);
				}
			});
		});
	}
	this.go = function (h, t) {
		setTimeout(function () {
			g.trigger('effectEnd');
		}, d.duration);
		q(l.get(h), c(h), 1);
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'kenburns',
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