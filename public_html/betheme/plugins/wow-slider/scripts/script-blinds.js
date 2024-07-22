/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_blinds(m, l, a) {
	var g = jQuery;
	var k = g(this);
	var c = m.parts || 3;
	var j = g('.ws_list', a);
	var h = g('<div>')
		.addClass('ws_effect ws_blinds')
		.css({
			position: 'absolute',
			width: '100%',
			height: '100%',
			right: 0,
			top: 0,
			'z-index': 8,
		})
		.hide()
		.appendTo(a);
	var d = g('<div>')
		.css({
			position: 'absolute',
			top: 0,
			right: 0,
			width: '100%',
			height: '100%',
			overflow: 'hidden',
		})
		.appendTo(h);
	var e = [];
	var b = document.addEventListener;
	for (var f = 0; f < c; f++) {
		e[f] = g('<div>')
			.css({
				position: b ? 'relative' : 'absolute',
				display: b ? 'inline-block' : 'block',
				height: '100%',
				width: (100 / c + 0.001).toFixed(3) + '%',
				border: 'none',
				margin: 0,
				overflow: 'hidden',
				top: 0,
				right: b ? 0 : ((100 * f) / c).toFixed(3) + '%',
			})
			.appendTo(h);
	}
	this.go = function (r, p, o) {
		if (o == undefined) {
			o = p > r ? 1 : 0;
		}
		h.find('img').stop(true, true);
		h.show();
		var s = g(l[p]);
		var t = { width: s.width() || m.width, height: s.height() || m.height };
		var u = s.clone().css(t).appendTo(d);
		u.from = { right: 0 };
		u.to = { right: (!o ? -1 : 1) * u.width() * 0.5 };
		if (m.support.transform) {
			u.from = { translate: [u.from.right, 0, 0] };
			u.to = { translate: [u.to.right, 0, 0] };
		}
		j.hide();
		wowAnimate(u, u.from, u.to, m.duration, m.duration * 0.1, 'swing');
		for (var q = 0; q < e.length; q++) {
			var n = e[q];
			var v = g(l[r])
				.clone()
				.css({ position: 'absolute', top: 0 })
				.css(t)
				.appendTo(n);
			v.from = { right: (!o ? 1 : -1) * v.width() - n.position().left };
			v.to = { right: n.position().left };
			if (m.support.transform) {
				v.from = { translate: [v.from.right, 0, 0] };
				v.to = { translate: [v.to.right, 0, 0] };
			}
			wowAnimate(
				v,
				v.from,
				v.to,
				(m.duration / (e.length + 1)) * (o ? e.length - q + 1 : q + 2),
				'swing',
				(!o && q == e.length - 1) || (o && !q)
					? function () {
							k.trigger('effectEnd');
							h.hide().find('img').remove();
							u.remove();
					  }
					: false
			);
		}
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'blinds',
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