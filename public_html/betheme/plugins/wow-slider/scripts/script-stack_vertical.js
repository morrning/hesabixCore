/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_stack_vertical(d, a, b) {
	var e = jQuery;
	var g = e(this);
	var c = e('li', b);
	var f = e('<div>')
		.addClass('ws_effect ws_stack_vertical')
		.css({
			position: 'absolute',
			top: 0,
			right: 0,
			width: '100%',
			height: '100%',
			overflow: 'hidden',
		})
		.appendTo(b);
	this.go = function (q, j, i) {
		var k = (d.revers ? 1 : -1) * b.height();
		c.each(function (s) {
			if (i && s != j) {
				this.style.zIndex = Math.max(0, this.style.zIndex - 1);
			}
		});
		var p = e('.ws_list', b);
		var h = e('<div>')
				.css({
					position: 'absolute',
					right: 0,
					top: 0,
					width: '100%',
					height: '100%',
					overflow: 'hidden',
					zIndex: 4,
				})
				.append(e(a.get(i ? q : j)).clone()),
			r = e('<div>')
				.css({
					position: 'absolute',
					right: 0,
					top: 0,
					width: '100%',
					height: '100%',
					overflow: 'hidden',
					zIndex: 4,
				})
				.append(e(a.get(i ? j : q)).clone());
		if (d.responsive < 3) {
			h.find('img').css('width', '100%');
			r.find('img').css('width', '100%');
		}
		if (i) {
			r.appendTo(f);
			h.appendTo(f);
		} else {
			h.insertAfter(p);
			r.insertAfter(p);
		}
		if (!i) {
			p.stop(true, true)
				.hide()
				.css({ right: -q + '00%' });
			if (d.fadeOut) {
				p.fadeIn(d.duration);
			} else {
				p.show();
			}
		} else {
			if (d.fadeOut) {
				p.fadeOut(d.duration);
			}
		}
		var o = { top: i ? k : 0 };
		var m = { top: i ? 0 : -k * 0.5 };
		var n = { top: i ? 0 : k };
		var l = { top: (i ? 1 : 0) * b.height() * 0.5 };
		if (d.support.transform) {
			o = { translate: [0, o.top, 0] };
			m = { translate: [0, m.top, 0] };
			n = { translate: [0, n.top, 0] };
			l = { translate: [0, l.top, 0] };
		}
		wowAnimate(
			h,
			o,
			n,
			d.duration,
			d.duration * (i ? 0 : 0.1),
			'easeInOutExpo',
			function () {
				g.trigger('effectEnd');
				h.remove();
				r.remove();
			}
		);
		wowAnimate(
			r,
			m,
			l,
			d.duration,
			d.duration * (i ? 0.1 : 0),
			'easeInOutExpo'
		);
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'stack_vertical',
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