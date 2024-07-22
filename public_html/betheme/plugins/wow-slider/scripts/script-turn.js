/**
 * WOWslider
 *
 * http://wowslider.com
 */
jQuery.extend(jQuery.easing, {
	easeInOutQuart: function (e, f, a, h, g) {
		if ((f /= g / 2) < 1) {
			return (h / 2) * f * f * f * f + a;
		}
		return (-h / 2) * ((f -= 2) * f * f * f - 2) + a;
	},
});
function ws_turn(d, a, b) {
	var f = jQuery;
	var h = f(this);
	var c = f('li', b);
	var e = f('.ws_list', b);
	var g = f('<div>')
		.addClass('ws_effect ws_turn')
		.css({
			position: 'absolute',
			top: 0,
			right: 0,
			width: '100%',
			height: '100%',
			overflow: 'hidden',
			perspective: 500,
		})
		.appendTo(b);
	this.go = function (q, l) {
		var s = b.height();
		var j = b.width();
		var k = {
			right: ['0% 50%', { rotateY: 90, translate: [0, 0, 0.1] }, { left: -j }],
			left: [
				'100% 50%',
				{ rotateY: -90, translate: [0, 0, 0.1] },
				{ left: j },
			],
			up: ['50% 0%', { rotateX: -90, translate: [0, 0, 0.1] }, { top: -s }],
			down: ['50% 100%', { rotateX: 90, translate: [0, 0, 0.1] }, { top: s }],
		}[
			d.direction ||
				['left', 'right', 'down', 'up'][Math.floor(Math.random() * 4)]
		];
		var i = f('<div>')
				.css({
					position: 'absolute',
					right: 0,
					top: 0,
					width: '100%',
					height: '100%',
					overflow: 'hidden',
					transformOrigin: k[0],
					transformStyle: 'preserve-3d',
					outline: '1px solid transparent',
					zIndex: 5,
				})
				.append(f(a.get(q)).clone()),
			r = f('<div>')
				.css({
					position: 'absolute',
					right: 0,
					top: 0,
					width: '100%',
					height: '100%',
					overflow: 'hidden',
					background: '#000',
					zIndex: 4,
				})
				.append(f(a.get(l)).clone());
		g.css({ perspectiveOrigin: k[0] });
		if (d.responsive < 3) {
			i.find('img').css('width', '100%');
			r.find('img').css('width', '100%');
		}
		r.appendTo(g);
		i.appendTo(g);
		e.stop(true, true)
			.hide()
			.css({ right: -q + '00%' });
		var p = k[2];
		var o = { top: 0, left: 0 };
		var n = { opacity: 1 };
		var m = { opacity: 0.2 };
		if (d.support.transform) {
			p = k[1];
			o = { rotateX: 0, rotateY: 0, translate: [0, 0, 0] };
		}
		wowAnimate(i, p, o, d.duration, 'easeInOutQuart', function () {
			h.trigger('effectEnd');
			i.remove();
			r.remove();
		});
		wowAnimate(r.find('img'), n, m, d.duration, 'easeInOutQuart');
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'turn',
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