/*
 *  Document   : helpers.js
 *  Author     : pixelcave
 *  Description: Various helpers for plugin inits or helper functionality
 *
 */

// Helper variables
let jqSparklineResize = false;
let jqSparklineTimeout;

// Helpers
export default class Helpers {
    /*
     * Run helpers
     *
     */
    static run(helpers, options = {}) {
        let helperList = {
            // Bootstrap
            'bs-tooltip': () => this.bsTooltip(),
            'bs-popover': () => this.bsPopover(),

            // Dashmix
            'dm-toggle-class': () => this.dmToggleClass(),
            'dm-year-copy': () => this.dmYearCopy(),
            'dm-ripple': () => this.dmRipple(),
            'dm-print': () => this.dmPrint(),
            'dm-table-tools-sections': () => this.dmTableToolsSections(),
            'dm-table-tools-checkable': () => this.dmTableToolsCheckable(),

            // JavaScript
            'js-ckeditor': () => this.jsCkeditor(),
            'js-ckeditor5': () => this.jsCkeditor5(),
            'js-simplemde': () => this.jsSimpleMDE(),
            'js-highlightjs': () => this.jsHighlightjs(),
            'js-flatpickr': () => this.jsFlatpickr(),

            // jQuery
            'jq-appear': () => this.jqAppear(),
            'jq-magnific-popup': () => this.jqMagnific(),
            'jq-slick': () => this.jqSlick(),
            'jq-datepicker': () => this.jqDatepicker(),
            'jq-masked-inputs': () => this.jqMaskedInputs(),
            'jq-select2': () => this.jqSelect2(),
            'jq-notify': (options) => this.jqNotify(options),
            'jq-easy-pie-chart': () => this.jqEasyPieChart(),
            'jq-maxlength': () => this.jqMaxlength(),
            'jq-rangeslider': () => this.jqRangeslider(),
            'jq-pw-strength': () => this.jqPwStrength(),
            'jq-sparkline': () => this.jqSparkline(),
            'jq-validation': () => this.jqValidation()
        };

        if (helpers instanceof Array) {
            for (let index in helpers) {
                if (helperList[helpers[index]]) {
                    helperList[helpers[index]](options);
                }
            }
        } else {
            if (helperList[helpers]) {
                helperList[helpers](options);
            }
        }
    }

    /*
     ********************************************************************************************
     *
     * Init helpers for Bootstrap plugins
     *
     *********************************************************************************************
     */

    /*
     * Bootstrap Tooltip, for more examples you can check out https://getbootstrap.com/docs/5.0/components/tooltips/
     *
     * Helpers.run('bs-tooltip');
     *
     * Example usage:
     *
     * <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" title="Tooltip Text">Example</button> or
     * <button type="button" class="btn btn-primary js-bs-tooltip" title="Tooltip Text">Example</button>
     *
     */
    static bsTooltip() {
        let elements = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]:not(.js-bs-tooltip-enabled), .js-bs-tooltip:not(.js-bs-tooltip-enabled)'));

