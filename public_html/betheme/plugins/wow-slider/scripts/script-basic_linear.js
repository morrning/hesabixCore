/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_basic_linear(j, g, a) {
	var c = jQuery;
	var f = c(this);
	var e = a.find('.ws_list');
	var i = c('<div>')
		.addClass('ws_effect ws_basic_linear')
		.css({
			position: 'absolute',
			top: 0,
			right: 0,
			width: '100%',
			height: '100%',
			overflow: 'hidden',
		})
		.appendTo(a);
	var b = c('<div>')
		.css({
			position: 'absolute',
			display: 'none',
			'z-index': 2,
			width: '200%',
			height: '100%',
			transform: 'translate3d(0,0,0)',
		})
		.appendTo(i);
	var h = c('<div>').css({
			position: 'absolute',
			right: 'auto',
			top: 'auto',
			width: '50%',
			height: '100%',
			overflow: 'hidden',
		}),
		d = h.clone();
	b.append(h, d);
	this.go = function (k, n, m) {
		b.stop(1, 1);
		if (m == undefined) {
			m = !!((k - n + 1) % g.length) ^ j.revers ? 'right' : 'left';
		} else {
			m = m ? 'right' : 'left';
		}
		var o = c(g[n]);
		var l = { width: o.width() || j.width, height: o.height() || j.height };
		o.clone().css(l).appendTo(h).css(m, 0);
		c(g[k]).clone().css(l).appendTo(d).show();
		if (m == 'right') {
			h.css('left', '50%');
			d.css('left', 0);
		} else {
			h.css('left', 0);
			d.css('left', '50%');
		}
		var q = {},
			p = {};
		q[m] = 0;
		p[m] = -a.width();
		if (j.support.transform) {
			if (m == 'right') {
				q.left = q.right;
				p.left = -p.right;
			}
			q = { translate: [q.left, 0, 0] };
			p = { translate: [p.left, 0, 0] };
		}
		e.hide();
		wowAnimate(
			b.css({ left: 'auto', right: 'auto', top: 0 }).css(m, 0).show(),
			q,
			p,
			j.duration,
			'easeInOutExpo',
			function () {
				f.trigger('effectEnd');
				b.hide().find('div').html('');
			}
		);
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'basic_linear',
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
});