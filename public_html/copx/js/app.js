/*================
 Template Name: CorporX Corporate and Business HTML Template
 Description: All type of corporate business with marketing and agency template.
 Version: 1.0
 Author: https://themeforest.net/user/themetags
=======================*/
// TABLE OF CONTENTS
// 1. preloader
// 2. fixed navbar
// 3. back to top
// 4. magnify popup video
// 5. magnify gallery popup
// 6. hero background image with content slider
// 7. custom counter js with scrolling
// 8. client-testimonial one item carousel
// 9. hero content one item carousel
// 10. our clients logo carousel
// 11. mixitup portfolio
// 12. video background
// 13. work process carousel
// 14. gallery carousel
// 15. wow js
jQuery(function ($) {
  'use strict'; // preloader

  $(window).ready(function () {
    $('#preloader').delay(200).fadeOut('fade');
  });
  var $navbarCollapse = $('.navbar-main .collapse'); // Collapse navigation

  $navbarCollapse.on('hide.bs.collapse', function () {
    var $this = $(this);
    $this.addClass('collapsing-out');
    $('html, body').css('overflow', 'initial');
  });
  $navbarCollapse.on('hidden.bs.collapse', function () {
    var $this = $(this);
    $this.removeClass('collapsing-out');
  });
  $navbarCollapse.on('shown.bs.collapse', function () {
    $('html, body').css('overflow', 'hidden');
  });
  $('.navbar-main .dropdown').on('hide.bs.dropdown', function () {
    var $this = $(this).find('.dropdown-menu');
    $this.addClass('close');
    setTimeout(function () {
      $this.removeClass('close');
    }, 200);
  });
  $(document).on('click', '.mega-dropdown', function (e) {
    e.stopPropagation();
  });
  $(document).on('click', '.navbar-nav > .dropdown', function (e) {
    e.stopPropagation();
  });
  $('.dropdown-submenu > .dropdown-toggle').click(function (e) {
    e.preventDefault();
    $(this).parent('.dropdown-submenu').toggleClass('show');
  }); // 3. back to top

  if ($('.scroll-to-target').length) {
    $(".scroll-to-target").on('click', function () {
      var target = $(this).attr('data-target'); // animate

      $('html, body').animate({
        scrollTop: $(target).offset().top
      }, 500);
    });
  } // Tooltip


  $('[data-toggle="tooltip"]').tooltip(); // Additional .focus class on form-groups

  $('.form-control').on('focus blur', function (e) {
    $(this).parents('.form-group').toggleClass('focused', e.type === 'focus' || this.value.length > 0);
  }).trigger('blur');
  $(".progress-bar").each(function () {
    $(this).waypoint(function () {
      var progressBar = $(".progress-bar");
      progressBar.each(function (indx) {
        $(this).css("width", $(this).attr("aria-valuenow") + "%");
      });
      $('.progress-bar').css({
        animation: "animate-positive 3s",
        opacity: "1"
      });
    }, {
      triggerOnce: true,
      offset: '60%'
    });
  }); // Scroll to anchor with scroll animation

  $('[data-toggle="scroll"]').on('click', function (event) {
    var hash = $(this).attr('href');
    var offset = $(this).data('offset') ? $(this).data('offset') : 0; // Animate scroll to the selected section

    $('html, body').stop(true, true).animate({
      scrollTop: $(hash).offset().top - offset
    }, 600);
    event.preventDefault();
  }); //Smooth scroll

  var scroll = new SmoothScroll('a[href*="#"]', {
    speed: 500,
    speedAsDuration: true
  }); // 2. fixed navbar

  $(window).on('scroll', function () {
    // checks if window is scrolled more than 500px, adds/removes solid class
    if ($(this).scrollTop() > 0) {
      $('.navbar').addClass('affix');
      $('.scroll-to-target').addClass('open');
    } else {
      $('.navbar').removeClass('affix');
      $('.scroll-to-target').removeClass('open');
    } // checks if window is scrolled more than 500px, adds/removes top to target class


    if ($(this).scrollTop() > 500) {
      $('.scroll-to-target').addClass('open');
    } else {
      $('.scroll-to-target').removeClass('open');
    }
  });
  $('.sub-menu a.dropdown-toggle').on('click', function (e) {
    if (!$(this).next().hasClass('show')) {
      $(this).parents('.sub-menu').first().find('.show').removeClass("show");
    }

    var $subMenu = $(this).next(".sub-menu");
    $subMenu.toggleClass('show');
    $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function (e) {
      $('.dropdown-submenu .show').removeClass("show");
    });
    return false;
  }); // 10. hero slider two

  $('.hero-content-slider').owlCarousel({
    loop: false,
    autoplay: true,
    dots: true,
    autoplayHoverPause: true,
    items: 1,
    smartSpeed: 1000,
    animateOut: "slideOutUp",
    animateIn: "slideInDown"
  });
  $('.custom-indicator-slider').owlCarousel({
    items: 1,
    nav: false,
    dots: true,
    smartSpeed: 1000,
    animateOut: "slideOutUp",
    animateIn: "slideInDown",
    dotsContainer: '#carousel-custom-indicator'
  }); // 7. custom counter js with scrolling

  $('.counter').each(function () {
    var $this = $(this),
        countTo = $this.attr('data-count');
    $({
      countNum: $this.text()
    }).animate({
      countNum: countTo
    }, {
      duration: 5000,
      easing: 'linear',
      step: function () {
        $this.text(Math.floor(this.countNum));
      },
      complete: function () {
        $this.text(this.countNum); //alert('finished');
      }
    });
  });
  $('.client-testimonial').owlCarousel({
    loop: true,
    margin: 25,
    nav: false,
    dots: true,
    responsiveClass: true,
    autoplay: true,
    autoplayHoverPause: true,
    lazyLoad: true,
    responsive: {
      0: {
        items: 1
      },
      500: {
        items: 1
      },
      600: {
        items: 2
      },
      800: {
        items: 2
      },
      1200: {
        items: 2
      }
    }
  }); // 10. our clients logo carousel

  $('.clients-carousel').owlCarousel({
    autoplay: true,
    loop: true,
    margin: 15,
    dots: false,
    slideTransition: 'linear',
    autoplayTimeout: 4500,
    autoplayHoverPause: true,
    autoplaySpeed: 4500,
    responsive: {
      0: {
        items: 2
      },
      500: {
        items: 3
      },
      600: {
        items: 4
      },
      800: {
        items: 5
      },
      1200: {
        items: 6
      }
    }
  }); // 11. mixitup portfolio

  $(function () {
    // 1. querySelector
    var containerEl = document.querySelector("#MixItUp"); // 2. Passing the configuration object inline
    //https://www.kunkalabs.com/mixitup/docs/configuration-object/

    if (typeof containerEl != 'undefined' && containerEl != null) {
      var mixer = mixitup(containerEl, {
        selectors: {
          control: '[data-mixitup-control]'
        },
        animation: {
          effects: "fade translateZ(-100px)"
        }
      });
    }
  }); // 16. countdown one

  $('#clock').countdown('2022/01/30', function (event) {
    $(this).html(event.strftime('' + '<div class="row">' + '<div class="col">' + '<h2 class="mb-1">%-D</h2>' + '<h6>روز</h6>' + '</div>' + '<div class="col">' + '<h2 class="mb-1">%H</h2>' + '<h6>ساعت</h6>' + '</div>' + '<div class="col">' + '<h2 class="mb-1">%M</h2>' + '<h6>دقیقه</h6>' + '</div>' + '<div class="col">' + '<h2 class="mb-1">%S</h2>' + '<h6>ثانیه</h6>' + '</div>' + '</div>'));
  }); // 17. Get a quote

  if ($("#getQuoteFrm").length) {
    $("#getQuoteFrm").validator().on("submit", function (event) {
      if (event.isDefaultPrevented()) {
        // handle the invalid form...
        submitMSG(false, '.sign-up-form-wrap');
      } else {
        // everything looks good!
        event.preventDefault();
        submitGetQuoteForm();
      }
    });
  }

  function submitGetQuoteForm() {
    // Initiate Variables With Form Content
    var name = $('#getQuoteFrm input[name="name"]').val();
    var email = $('#getQuoteFrm input[name="email"]').val();
    var subject = $('#getQuoteFrm input[name="subject"]').val();
    var message = $('#getQuoteFrm textarea[name="message"]').val();

    if (!$('#getQuoteFrm #exampleCheck1').is(":checked")) {
      submitMSG(false, '.sign-up-form-wrap');
      return;
    }

    $.ajax({
      type: "POST",
      url: "libs/quote-form-process.php",
      data: "name=" + name + "&email=" + email + "&subject=" + subject + "&message=" + message,
      success: function success(text) {
        if (text == "success") {
          quoteFormSuccess();
        } else {
          submitMSG(false, '.sign-up-form-wrap');
        }
      }
    });
  }

  function quoteFormSuccess() {
    $("#getQuoteFrm")[0].reset();
    submitMSG(true, '.sign-up-form-wrap');
  } // 18. Contact Form


  if ($("#contactForm").length) {
    $("#contactForm").validator().on("submit", function (event) {
      if (event.isDefaultPrevented()) {
        // handle the invalid form...
        submitMSG(false, '.contact');
      } else {
        // everything looks good!
        event.preventDefault();
        submitContactForm();
      }
    });
  }

  function submitContactForm() {
    // Initiate Variables With Form Content
    var name = $('#contactForm input[name="name"]').val();
    var email = $('#contactForm input[name="email"]').val();
    var message = $('#contactForm textarea[name="message"]').val();
    $.ajax({
      type: "POST",
      url: "libs/contact-form-process.php",
      data: "name=" + name + "&email=" + email + "&message=" + message,
      success: function (text) {
        if (text == "success") {
          formSuccess();
        } else {
          submitMSG(false, '.contact');
        }
      }
    });
  }

  function formSuccess() {
    $("#contactForm")[0].reset();
    submitMSG(true, '.contact');
  }

  function submitMSG(valid, parentSelector) {
    if (valid) {
      $(parentSelector + " .message-box").removeClass('d-none').addClass('d-block ');
      $(parentSelector + " .message-box div").removeClass('alert-danger').addClass('alert-success').text('Form submitted successfully');
    } else {
      $(parentSelector + " .message-box").removeClass('d-none').addClass('d-block ');
      $(parentSelector + " .message-box div").removeClass('alert-success').addClass('alert-danger').text('Found error in the form. Please check again.');
    }
  }
}); // JQuery end