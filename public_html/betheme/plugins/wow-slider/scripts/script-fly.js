/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_fly(c, a, b) {
	var e = jQuery;
	var f = e(this);
	var h = {
		position: 'absolute',
		right: 0,
		top: 0,
		width: '100%',
		height: '100%',
		transform: 'translate3d(0,0,0)',
	};
	var d = b.find('.ws_list');
	var g = e('<div>')
		.addClass('ws_effect ws_fly')
		.css(h)
		.css({ overflow: 'visible' })
		.appendTo(b);
	this.go = function (p, m, l) {
		if (l == undefined) {
			l = !!c.revers;
		} else {
			l = !l;
		}
		var k = -(c.distance || g.width() / 4),
			n = Math.min(
				-k,
				Math.max(0, e(window).width() - g.offset().left - g.width())
			),
			i = l ? n : k,
			q = l ? k : n;
		var j = e(a.get(m));
		j = { width: j.width(), height: j.height() };
		var r = e('<div>')
			.css(h)
			.css({ 'z-index': 1, overflow: 'hidden' })
			.html(e(a.get(m)).clone().css(j))
			.appendTo(g);
		var o = e('<div>')
			.css(h)
			.css({ 'z-index': 3, overflow: 'hidden' })
			.html(e(a.get(p)).clone().css(j))
			.appendTo(g)
			.show();
		wowAnimate(o, { opacity: 0 }, { opacity: 1 }, c.duration);
		wowAnimate(o, { right: i }, { right: 0 }, (2 * c.duration) / 3);
		d.hide();
		wowAnimate(
			r,
			{ right: 0, opacity: 1 },
			{ right: q, opacity: 0 },
			(2 * c.duration) / 3,
			c.duration / 3,
			function () {
				r.remove();
				f.trigger('effectEnd');
				o.remove();
			}
		);
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'fly',
	duration: 13 * 100, // change effect transition time
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