        window.helperBsTooltips = elements.map(el => {
            // Add .js-bs-tooltip-enabled class to tag it as activated
            el.classList.add('js-bs-tooltip-enabled');

            // Init Bootstrap Tooltip
            return new bootstrap.Tooltip(el, {
                container: el.dataset.bsContainer || '#page-container',
                animation: el.dataset.bsAnimation && el.dataset.bsAnimation.toLowerCase() == 'true' ? true : false,
            })
        });
    }

    /*
     * Bootstrap Popover, for more examples you can check out https://getbootstrap.com/docs/5.0/components/popovers/
     *
     * Helpers.run('bs-popover');
     *
     * Example usage:
     *
     * <button type="button" class="btn btn-primary" data-bs-toggle="popover" title="Popover Title" data-bs-content="This is the content of the Popover">Example</button> or
     * <button type="button" class="btn btn-primary js-bs-popover" title="Popover Title" data-bs-content="This is the content of the Popover">Example</button>
     *
     */
    static bsPopover() {
        let elements = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]:not(.js-bs-popover-enabled), .js-bs-popover:not(.js-bs-popover-enabled)'));

        window.helperBsPopovers = elements.map(el => {
            // Add .js-bs-popover-enabled class to tag it as activated
            el.classList.add('js-bs-popover-enabled');

            // Init Bootstrap Popover
            return new bootstrap.Popover(el, {
                container: el.dataset.bsContainer || '#page-container',
                animation: el.dataset.bsAnimation && el.dataset.bsAnimation.toLowerCase() == 'true' ? true : false,
                trigger: el.dataset.bsTrigger || 'hover focus',
            })
        });
    }

    /*
     ********************************************************************************************
     *
     * JS helpers to add custom functionality
     *
     *********************************************************************************************
     */

    /*
     * Toggle class on element click
     *
     * Helpers.run('dm-toggle-class');
     *
     * Example usage (on button click, "exampleClass" class is toggled on the element with id "elementID"):
     *
     * <button type="button" class="btn btn-primary" data-toggle="class-toggle" data-target="#elementID" data-class="exampleClass">Toggle</button>
     *
     * or
     *
     * <button type="button" class="btn btn-primary js-class-toggle" data-target="#elementID" data-class="exampleClass">Toggle</button>
     *
     */
    static dmToggleClass() {
        let elements = document.querySelectorAll('[data-toggle="class-toggle"]:not(.js-class-toggle-enabled), .js-class-toggle:not(.js-class-toggle-enabled)');

        elements.forEach(el => {
            el.addEventListener('click', () => {
                // Add .js-class-toggle-enabled class to tag it as activated
                el.classList.add('js-class-toggle-enabled');

                // Get all classes
                let cssClasses = el.dataset.class ? el.dataset.class.split(' ') : false;

                // Toggle class on target elements
                document.querySelectorAll(el.dataset.target).forEach(targetEl => {
                    if (cssClasses) {
                        cssClasses.forEach(cls => {
                            targetEl.classList.toggle(cls);
                        });
                    }
                });
            });
        });
    }

    /*
     * Add the correct copyright year to an element
     *
     * Helpers.run('dm-year-copy');
     *
     * Example usage (it will get populated with current year if empty or will append it to specified year if needed):
     *
     * <span data-toggle="year-copy"></span> or
     * <span data-toggle="year-copy">2018</span>
     *
     */
    static dmYearCopy() {
        let elements = document.querySelectorAll('[data-toggle="year-copy"]:not(.js-year-copy-enabled)');

        elements.forEach(el => {
            let date = new Date();
            // let currentYear = date.getFullYear();
            let currentYear = "1401";
            let baseYear = el.textContent || currentYear;

            // Add .js-year-copy-enabled class to tag it as activated
            el.classList.add('js-year-copy-enabled');

            // Set the correct year
            el.textContent = (parseInt(baseYear) >= currentYear) ? currentYear : baseYear + '-' + currentYear.toString().substr(2, 2);
        });
    }

    /*
     * Ripple effect fuctionality
     *
     * Helpers.run('dm-ripple');
     *
     * Example usage:
     *
     * <button type="button" class="btn btn-primary" data-toggle="click-ripple">Click Me!</button>
     *
     */
    static dmRipple() {
        let elements = document.querySelectorAll('[data-toggle="click-ripple"]:not(.js-click-ripple-enabled)');

        elements.forEach(el => {
            // Add .js-click-ripple-enabled class to tag it as activated and init it
            el.classList.add('js-click-ripple-enabled');

            // Add custom CSS styles
            el.style.overflow = 'hidden';
            el.style.position = 'relative';
            el.style.zIndex = 1;

            // On click create and render the ripple
            el.addEventListener('click', e => {
                let cssClass = 'click-ripple';
                let ripple = el.querySelector('.' + cssClass);
                let d, x, y;

                // If the ripple element exists in this element, remove .animate class from ripple element..
                if (ripple) {
                    ripple.classList.remove('animate');
                } else { // ..else add it
                    let elChild = document.createElement('span')

                    elChild.classList.add(cssClass)
                    el.insertBefore(elChild, el.firstChild);
                }

                // Get the ripple element
                ripple = el.querySelector('.' + cssClass);

                // If the ripple element doesn't have dimensions, set them accordingly
                if ((getComputedStyle(ripple).height === '0px') || (getComputedStyle(ripple).width === '0px')) {
                    d = Math.max(el.offsetWidth, el.offsetHeight)

                    ripple.style.height = d + 'px'
                    ripple.style.width = d + 'px'
                }

                // Get coordinates for our ripple element
                x = e.pageX - (el.getBoundingClientRect().left + window.scrollX) - parseFloat(getComputedStyle(ripple).width.replace('px', '')) / 2
                y = e.pageY - (el.getBoundingClientRect().top + window.scrollY) - parseFloat(getComputedStyle(ripple).height.replace('px', '')) / 2

                // Position the ripple element and add the class .animate to it
                ripple.style.top = y + 'px'
                ripple.style.left = x + 'px'
                ripple.classList.add('animate')
            });
        });
    }

    /*
     * Print Page functionality
     *
     * Helpers.run('dm-print');
     *
     */
    static dmPrint() {
        // Store all #page-container classes
        let lPage = document.getElementById('page-container');
        let pageCls = lPage.className;

        console.log(pageCls);

        // Remove all classes from #page-container
        lPage.classList = '';

        // Print the page
        window.print();

        // Restore all #page-container classes
        lPage.classList = pageCls;
    }

    /*
     * Table sections functionality
     *
     * Helpers.run('dm-table-tools-sections');
     *
     * Example usage:
     *
     * Please check out the Table Helpers page for complete markup examples
     *
     */
    static dmTableToolsSections() {
        let tables = document.querySelectorAll('.js-table-sections:not(.js-table-sections-enabled)');

        tables.forEach(table => {
            // Add .js-table-sections-enabled class to tag it as activated
            table.classList.add('js-table-sections-enabled');

            // When a row is clicked in tbody.js-table-sections-header
            table.querySelectorAll('.js-table-sections-header > tr').forEach(tr => {
                tr.addEventListener('click', e => {
                    if (e.target.type !== 'checkbox'
                        && e.target.type !== 'button'
                        && e.target.tagName.toLowerCase() !== 'a'
                        && e.target.parentNode.nodeName.toLowerCase() !== 'a'
                        && e.target.parentNode.nodeName.toLowerCase() !== 'button'
                        && e.target.parentNode.nodeName.toLowerCase() !== 'label'
                        && !e.target.parentNode.classList.contains('custom-control')) {
                        let tbody = tr.parentNode;
                        let tbodyAll = table.querySelectorAll('tbody');

                        if (!tbody.classList.contains('show')) {
                            if (tbodyAll) {
                                tbodyAll.forEach(tbodyEl => {
                                    tbodyEl.classList.remove('show');
                                    tbodyEl.classList.remove('table-active');
                                });
                            }
                        }

                        tbody.classList.toggle('show');
                        tbody.classList.toggle('table-active');
                    }
                });
            });
        });
    }

    /*
     * Checkable table functionality
     *
     * Helpers.run('dm-table-tools-checkable');
     *
     * Example usage:
     *
     * Please check out the Table Helpers page for complete markup examples
     *
     */
    static dmTableToolsCheckable() {
        let tables = document.querySelectorAll('.js-table-checkable:not(.js-table-checkable-enabled)');

        tables.forEach(table => {
            // Add .js-table-checkable-enabled class to tag it as activated
            table.classList.add('js-table-checkable-enabled');

            // When a checkbox is clicked in thead
            table.querySelector('thead input[type=checkbox]').addEventListener('click', e => {
                // Check or uncheck all checkboxes in tbody
                table.querySelectorAll('tbody input[type=checkbox]').forEach(checkbox => {
                    checkbox.checked = e.currentTarget.checked;

                    // Update Row classes
                    this.tableToolscheckRow(checkbox, e.currentTarget.checked);
                });
            });

            // When a checkbox is clicked in tbody
            table.querySelectorAll('tbody input[type=checkbox], tbody input + label').forEach(checkbox => {
                checkbox.addEventListener('click', e => {
                    let checkboxHead = table.querySelector('thead input[type=checkbox]');

                    // Adjust checkbox in thead
                    if (!checkbox.checked) {
                        checkboxHead.checked = false
                    } else {
                        if (table.querySelectorAll('tbody input[type=checkbox]:checked').length === table.querySelectorAll('tbody input[type=checkbox]').length) {
                            checkboxHead.checked = true;
                        }
                    }

                    // Update Row classes
                    this.tableToolscheckRow(checkbox, checkbox.checked);
                });
            });

            // When a row is clicked in tbody
            table.querySelectorAll('tbody > tr').forEach(tr => {
                tr.addEventListener('click', e => {
                    if (e.target.type !== 'checkbox'
                        && e.target.type !== 'button'
                        && e.target.tagName.toLowerCase() !== 'a'
                        && e.target.parentNode.nodeName.toLowerCase() !== 'a'
                        && e.target.parentNode.nodeName.toLowerCase() !== 'button'
                        && e.target.parentNode.nodeName.toLowerCase() !== 'label'
                        && !e.target.parentNode.classList.contains('custom-control')) {
                        let checkboxHead = table.querySelector('thead input[type=checkbox]');
                        let checkbox = e.currentTarget.querySelector('input[type=checkbox]');

                        // Update row's checkbox status
                        checkbox.checked = !checkbox.checked;

                        // Update Row classes
                        this.tableToolscheckRow(checkbox, checkbox.checked);

                        // Adjust checkbox in thead
                        if (!checkbox.checked) {
                            checkboxHead.checked = false
                        } else {
                            if (table.querySelectorAll('tbody input[type=checkbox]:checked').length === table.querySelectorAll('tbody input[type=checkbox]').length) {
                                checkboxHead.checked = true;
                            }
                        }
                    }
                });
            });
        });
    }

    // Checkable table functionality helper - Checks or unchecks table row
    static tableToolscheckRow(checkbox, checkedStatus) {
        if (checkedStatus) {
            checkbox.closest('tr').classList.add('table-active');
        } else {
            checkbox.closest('tr').classList.remove('table-active');
        }
    }

    /*
     ********************************************************************************************
     *
     * Init helpers for pure JS libraries
     *
     ********************************************************************************************
     */

    /*
     * CKEditor init, for more examples you can check out http://ckeditor.com/
     *
     * Helpers.run('jsCkeditor');
     *
     * Example usage:
     *
     * <textarea id="js-ckeditor" name="ckeditor">Hello CKEditor!</textarea> or
     * <div id="js-ckeditor-inline">Hello inline CKEditor!</div>
     *
     */
    static jsCkeditor() {
        let ckeditorInline = document.querySelector('#js-ckeditor-inline:not(.js-ckeditor-inline-enabled)');
        let ckeditorFull = document.querySelector('#js-ckeditor:not(.js-ckeditor-enabled)');

        // Init inline text editor
        if (ckeditorInline) {
            ckeditorInline.setAttribute('contenteditable', 'true');
            CKEDITOR.inline('js-ckeditor-inline');

            // Add .js-ckeditor-inline-enabled class to tag it as activated
            ckeditorInline.classList.add('js-ckeditor-inline-enabled');
        }

        // Init full text editor
        if (ckeditorFull) {
            CKEDITOR.replace('js-ckeditor');

            // Add .js-ckeditor-enabled class to tag it as activated
            ckeditorFull.classList.add('js-ckeditor-enabled');
        }
    }

    /*
     * CKEditor 5 init, for more examples you can check out http://ckeditor.com/
     *
     * Helpers.run('js-ckeditor5');
     *
     * Example usage:
     *
     * <div id="js-ckeditor5-classic">Hello classic CKEditor 5!</div>
     * ..or..
     * <div id="js-ckeditor5-inline">Hello inline CKEditor 5!</div>
     *
     */
    static jsCkeditor5() {
        let ckeditor5Inline = document.querySelector('#js-ckeditor5-inline');
        let ckeditor5Full = document.querySelector('#js-ckeditor5-classic');

        // Init inline text editor
        if (ckeditor5Inline) {
            InlineEditor
                .create(document.querySelector('#js-ckeditor5-inline'))
                .then(editor => {
                    window.editor = editor;
                })
                .catch(error => {
                    console.error('There was a problem initializing the inline editor.', error);
                });
        }

        // Init full text editor
        if (ckeditor5Full) {
            ClassicEditor
                .create(document.querySelector('#js-ckeditor5-classic'))
                .then(editor => {
                    window.editor = editor;
                })
                .catch(error => {
                    console.error('There was a problem initializing the classic editor.', error);
                });
        }
    }

    /*
     * SimpleMDE init, for more examples you can check out https://github.com/NextStepWebs/simplemde-markdown-editor
     *
     * Helpers.run('js-simplemde');
     *
     * Example usage:
     *
     * <textarea class="js-simplemde" id="simplemde" name="simplemde">Hello SimpleMDE!</textarea>
     *
     */
    static jsSimpleMDE() {
        let elements = document.querySelectorAll('.js-simplemde');

        elements.forEach(el => {
            // Init editor
            new SimpleMDE({ element: el, autoDownloadFontAwesome: false });
        });

        // Fix: Change SimpleMDE's Font Awesome 4 Icons with Font Awesome 6
        if (elements) {
            document.querySelector('.editor-toolbar > a.fa-header').classList.replace('fa-header', 'fa-heading');
            document.querySelector('.editor-toolbar > a.fa-picture-o').classList.replace('fa-picture-o', 'fa-image');
        }
    }

    /*
     * Highlight.js, for more examples you can check out https://highlightjs.org/usage/
     *
     * Helpers.run('js-highlightjs');
     *
     * Example usage:
     *
     * Please check out the Syntax Highlighting page in Components for complete markup examples
     *
     */
    static jsHighlightjs() {
        // Init Highlight.js
        if (!hljs.isHighlighted) {
            hljs.initHighlighting();
        }
    }

    /*
     * Flatpickr init, for more examples you can check out https://github.com/flatpickr/flatpickr
     *
     * Helpers.run('js-flatpickr');
     *
     * Example usage:
     *
     * <input type="text" class="js-flatpickr form-control">
     *
     */
    static jsFlatpickr() {

        window.Date = window.JDate;
        let elements = document.querySelectorAll('.js-flatpickr:not(.js-flatpickr-enabled)');

        elements.forEach(el => {
            // Add .js-flatpickr-enabled class to tag it as activated
            el.classList.add('js-flatpickr-enabled');

            // Init it
            flatpickr(el, {
                locale: {
                    weekdays: {
                        shorthand: ['یک', 'دو', 'سه', 'چهار', 'پنج', 'جمعه', 'شنبه'],
                        longhand: ["یک‌شنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه", "پنچ‌شنبه", "جمعه", "شنبه",],
                    },
                    months: {
                        shorthand: ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"],
                        longhand: ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"],
                    },
                    rangeSeparator: " - ",
                    weekAbbreviation: "هفته",
                    scrollTitle: "Scroll to increment",
                    toggleTitle: "Click to toggle",
                    yearAriaLabel: "سال",
                    monthAriaLabel: "ماه",
                    hourAriaLabel: "ساعت",
                    minuteAriaLabel: "دقیقه",
                    amPM: ["صبح", "بعدازظهر"],
                    // daysInMonth: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
                    firstDayOfWeek: 6,
                }
            });
        });
    }

    /*
     ********************************************************************************************
     *
     * Init helpers for jQuery plugins
     *
     ********************************************************************************************
     */

    /*
     * jQuery Appear, for more examples you can check out https://github.com/bas2k/jquery.appear
     *
     * Helpers.run('jq-appear');
     *
     * Example usage (the following div will appear on scrolling, remember to add the invisible class):
     *
     * <div class="invisible" data-toggle="appear">...</div>
     *
     */
    static jqAppear() {
        // Add a specific class on elements (when they become visible on scrolling)
        jQuery('[data-toggle="appear"]:not(.js-appear-enabled)').each((index, element) => {
            let windowW = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
            let el = jQuery(element);
            let elCssClass = el.data('class') || 'animated fadeIn';
            let elOffset = el.data('offset') || 0;
            let elTimeout = (windowW < 992) ? 0 : (el.data('timeout') ? el.data('timeout') : 0);

            // Add .js-appear-enabled class to tag it as activated and init it
            el.addClass('js-appear-enabled').appear(() => {
                setTimeout(() => {
                    el.removeClass('invisible').addClass(elCssClass);
                }, elTimeout);
            }, { accY: elOffset });
        });
    }

    /*
     * Magnific Popup functionality, for more examples you can check out http://dimsemenov.com/plugins/magnific-popup/
     *
     * Helpers.run('jq-magnific-popup');
     *
     * Example usage:
     *
     * Please check out the Gallery page in Components for complete markup examples
     *
     */
    static jqMagnific() {
        // Gallery init
        jQuery('.js-gallery:not(.js-gallery-enabled)').each((index, element) => {
            // Add .js-gallery-enabled class to tag it as activated and init it
            jQuery(element).addClass('js-gallery-enabled').magnificPopup({
                delegate: 'a.img-lightbox',
                type: 'image',
                gallery: {
                    enabled: true,
                    tPrev: 'قبلی (دکمه جهت چپ)', // Alt text on left arrow
                    tNext: 'بعدی (دکمه جهت راست)', // Alt text on right arrow
                    tCounter: '%curr% از %total%' // Markup for "1 of 7" counter
                },
                tClose: 'بستن (Esc)', // Alt text on close button
                tLoading: 'درحال بارگذاری...', // Text that is displayed during loading. Can contain %curr% and %total% keys
                image: {
                    tError: '<a href="%url%">عکس</a> لود نشد.' // Error message when image could not be loaded
                },
                ajax: {
                    tError: '<a href="%url%">متن</a> لود نشد.' // Error message when ajax request failed
                }
            });
        });
    }

    /*
     * Slick init, for more examples you can check out http://kenwheeler.github.io/slick/
     *
     * Helpers.run('jq-slick');
     *
     * Example usage:
     *
     * <div class="js-slider">
     *   <div>Slide #1</div>
     *   <div>Slide #2</div>
     *   <div>Slide #3</div>
     * </div>
     *
     */
    static jqSlick() {
        // Get each slider element (with .js-slider class)
        jQuery('.js-slider:not(.js-slider-enabled)').each((index, element) => {
            let el = jQuery(element);

            // Add .js-slider-enabled class to tag it as activated and init it
            el.addClass('js-slider-enabled').slick({
                rtl: true,
                arrows: el.data('arrows') || false,
                dots: el.data('dots') || false,
                slidesToShow: el.data('slides-to-show') || 1,
                centerMode: el.data('center-mode') || false,
                autoplay: el.data('autoplay') || false,
                autoplaySpeed: el.data('autoplay-speed') || 3000,
                infinite: typeof el.data('infinite') === 'undefined' ? true : el.data('infinite')
            });
        });
    }

    /*
     * Bootstrap Datepicker init, for more examples you can check out https://github.com/eternicode/bootstrap-datepicker
     *
     * Helpers.run('jq-datepicker');
     *
     * Example usage:
     *
     * <input type="text" class="js-datepicker form-control">
     *
     */
    static jqDatepicker() {
        // Init datepicker (with .js-datepicker and .input-daterange class)
        jQuery('.js-datepicker:not(.js-datepicker-enabled)').add('.input-daterange:not(.js-datepicker-enabled)').each((index, element) => {
            let el = jQuery(element);
            $.fn.datepicker.dates['faa'] = {
                days: ["یک‌شنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه", "پنچ‌شنبه", "جمعه", "شنبه"],
                daysShort: ['یک', 'دو', 'سه', 'چهار', 'پنج', 'جمعه', 'شنبه'],
                daysMin: ['یک', 'دو', 'سه', 'چهار', 'پنج', 'جمعه', 'شنبه'],
                months: ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"],
                monthsShort: ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"],
                today: "امروز",
                clear: "پاک کردن",
                format: "mm/dd/yyyy",
                titleFormat: "MM yyyy", /* Leverages same syntax as 'format' */
                rtl: true
            };
            // Add .js-datepicker-enabled class to tag it as activated and init it
            el.addClass('js-datepicker-enabled').datepicker({
                weekStart: el.data('week-start') || 0,
                autoclose: el.data('autoclose') || false,
                todayHighlight: el.data('today-highlight') || false,
                startDate: el.data('start-date') || false,
                container: el.data('container') || '#page-container',
                orientation: 'bottom', // Position issue when using BS5, set it to bottom until officially supported
                language: "faa",
                weekStart: 6,
            });
        });
    }

    /*
     * Masked Inputs, for more examples you can check out https://github.com/digitalBush/jquery.maskedinput
     *
     * Helpers.run('jq-masked-inputs');
     *
     * Example usage:
     *
     * Please check out the Form plugins page for complete markup examples
     *
     */
    static jqMaskedInputs() {
        // Init Masked Inputs
        // a - Represents an alpha character (A-Z,a-z)
        // 9 - Represents a numeric character (0-9)
        // * - Represents an alphanumeric character (A-Z,a-z,0-9)
        jQuery('.js-masked-date:not(.js-masked-enabled)').mask('99/99/9999');
        jQuery('.js-masked-date-dash:not(.js-masked-enabled)').mask('99-99-9999');
        jQuery('.js-masked-phone:not(.js-masked-enabled)').mask('(999) 999-9999');
        jQuery('.js-masked-phone-ext:not(.js-masked-enabled)').mask('(999) 999-9999? x99999');
        jQuery('.js-masked-taxid:not(.js-masked-enabled)').mask('99-9999999');
        jQuery('.js-masked-ssn:not(.js-masked-enabled)').mask('999-99-9999');
        jQuery('.js-masked-pkey:not(.js-masked-enabled)').mask('a*-999-a999');
        jQuery('.js-masked-time:not(.js-masked-enabled)').mask('99:99');

        jQuery('.js-masked-date')
            .add('.js-masked-date-dash')
            .add('.js-masked-phone')
            .add('.js-masked-phone-ext')
            .add('.js-masked-taxid')
            .add('.js-masked-ssn')
            .add('.js-masked-pkey')
            .add('.js-masked-time')
            .addClass('js-masked-enabled');
    }

    /*
     * Select2, for more examples you can check out https://github.com/select2/select2
     *
     * Helpers.run('jq-select2');
     *
     * Example usage:
     *
     * <select class="js-select2 form-control" style="width: 100%;" data-placeholder="Choose one..">
     *   <option></option><!-- Required for data-placeholder attribute to work with Select2 plugin -->
     *   <option value="1">HTML</option>
     *   <option value="2">CSS</option>
     *   <option value="3">Javascript</option>
     * </select>
     *
     */
    static jqSelect2() {
        // Init Select2 (with .js-select2 class)
        jQuery('.js-select2:not(.js-select2-enabled)').each((index, element) => {
            let el = jQuery(element);

            // Add .js-select2-enabled class to tag it as activated and init it
            el.addClass('js-select2-enabled').select2({
                dir: "rtl",
                placeholder: el.data('placeholder') || false,
                dropdownParent: document.querySelector(el.data('container') || '#page-container'),
            });
        });
    }

    /*
     * Bootstrap Notify, for more examples you can check out http://bootstrap-growl.remabledesigns.com/
     *
     * Helpers.run('jq-notify');
     *
     * Example usage:
     *
     * Please check out the Notifications page for examples
     *
     */
    static jqNotify(options = {}) {
        if (jQuery.isEmptyObject(options)) {
            // Init notifications (with .js-notify class)
            jQuery('.js-notify:not(.js-notify-enabled)').each((index, element) => {
                // Add .js-notify-enabled class to tag it as activated and init it
                jQuery(element).addClass('js-notify-enabled').on('click.pixelcave.helpers', e => {
                    let el = jQuery(e.currentTarget);

                    // Create notification
                    jQuery.notify({
                        icon: el.data('icon') || '',
                        message: el.data('message'),
                        url: el.data('url') || ''
                    },
                        {
                            element: 'body',
                            type: el.data('type') || 'info',
                            placement: {
                                from: el.data('from') || 'top',
                                align: el.data('align') || 'right'
                            },
                            allow_dismiss: true,
                            newest_on_top: true,
                            showProgressbar: false,
                            offset: 20,
                            spacing: 10,
                            z_index: 1033,
                            delay: 5000,
                            timer: 1000,
                            animate: {
                                enter: 'animated fadeIn',
                                exit: 'animated fadeOutDown'
                            },
                            template: `<div data-notify="container" class="col-11 col-sm-4 alert alert-{0} alert-dismissible" role="alert">
  <p class="mb-0">
    <span data-notify="icon"></span>
    <span data-notify="title">{1}</span>
    <span data-notify="message">{2}</span>
  </p>
  <div class="progress" data-notify="progressbar">
    <div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
  </div>
  <a href="{3}" target="{4}" data-notify="url"></a>
  <a class="p-2 m-1 text-dark" href="javascript:void(0)" aria-label="Close" data-notify="dismiss">
    <i class="fa fa-times"></i>
  </a>
</div>`
                        });
                });
            });
        } else {
            // Create notification
            jQuery.notify({
                icon: options.icon || '',
                message: options.message,
                url: options.url || ''
            },
                {
                    element: options.element || 'body',
                    type: options.type || 'info',
                    placement: {
                        from: options.from || 'top',
                        align: options.align || 'right'
                    },
                    allow_dismiss: (options.allow_dismiss === false) ? false : true,
                    newest_on_top: (options.newest_on_top === false) ? false : true,
                    showProgressbar: options.show_progress_bar ? true : false,
                    offset: options.offset || 20,
                    spacing: options.spacing || 10,
                    z_index: options.z_index || 1033,
                    delay: options.delay || 5000,
                    timer: options.timer || 1000,
                    animate: {
                        enter: options.animate_enter || 'animated fadeIn',
                        exit: options.animate_exit || 'animated fadeOutDown'
                    },
                    template: `<div data-notify="container" class="col-11 col-sm-4 alert alert-{0} alert-dismissible" role="alert">
  <p class="mb-0">
    <span data-notify="icon"></span>
    <span data-notify="title">{1}</span>
    <span data-notify="message">{2}</span>
  </p>
  <div class="progress" data-notify="progressbar">
    <div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
  </div>
  <a href="{3}" target="{4}" data-notify="url"></a>
  <a class="p-2 m-1 text-dark" href="javascript:void(0)" aria-label="Close" data-notify="dismiss">
    <i class="fa fa-times"></i>
  </a>
</div>`
                });
        }
    }

    /*
     * Easy Pie Chart, for more examples you can check out http://rendro.github.io/easy-pie-chart/
     *
     * Helpers.run('jq-easy-pie-chart');
     *
     * Example usage:
     *
     * <div class="js-pie-chart pie-chart" data-percent="25" data-line-width="2" data-size="100">
     *   <span>..Content..</span>
     * </div>
     *
     */
    static jqEasyPieChart() {
        // Init Easy Pie Charts (with .js-pie-chart class)
        jQuery('.js-pie-chart:not(.js-pie-chart-enabled)').each((index, element) => {
            let el = jQuery(element);

            // Add .js-pie-chart-enabled class to tag it as activated and init it
            el.addClass('js-pie-chart-enabled').easyPieChart({
                barColor: el.data('bar-color') || '#777777',
                trackColor: el.data('track-color') || '#eeeeee',
                lineWidth: el.data('line-width') || 3,
                size: el.data('size') || '80',
                animate: el.data('animate') || 750,
                scaleColor: el.data('scale-color') || false
            });
        });
    }

    /*
     * Bootstrap Maxlength, for more examples you can check out https://github.com/mimo84/bootstrap-maxlength
     *
     * Helpers.run('jq-maxlength');
     *
     * Example usage:
     *
     * <input type="text" class="js-maxlength form-control" maxlength="20">
     *
     */
    static jqMaxlength() {
        // Init Bootstrap Maxlength (with .js-maxlength class)
        jQuery('.js-maxlength:not(.js-maxlength-enabled)').each((index, element) => {
            let el = jQuery(element);

            // Add .js-maxlength-enabled class to tag it as activated and init it
            el.addClass('js-maxlength-enabled').maxlength({
                alwaysShow: el.data('always-show') ? true : false,
                threshold: el.data('threshold') || 10,
                warningClass: el.data('warning-class') || 'badge bg-warning',
                limitReachedClass: el.data('limit-reached-class') || 'badge bg-danger',
                placement: el.data('placement') || 'bottom',
                preText: el.data('pre-text') || '',
                separator: el.data('separator') || '/',
                postText: el.data('post-text') || ''
            });
        });
    }

    /*
     * Ion Range Slider, for more examples you can check out https://github.com/IonDen/ion.rangeSlider
     *
     * Helpers.run('jq-rangeslider');
     *
     * Example usage:
     *
     * <input type="text" class="js-rangeslider form-control" value="50">
     *
     */
    static jqRangeslider() {
        // Init Ion Range Slider (with .js-rangeslider class)
        jQuery('.js-rangeslider:not(.js-rangeslider-enabled)').each((index, element) => {
            let el = jQuery(element);

            // Add .js-rangeslider-enabled class to tag it as activated and init it
            jQuery(element).addClass('js-rangeslider-enabled').ionRangeSlider({
                input_values_separator: ';',
                skin: el.data('skin') || 'round'
            });
        });
    }

    /*
     * Password Strength Meter, for more examples you can check out https://github.com/ablanco/jquery.pwstrength.bootstrap
     *
     * Helpers.run('jq-pw-strength');
     *
     * Example usage:
     *
     * <div class="js-pw-strength-container mb-4">
     *   <label class="form-label" for="example-pw-strength1">Password</label>
     *   <input type="password" class="js-pw-strength form-control" id="example-pw-strength1" name="example-pw-strength1">
     *   <div class="js-pw-strength-progress pw-strength-progress mt-1"></div>
     *   <p class="js-pw-strength-feedback form-text mb-0"></p>
     * </div>
     *
     */
    static jqPwStrength() {
        // Init Password Strength Meter (with .js-pw-strength class)
        jQuery('.js-pw-strength:not(.js-pw-strength-enabled)').each((index, element) => {
            let el = jQuery(element);
            let container = el.parents('.js-pw-strength-container');
            let progress = jQuery('.js-pw-strength-progress', container);
            let verdict = jQuery('.js-pw-strength-feedback', container);
            var i18n = {};
            i18n.fallback = {
                wordMinLength: 'پسورد شما خیلی کوتاه است',
                wordMaxLength: 'پسورد شما خیلی طولانی است',
                wordInvalidChar: 'رمز عبور شما حاوی یک کاراکتر نامعتبر است',
                wordNotEmail: 'از ایمیل خود به عنوان رمز عبور استفاده نکنید',
                wordSimilarToUsername: 'رمز عبور شما نمی تواند شامل نام کاربری شما باشد',
                wordTwoCharacterClasses: 'از کلاس های شخصیت های مختلف استفاده کنید',
                wordRepetitions: 'تکرار خیلی زیاد',
                wordSequences: 'رمز عبور شما حاوی توالی است',
                errorList: 'خطاها:',
                veryWeak: 'خیلی ضعیف',
                weak: 'ضعیف',
                normal: 'عادی',
                medium: 'معمولی',
                strong: 'قوی',
                veryStrong: 'خیلی قوی'
            };
            // Add .js-pw-strength-enabled class to tag it as activated and init it
            el.addClass('js-pw-strength-enabled').pwstrength({
                ui: {
                    container: container,
                    viewports: {
                        progress: progress,
                        verdict: verdict
                    }
                },
                i18n: {
                    t: function (key) {
                        var result = '';

                        result = i18n.fallback[key];

                        return result === key ? '' : result;
                    }
                }
            });
        });
    }

    /*
     * jQuery Sparkline Charts, for more examples you can check out http://omnipotent.net/jquery.sparkline/#s-docs
     *
     * Helpers.run('jq-sparkline');
     *
     * Example usage:
     *
     * <span class="js-sparkline" data-type="line" data-points="[10,20,30,25,15,40,45]"></span>
     *
     */
    static jqSparkline() {
        let self = this;

        // Init jQuery Sparkline Charts (with .js-sparkline class)
        jQuery('.js-sparkline:not(.js-sparkline-enabled)').each((index, element) => {
            let el = jQuery(element);
            let type = el.data('type');
            let options = {};

            // Sparkline types
            let types = {
                line: () => {
                    options['type'] = type;
                    options['lineWidth'] = el.data('line-width') || 2;
                    options['lineColor'] = el.data('line-color') || '#0665d0';
                    options['fillColor'] = el.data('fill-color') || '#0665d0';
                    options['spotColor'] = el.data('spot-color') || '#495057';
                    options['minSpotColor'] = el.data('min-spot-color') || '#495057';
                    options['maxSpotColor'] = el.data('max-spot-color') || '#495057';
                    options['highlightSpotColor'] = el.data('highlight-spot-color') || '#495057';
                    options['highlightLineColor'] = el.data('highlight-line-color') || '#495057';
                    options['spotRadius'] = el.data('spot-radius') || 2;
                    options['tooltipFormat'] = '{{prefix}}{{y}}{{suffix}}';
                },
                bar: () => {
                    options['type'] = type;
                    options['barWidth'] = el.data('bar-width') || 8;
                    options['barSpacing'] = el.data('bar-spacing') || 6;
                    options['barColor'] = el.data('bar-color') || '#0665d0';
                    options['tooltipFormat'] = '{{prefix}}{{value}}{{suffix}}';
                },
                pie: () => {
                    options['type'] = type;
                    options['sliceColors'] = ['#fadb7d', '#faad7d', '#75b0eb', '#abe37d'];
                    options['highlightLighten'] = el.data('highlight-lighten') || 1.1;
                    options['tooltipFormat'] = '{{prefix}}{{value}}{{suffix}}';
                },
                tristate: () => {
                    options['type'] = type;
                    options['barWidth'] = el.data('bar-width') || 8;
                    options['barSpacing'] = el.data('bar-spacing') || 6;
                    options['posBarColor'] = el.data('pos-bar-color') || '#82b54b';
                    options['negBarColor'] = el.data('neg-bar-color') || '#e04f1a';
                }
            };

            // If the correct type is set init the chart
            if (types[type]) {
                types[type]();

                // Extra options added only if specified
                if (type === 'line') {
                    if (el.data('chart-range-min') >= 0 || el.data('chart-range-min')) {
                        options['chartRangeMin'] = el.data('chart-range-min');
                    }

                    if (el.data('chart-range-max') >= 0 || el.data('chart-range-max')) {
                        options['chartRangeMax'] = el.data('chart-range-max');
                    }
                }

                // Add common options used in all types
                options['width'] = el.data('width') || '120px';
                options['height'] = el.data('height') || '80px';
                options['tooltipPrefix'] = el.data('tooltip-prefix') ? el.data('tooltip-prefix') + ' ' : '';
                options['tooltipSuffix'] = el.data('tooltip-suffix') ? ' ' + el.data('tooltip-suffix') : '';

                // If we need a responsive width for the chart, then don't add .js-sparkline-enabled class and re-run the helper on window resize
                if (options['width'] === '100%') {
                    if (!jqSparklineResize) {
                        // Make sure that we bind the event only once
                        jqSparklineResize = true;

                        // On window resize, re-run the Sparkline helper
                        jQuery(window).on('resize.pixelcave.helpers.sparkline', function (e) {
                            clearTimeout(jqSparklineTimeout);

                            jqSparklineTimeout = setTimeout(() => {
                                self.sparkline();
                            }, 500);
                        });
                    }
                } else {
                    // It has a specific width (it doesn't need to re-init again on resize), so add .js-sparkline-enabled class to tag it as activated
                    jQuery(element).addClass('js-sparkline-enabled');
                }

                // Finally init it
                jQuery(element).sparkline(el.data('points') || [0], options);
            } else {
                console.log('[jQuery Sparkline JS Helper] Please add a correct type (line, bar, pie or tristate) in all your elements with \'js-sparkline\' class.')
            }
        });
    }

    /*
     * jQuery Validation, for more examples you can check out https://github.com/jzaefferer/jquery-validation
     *
     * Helpers.run('jq-validation');
     *
     * Example usage:
     *
     * By calling the helper, you set up the default options that will be used for jQuery Validation
     *
     */
    static jqValidation() {
        // Set default options for jQuery Validation plugin
        jQuery.validator.setDefaults({
            errorClass: 'invalid-feedback animated fadeIn',
            errorElement: 'div',
            errorPlacement: (error, el) => {
                jQuery(el).addClass('is-invalid');
                jQuery(el).parents('div:not(.input-group)').first().append(error);
            },
            highlight: el => {
                jQuery(el).parents('div:not(.input-group)').first().find('.is-invalid').removeClass('is-invalid').addClass('is-invalid');
            },
            success: el => {
                jQuery(el).parents('div:not(.input-group)').first().find('.is-invalid').removeClass('is-invalid');
                jQuery(el).remove();
            }
        });

        // Add custom email validation rule which requires a dot after @
        jQuery.validator.addMethod('emailWithDot', function (value, element) {
            let reg = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
            return this.optional(element) || reg.test(value);
        }, 'لطفا یک آدرس ایمیل معتبر وارد کنید');
    }
}
