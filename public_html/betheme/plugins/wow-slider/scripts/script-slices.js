/**
 * WOWslider
 *
 * http://wowslider.com
 */
function ws_slices(k, h, i) {
	var b = jQuery;
	var f = b(this);
	function g(q, p, o, m, l, n) {
		if (k.support.transform) {
			if (p.top) {
				p.translate = [0, p.top || 0, 0];
			}
			if (o.top) {
				o.translate = [0, o.top || 0, 0];
			}
			delete p.top;
			delete o.top;
		}
		wowAnimate(q, p, o, m, l, 'swing', n);
	}
	var e = function (r, x) {
		var q = b.extend(
			{},
			{
				effect: 'random',
				slices: 15,
				animSpeed: 500,
				pauseTime: 3000,
				startSlide: 0,
				container: null,
				onEffectEnd: 0,
			},
			x
		);
		var t = {
			currentSlide: 0,
			currentImage: '',
			totalSlides: 0,
			randAnim: '',
			stop: false,
		};
		var o = b(r);
		o.data('wow:vars', t);
		if (!/absolute|relative/.test(o.css('position'))) {
			o.css('position', 'relative');
		}
		var m = x.container || o;
		var p = o.children();
		t.totalSlides = p.length;
		if (q.startSlide > 0) {
			if (q.startSlide >= t.totalSlides) {
				q.startSlide = t.totalSlides - 1;
			}
			t.currentSlide = q.startSlide;
		}
		if (b(p[t.currentSlide]).is('img')) {
			t.currentImage = b(p[t.currentSlide]);
		} else {
			t.currentImage = b(p[t.currentSlide]).find('img:first');
		}
		if (b(p[t.currentSlide]).is('a')) {
			b(p[t.currentSlide]).css('display', 'block');
		}
		for (var s = 0; s < q.slices; s++) {
			var w = Math.round(m.width() / q.slices);
			var v = b('<div class="wow-slice"></div>')
				.css({
					right: w * s + 'px',
					overflow: 'hidden',
					width: (s == q.slices - 1 ? m.width() - w * s : w) + 'px',
					position: 'absolute',
				})
				.appendTo(m);
			b('<img>')
				.css({
					position: 'absolute',
					right: 0,
					top: 0,
					transform: 'translate3d(0,0,0)',
				})
				.appendTo(v);
		}
		var n = 0;
		this.sliderRun = function (y, z) {
			if (t.busy) {
				return false;
			}
			q.effect = z || q.effect;
			t.currentSlide = y - 1;
			u(o, p, q, false);
			return true;
		};
		var l = function () {
			if (q.onEffectEnd) {
				q.onEffectEnd(t.currentSlide);
			}
			m.hide();
			b('.wow-slice', m).css({ transition: '', transform: '' });
			t.busy = 0;
		};
		var u = function (y, z, C, E) {
			var F = y.data('wow:vars');
			if ((!F || F.stop) && !E) {
				return false;
			}
			F.busy = 1;
			F.currentSlide++;
			if (F.currentSlide == F.totalSlides) {
				F.currentSlide = 0;
			}
			if (F.currentSlide < 0) {
				F.currentSlide = F.totalSlides - 1;
			}
			F.currentImage = b(z[F.currentSlide]);
			if (!F.currentImage.is('img')) {
				F.currentImage = F.currentImage.find('img:first');
			}
			var A = b(h[F.currentSlide]);
			A = {
				width: A.width(),
				heiht: A.height(),
				marginTop: A.css('marginTop'),
				marginRight: A.css('marginRight'),
			};
			b('.wow-slice', m).each(function (J) {
				var L = b(this),
					I = b('img', L);
				var K = Math.round(m.width() / C.slices);
				I.width(m.width());
				I.attr('src', F.currentImage.attr('src'));
				I.css({ right: -K * J + 'px' }).css(A);
				L.css({
					height: '100%',
					opacity: 0,
					right: K * J,
					width: J == C.slices - 1 ? m.width() - K * J : K,
				});
			});
			m.show();
			if (C.effect == 'random') {
				var G = new Array(
					'sliceDownRight',
					'sliceDownLeft',
					'sliceUpRight',
					'sliceUpLeft',
					'sliceUpDownRight',
					'sliceUpDownLeft',
					'fold',
					'fade'
				);
				F.randAnim = G[Math.floor(Math.random() * (G.length + 1))];
				if (F.randAnim == undefined) {
					F.randAnim = 'fade';
				}
			}
			if (C.effect.indexOf(',') != -1) {
				var G = C.effect.split(',');
				F.randAnim = b.trim(G[Math.floor(Math.random() * G.length)]);
			}
			if (
				C.effect == 'sliceDown' ||
				C.effect == 'sliceDownRight' ||
				F.randAnim == 'sliceDownRight' ||
				C.effect == 'sliceDownLeft' ||
				F.randAnim == 'sliceDownLeft'
			) {
				var B = 0;
				var H = b('.wow-slice', m);
				if (C.effect == 'sliceDownRight' || F.randAnim == 'sliceDownRight') {
					H = H._reverse();
				}
				H.each(function (I) {
					g(
						b(this),
						{ top: '-100%', opacity: 0 },
						{ top: '0%', opacity: 1 },
						C.animSpeed,
						100 + B,
						I == C.slices - 1 ? l : 0
					);
					B += 50;
				});
			} else {
				if (
					C.effect == 'sliceUp' ||
					C.effect == 'sliceUpRight' ||
					F.randAnim == 'sliceUpRight' ||
					C.effect == 'sliceUpLeft' ||
					F.randAnim == 'sliceUpLeft'
				) {
					var B = 0;
					var H = b('.wow-slice', m);
					if (C.effect == 'sliceUpRight' || F.randAnim == 'sliceUpRight') {
						H = H._reverse();
					}
					H.each(function (I) {
						g(
							b(this),
							{ top: '100%', opacity: 0 },
							{ top: '0%', opacity: 1 },
							C.animSpeed,
							100 + B,
							I == C.slices - 1 ? l : 0
						);
						B += 50;
					});
				} else {
					if (
						C.effect == 'sliceUpDown' ||
						C.effect == 'sliceUpDownRight' ||
						F.randAnim == 'sliceUpDownRight' ||
						C.effect == 'sliceUpDownLeft' ||
						F.randAnim == 'sliceUpDownLeft'
					) {
						var B = 0;
						var H = b('.wow-slice', m);
						if (
							C.effect == 'sliceUpDownRight' ||
							F.randAnim == 'sliceUpDownRight'
						) {
							H = H._reverse();
						}
						H.each(function (I) {
							g(
								b(this),
								{ top: (I % 2 ? '-' : '') + '100%', opacity: 0 },
								{ top: '0%', opacity: 1 },
								C.animSpeed,
								100 + B,
								I == C.slices - 1 ? l : 0
							);
							B += 50;
						});
					} else {
						if (C.effect == 'fold' || F.randAnim == 'fold') {
							var B = 0;
							var H = b('.wow-slice', m);
							var D = H.width();
							H.each(function (I) {
								g(
									b(this),
									{ width: 0, opacity: 0 },
									{ width: D, opacity: 1 },
									C.animSpeed,
									100 + B,
									I == C.slices - 1 ? l : 0
								);
								B += 50;
							});
						} else {
							if (C.effect == 'fade' || F.randAnim == 'fade') {
								var H = b('.wow-slice', m);
								b('.wow-slice', m).each(function (I) {
									b(this).css('height', '100%');
									b(this).animate(
										{ opacity: '1.0' },
										C.animSpeed * 2,
										I == C.slices - 1 ? l : 0
									);
								});
							}
						}
					}
				}
			}
		};
	};
	b.fn._reverse = [].reverse;
	var a = b('li', i);
	var c = b('ul', i);
	var d = b('<div>')
		.addClass('ws_effect ws_slices')
		.css({
			right: 0,
			top: 0,
			'z-index': 8,
			overflow: 'hidden',
			width: '100%',
			height: '100%',
			position: 'absolute',
		})
		.appendTo(i);
	var j = new e(c, {
		keyboardNav: false,
		caption: 0,
		effect:
			'sliceDownRight,sliceDownLeft,sliceUpRight,sliceUpLeft,sliceUpDownRight,sliceUpDownLeft,sliceUpDownRight,sliceUpDownLeft,fold,fold,fold',
		animSpeed: k.duration,
		startSlide: k.startSlide,
		container: d,
		onEffectEnd: function (l) {
			f.trigger('effectEnd');
		},
	});
	this.go = function (m, l) {
		var n = j.sliderRun(m);
		if (k.fadeOut) {
			c.fadeOut(k.duration);
		}
	};
}

/* ---------------------------------------------------------------------------
 * WOWslider Settings
 * -------------------------------------------------------------------------- */

jQuery('#wowslider-container1').wowSlider({
	effect: 'slices',
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