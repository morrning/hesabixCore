/**
 * Parallax
 *
 * Translate3d
 * 1.0 | Muffin Group
 */

var mfnSetup = {
	translate: null,
};

(function ($) {
	/* globals jQuery */

	'use strict';

	/**
	 * mfnSetup
	 */

	// has3d

	var has3d = function () {
		if (!window.getComputedStyle) {
			return false;
		}

		var el = document.createElement('div'),
			has3d;

		document.body.insertBefore(el, null);

		if (el.style.transform !== undefined) {
			el.style.transform = 'translate3d(1px,1px,1px)';
			has3d = window.getComputedStyle(el).getPropertyValue('transform');
		}

		document.body.removeChild(el);

		return (
			has3d !== undefined &&
			has3d !== null &&
			has3d.length > 0 &&
			has3d !== 'none'
		);
	};

	// __construct

	var __construct = function () {
		if (has3d()) {
			mfnSetup.translate = function (el, x, y) {
				el.css('transform', 'translate3d(-' + x + ', ' + y + ', 0)');
			};
		} else {
			mfnSetup.translate = function (el, x, y) {
				el.css({
					right: x,
					top: y,
				});
			};
		}
	};

	__construct();
})(jQuery);

(function ($) {
	'use strict';

	/**
	 * $.fn.mfnParallax
	 */

	$.fn.mfnParallax = function () {
		var el = $(this),
			parent = el.parent(),
			speed = 500,
			element,
			parentPos,
			windowH;

		// imageSize

		var imageSize = function (img) {
			var w, h, l, t; // width, height, right, top

			var imageW = img.get(0).naturalWidth;
			var imageH = img.get(0).naturalHeight;

			var parentW = img.parent().outerWidth();
			var parentH = img.parent().outerHeight();

			var windowH = $(window).height();

			// fix for small sections
			if (windowH > parentH) {
				parentH = windowH;
			}

			var diff = imageW / parentW;

			if (imageH / diff < parentH) {
				w = imageW / (imageH / parentH);
				h = parentH;

				if (w > imageW) {
					w = imageW;
					h = imageH;
				}
			} else {
				w = parentW;
				h = imageH / diff;
			}

			l = (parentW - w) / 2;
			t = (parentH - h) / 2;

			return [w, h, l, t];
		};

		// parallax

		var parallax = function () {
			var scrollTop = $(window).scrollTop(),
				scrollDiff,
				ratio,
				translateTop;

			if (parentPos !== undefined) {
				if (scrollTop >= parentPos.min && scrollTop <= parentPos.max) {
					scrollDiff = scrollTop - parentPos.min;
					ratio = scrollDiff / parentPos.height;

					translateTop =
						windowH +
						ratio * speed -
						scrollDiff -
						speed * (windowH / parentPos.height);

					mfnSetup.translate(
						el,
						element.right + 'px',
						-Math.round(translateTop) + 'px'
					);
				}
			}
		};

		// init

		var init = function () {
			windowH = $(window).height();

			var initElement = function () {
				var size = imageSize(el);

				el.removeAttr('style').css({
					width: size[0],
					height: size[1],
				});

				mfnSetup.translate(el, size[2] + 'px', size[3] + 'px');

				return {
					width: size[0],
					height: size[1],
					right: size[2],
					top: size[3],
				};
			};

			element = initElement();

			var initParent = function () {
				var min = parent.offset().top - $(window).height();
				var max = parent.offset().top + $(parent).outerHeight();

				return {
					min: min,
					max: max,
					height: max - min,
				};
			};

			parentPos = initParent();
		};

		// reload

		var reload = function () {
			setTimeout(function () {
				init();
				parallax();
			}, 50);
		};

		reload();

		/**
		 * $(window).scroll
		 */

		$(window).on('scroll', parallax);
	};

	/**
	 * $(window).load
	 */

	$(window).on('load resize', function () {
		if ($('.mfn-parallax').length) {
			$('.mfn-parallax').each(function () {
				$(this).mfnParallax();
			});
		}
	});
})(jQuery);