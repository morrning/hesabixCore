/*
 *  Document   : template.js
 *  Author     : pixelcave
 *  Description: UI Framework custom functionality
 *
 */

// Imports
import * as bootstrap from 'bootstrap';
import SimpleBar from 'simplebar';
import Helpers from './helpers';

// Assignments
window.bootstrap = bootstrap;
window.SimpleBar = SimpleBar;

// Template
export default class Template {
  /*
   * Auto called when creating a new instance
   *
   */
  constructor() {
    this.onLoad(() => this._uiInit());
  }

  /*
   * Init all vital functionality
   *
   */
  _uiInit() {
    // Layout variables
    this._lHtml = document.documentElement;
    this._lBody = document.body;
    this._lpageLoader = document.getElementById('page-loader');
    this._lPage = document.getElementById('page-container');
    this._lSidebar = document.getElementById('sidebar');
    this._lSidebarScrollCon = this._lSidebar && this._lSidebar.querySelector('.js-sidebar-scroll');
    this._lSideOverlay = document.getElementById('side-overlay');
    this._lResize = false;
    this._lHeader = document.getElementById('page-header');
    this._lHeaderSearch = document.getElementById('page-header-search');
    this._lHeaderSearchInput = document.getElementById('page-header-search-input');
    this._lHeaderLoader = document.getElementById('page-header-loader');
    this._lMain = document.getElementById('main-container');
    this._lFooter = document.getElementById('page-footer');

    // Helper variables
    this._lSidebarScroll = false;
    this._lSideOverlayScroll = false;

    // Base UI Init
    this._uiHandleTheme();
    this._uiHandleDarkMode();
    this._uiHandleSidebars();
    this._uiHandleHeader();
    this._uiHandleNav();

    // API Init
    this._uiApiLayout();
    this._uiApiBlocks();

    // Init the following helpers by default
    this.helpers([
      'bs-tooltip',
      'bs-popover',
      'dm-toggle-class',
      'dm-year-copy',
      'dm-ripple',
    ]);

    // Page Loader (hide it)
    this._uiHandlePageLoader();
  }

  /*
   * Handles sidebar and side overlay scrolling functionality/styles
   *
   */
  _uiHandleSidebars(mode = 'init') {
    let self = this;

    if (self._lSidebar || self._lSideOverlay) {
      if (mode === 'init') {
        // Add 'side-trans-enabled' class to #page-container (enables sidebar and side overlay transition on open/close)
        // Fixes IE10, IE11 and Edge bug in which animation was executed on each page load
        self._lPage.classList.add('side-trans-enabled');

        // Remove side transitions on window resizing
        window.addEventListener('resize', () => {
          clearTimeout(self._lResize);

          self._lPage.classList.remove('side-trans-enabled');

          self._lResize = setTimeout(() => { self._lPage.classList.add('side-trans-enabled'); }, 500);
        });

        // Init custom scrolling
        this._uiHandleSidebars('custom-scroll');
      } else if (mode = 'custom-scroll') {
        // If .side-scroll is added to #page-container, enable custom scrolling
        if (self._lPage.classList.contains('side-scroll')) {
          // Init custom scrolling on Sidebar
          if ((self._lSidebar) && !self._lSidebarScroll) {
            self._lSidebarScroll = new SimpleBar(self._lSidebarScrollCon);
          }

          // Init custom scrolling on Side Overlay
          if ((self._lSideOverlay) && !self._lSideOverlayScroll) {
            self._lSideOverlayScroll = new SimpleBar(self._lSideOverlay);
          }
        }
      }
    }
  }

  /*
   * Handles header related classes
   *
   */
  _uiHandleHeader() {
    let self = this;

    // If the header is fixed and has the glass style, add the related class on scrolling to add a background color to the header
    if (self._lPage.classList.contains('page-header-glass') && self._lPage.classList.contains('page-header-fixed')) {
      window.addEventListener('scroll', e => {
        if (window.scrollY > 60) {
          self._lPage.classList.add('page-header-scroll');
        } else {
          self._lPage.classList.remove('page-header-scroll');
        }
      });

      window.dispatchEvent(new CustomEvent('scroll'));
    }
  }

  /*
   * Toggle Submenu functionality
   *
   */
  _uiHandleNav() {
    let links = document.querySelectorAll('[data-toggle="submenu"]');

    // When a submenu link is clicked
    if (links) {
      links.forEach(link => {
        link.addEventListener('click', e => {
          e.preventDefault();

          // Get main navigation
          let mainNav = link.closest('.nav-main');

          // Check if we are in horizontal navigation, large screen and hover is enabled
          if (
            !(
              (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) > 991
              && mainNav.classList.contains('nav-main-horizontal')
              && mainNav.classList.contains('nav-main-hover')
            )
          ) {
            // Get link's parent
            let parentLi = link.closest('li');

            if (parentLi.classList.contains('open')) {
              // If submenu is open, close it..
              parentLi.classList.remove('open');

              link.setAttribute('aria-expanded', 'false');
            } else {
              // .. else if submenu is closed, close all other (same level) submenus first before open it
              Array.from(link.closest('ul').children).forEach(el => {
                el.classList.remove('open');
              });

              parentLi.classList.add('open');

              link.setAttribute('aria-expanded', 'true');
            }
          }

          return false;
        });
      });
    }
  }

  /*
   * Page loading screen functionality
   *
   */
  _uiHandlePageLoader(mode = 'hide', colorClass) {
    if (mode === 'show') {
      if (this._lpageLoader) {
        if (colorClass) {
          this._lpageLoader.className = '';
          this._lpageLoader.classList.add(colorClass);
        }

        this._lpageLoader.classList.add('show');
      } else {
        let pageLoader = document.createElement('div');

        pageLoader.id = 'page-loader';

        if (colorClass) {
          pageLoader.classList.add(colorClass);
        }

        pageLoader.classList.add('show');

        this._lPage.insertBefore(pageLoader, this._lPage.firstChild);

        this._lpageLoader = document.getElementById('page-loader');
      }
    } else if (mode === 'hide') {
      if (this._lpageLoader) {
        this._lpageLoader.classList.remove('show');
      }
    }
  }

  /*
   * Saves/Retrieves Dark Mode preference to local storage
   *
   */
  _uiHandleDarkMode(mode = 'init') {
    let self = this;

    // If dark mode is not enabled by default remember default sidebar
    // and header style to return to after any possible dark mode disabling
    if (mode === 'init' && !self._lPage.classList.contains('dark-mode')) {
      if (self._lPage.classList.contains('sidebar-dark')) {
        localStorage.setItem('dashmixDefaultsSidebarDark', true);
      } else {
        localStorage.removeItem('dashmixDefaultsSidebarDark');
      }

      if (self._lPage.classList.contains('page-header-dark')) {
        localStorage.setItem('dashmixDefaultsPageHeaderDark', true);
      } else {
        localStorage.removeItem('dashmixDefaultsPageHeaderDark');
      }
    }

    // If remember-theme class is added in #page-container
    if (self._lPage.classList.contains('remember-theme')) {
      let darkMode = localStorage.getItem('dashmixDarkMode') || false;

      if (mode === 'init') {
        if (darkMode) {
          self._lPage.classList.add('sidebar-dark');
          self._lPage.classList.add('page-header-dark');
          self._lPage.classList.add('dark-mode');
        } else if (mode === 'init') {
          self._lPage.classList.remove('dark-mode');
        }
      } else if (mode === 'on') {
        localStorage.setItem('dashmixDarkMode', true);
      } else if (mode === 'off') {
        localStorage.removeItem('dashmixDarkMode');
      }
    } else if (mode === 'init') {
      localStorage.removeItem('dashmixDarkMode');
    }
  }

  /*
   * Set active color theme functionality
   *
   */
  _uiHandleTheme() {
    let self = this;
    let themeEl = document.getElementById('css-theme');
    let rememberTheme = this._lPage.classList.contains('remember-theme') ? true : false;

    // If remember theme is enabled
    if (rememberTheme) {
      let themeName = localStorage.getItem('dashmixThemeName') || false;

      // Update color theme
      if (themeName) {
        self._uiUpdateTheme(themeEl, themeName);
      }

      // Update theme element
      themeEl = document.getElementById('css-theme');
    } else {
      localStorage.removeItem('dashmixThemeName');
    }

    // Set the active color theme link as active
    document.querySelectorAll('[data-toggle="theme"][data-theme="' + (themeEl ? themeEl.getAttribute('href') : 'default') + '"]').forEach(link => {
      link.classList.add('active');
    });

    // When a color theme link is clicked
    document.querySelectorAll('[data-toggle="theme"]').forEach(el => {
      el.addEventListener('click', e => {
        e.preventDefault();

        // Get element's data
        let themeName = el.dataset.theme;

        // Set this color theme link as active
        document.querySelectorAll('[data-toggle="theme"]').forEach(link => {
          link.classList.remove('active');
        });

        document.querySelector('[data-toggle="theme"][data-theme="' + themeName + '"]').classList.add('active');

        // Update color theme
        self._uiUpdateTheme(themeEl, themeName);

        // Update theme element
        themeEl = document.getElementById('css-theme');

        // If remember theme is enabled, save the new active color theme
        if (rememberTheme) {
          localStorage.setItem('dashmixThemeName', themeName);
        }
      });
    });
  }

  /*
   * Updates the color theme
   *
   */
  _uiUpdateTheme(themeEl, themeName) {
    if (themeName === 'default') {
      if (themeEl) {
        themeEl.parentNode.removeChild(themeEl);
      }
    } else {
      if (themeEl) {
        themeEl.setAttribute('href', themeName);
      } else {
        let themeLink = document.createElement('link');

        themeLink.id = 'css-theme';
        themeLink.setAttribute('rel', 'stylesheet');
        themeLink.setAttribute('href', themeName);

        document.getElementById('css-main').insertAdjacentElement('afterend', themeLink);
      }
    }
  }

  /*
   * Layout API
   *
   */
  _uiApiLayout(mode = 'init') {
    let self = this;

    // API with object literals
    let layoutAPI = {
      init: () => {
        let buttons = document.querySelectorAll('[data-toggle="layout"]');

        // Call layout API on button click
        if (buttons) {
          buttons.forEach(btn => {
            btn.addEventListener('click', e => {
              self._uiApiLayout(btn.dataset.action);
            });
          })
        }

        // Prepend Page Overlay div if enabled (used when Side Overlay opens)
        if (self._lPage.classList.contains('enable-page-overlay')) {
          let pageOverlay = document.createElement('div');
          pageOverlay.id = 'page-overlay';

          self._lPage.insertBefore(pageOverlay, self._lPage.firstChild);

          document.getElementById('page-overlay').addEventListener('click', e => {
            self._uiApiLayout('side_overlay_close');
          });
        }
      },
      sidebar_pos_toggle: () => {
        self._lPage.classList.toggle('sidebar-r');
      },
      sidebar_pos_left: () => {
        self._lPage.classList.remove('sidebar-r');
      },
      sidebar_pos_right: () => {
        self._lPage.classList.add('sidebar-r');
      },
      sidebar_toggle: () => {
        if (window.innerWidth > 991) {
          self._lPage.classList.toggle('sidebar-o');
        } else {
          self._lPage.classList.toggle('sidebar-o-xs');
        }
      },
      sidebar_open: () => {
        if (window.innerWidth > 991) {
          self._lPage.classList.add('sidebar-o');
        } else {
          self._lPage.classList.add('sidebar-o-xs');
        }
      },
      sidebar_close: () => {
        if (window.innerWidth > 991) {
          self._lPage.classList.remove('sidebar-o');
        } else {
          self._lPage.classList.remove('sidebar-o-xs');
        }
      },
      sidebar_mini_toggle: () => {
        if (window.innerWidth > 991) {
          self._lPage.classList.toggle('sidebar-mini');
        }
      },
      sidebar_mini_on: () => {
        if (window.innerWidth > 991) {
          self._lPage.classList.add('sidebar-mini');
        }
      },
      sidebar_mini_off: () => {
        if (window.innerWidth > 991) {
          self._lPage.classList.remove('sidebar-mini');
        }
      },
      sidebar_style_toggle: () => {
        if (self._lPage.classList.contains('sidebar-dark')) {
          self._uiApiLayout('sidebar_style_light');
        } else {
          self._uiApiLayout('sidebar_style_dark');
        }
      },
      sidebar_style_dark: () => {
        self._lPage.classList.add('sidebar-dark');
        localStorage.setItem('dashmixDefaultsSidebarDark', true);
      },
      sidebar_style_light: () => {
        self._lPage.classList.remove('sidebar-dark');
        self._lPage.classList.remove('dark-mode');
        localStorage.removeItem('dashmixDefaultsSidebarDark');
      },
      side_overlay_toggle: () => {
        if (self._lPage.classList.contains('side-overlay-o')) {
          self._uiApiLayout('side_overlay_close');
        } else {
          self._uiApiLayout('side_overlay_open');
        }
      },
      side_overlay_open: () => {
        // When ESCAPE key is hit close the side overlay
        document.addEventListener('keydown', e => {
          if (e.key === 'Esc' || e.key === 'Escape') {
            self._uiApiLayout('side_overlay_close');
          }
        });

        self._lPage.classList.add('side-overlay-o');
      },
      side_overlay_close: () => {
        self._lPage.classList.remove('side-overlay-o');
      },
      side_overlay_mode_hover_toggle: () => {
        self._lPage.classList.toggle('side-overlay-hover');
      },
      side_overlay_mode_hover_on: () => {
        self._lPage.classList.add('side-overlay-hover');
      },
      side_overlay_mode_hover_off: () => {
        self._lPage.classList.remove('side-overlay-hover');
      },
      header_glass_toggle: () => {
        self._lPage.classList.toggle('page-header-glass');
        self._uiHandleHeader();
      },
      header_glass_on: () => {
        self._lPage.classList.add('page-header-glass');
        self._uiHandleHeader();
      },
      header_glass_off: () => {
        self._lPage.classList.remove('page-header-glass');
        self._uiHandleHeader();
      },
      header_mode_toggle: () => {
        self._lPage.classList.toggle('page-header-fixed');
      },
      header_mode_static: () => {
        self._lPage.classList.remove('page-header-fixed');
      },
      header_mode_fixed: () => {
        self._lPage.classList.add('page-header-fixed');
      },
      header_style_toggle: () => {
        if (self._lPage.classList.contains('page-header-dark')) {
          self._uiApiLayout('header_style_light');
        } else {
          self._uiApiLayout('header_style_dark');
        }
      },
      header_style_dark: () => {
        self._lPage.classList.add('page-header-dark');
        localStorage.setItem('dashmixDefaultsPageHeaderDark', true);
      },
      header_style_light: () => {
        self._lPage.classList.remove('page-header-dark');
        self._lPage.classList.remove('dark-mode');
        localStorage.removeItem('dashmixDefaultsPageHeaderDark');
      },
      header_search_on: () => {
        self._lHeaderSearch.classList.add('show');
        self._lHeaderSearchInput.focus();

        // When ESCAPE key is hit close the search section
        document.addEventListener('keydown', e => {
          if (e.key === 'Esc' || e.key === 'Escape') {
            self._uiApiLayout('header_search_off');
          }
        });
      },
      header_search_off: () => {
        self._lHeaderSearch.classList.remove('show');
        self._lHeaderSearchInput.blur();
      },
      header_loader_on: () => {
        self._lHeaderLoader.classList.add('show');
      },
      header_loader_off: () => {
        self._lHeaderLoader.classList.remove('show');
      },
      dark_mode_toggle: () => {
        if (self._lPage.classList.contains('dark-mode')) {
          self._uiApiLayout('dark_mode_off');
        } else {
          self._uiApiLayout('dark_mode_on');
        }
      },
      dark_mode_on: () => {
        self._lPage.classList.add('sidebar-dark');
        self._lPage.classList.add('page-header-dark');
        self._lPage.classList.add('dark-mode');
        this._uiHandleDarkMode('on');
      },
      dark_mode_off: () => {
        if (!localStorage.getItem('dashmixDefaultsSidebarDark')) {
          self._lPage.classList.remove('sidebar-dark');
        }

        if (!localStorage.getItem('dashmixDefaultsPageHeaderDark')) {
          self._lPage.classList.remove('page-header-dark');
        }

        self._lPage.classList.remove('dark-mode');
        this._uiHandleDarkMode('off');
      },
      content_layout_toggle: () => {
        if (self._lPage.classList.contains('main-content-boxed')) {
          self._uiApiLayout('content_layout_narrow');
        } else if (self._lPage.classList.contains('main-content-narrow')) {
          self._uiApiLayout('content_layout_full_width');
        } else {
          self._uiApiLayout('content_layout_boxed');
        }
      },
      content_layout_boxed: () => {
        self._lPage.classList.remove('main-content-narrow');
        self._lPage.classList.add('main-content-boxed');
      },
      content_layout_narrow: () => {
        self._lPage.classList.remove('main-content-boxed');
        self._lPage.classList.add('main-content-narrow');
      },
      content_layout_full_width: () => {
        self._lPage.classList.remove('main-content-boxed');
        self._lPage.classList.remove('main-content-narrow');
      }
    };

    // Call layout API
    if (layoutAPI[mode]) {
      layoutAPI[mode]();
    }
  }

  /*
   * Blocks API
   *
   */
  _uiApiBlocks(mode = 'init', block = false) {
    let self = this;

    // Helper variables
    let elBlock, btnFullscreen, btnContentToggle;

    // Set default icons for fullscreen and content toggle buttons
    let iconBase = 'si';
    let iconFullscreen = 'si-size-fullscreen';
    let iconFullscreenActive = 'si-size-actual';
    let iconContent = 'si-arrow-up';
    let iconContentActive = 'si-arrow-down';

    // API with object literals
    let blockAPI = {
      init: () => {
        // Auto add the default toggle icons to fullscreen and content toggle buttons
        document.querySelectorAll('[data-toggle="block-option"][data-action="fullscreen_toggle"]').forEach(btn => {
          btn.innerHTML = '<i class="' + iconBase + ' ' + (btn.closest('.block').classList.contains('block-mode-fullscreen') ? iconFullscreenActive : iconFullscreen) + '"></i>';
        });

        document.querySelectorAll('[data-toggle="block-option"][data-action="content_toggle"]').forEach(btn => {
          btn.innerHTML = '<i class="' + iconBase + ' ' + (btn.closest('.block').classList.contains('block-mode-hidden') ? iconContentActive : iconContent) + '"></i>';
        });

        // Call blocks API on option button click
        document.querySelectorAll('[data-toggle="block-option"]').forEach(btn => {
          btn.addEventListener('click', e => {
            this._uiApiBlocks(btn.dataset.action, btn.closest('.block'));
          });
        });
      },
      fullscreen_toggle: () => {
        elBlock.classList.remove('block-mode-pinned');
        elBlock.classList.toggle('block-mode-fullscreen');

        // Update block option icon
        if (btnFullscreen) {
          if (elBlock.classList.contains('block-mode-fullscreen')) {
            btnFullscreen && btnFullscreen.querySelector('i').classList.replace(iconFullscreen, iconFullscreenActive);
          } else {
            btnFullscreen && btnFullscreen.querySelector('i').classList.replace(iconFullscreenActive, iconFullscreen);
          }
        }
      },
      fullscreen_on: () => {
        elBlock.classList.remove('block-mode-pinned')
        elBlock.classList.add('block-mode-fullscreen');

        // Update block option icon
        btnFullscreen && btnFullscreen.querySelector('i').classList.replace(iconFullscreen, iconFullscreenActive);
      },
      fullscreen_off: () => {
        elBlock.classList.remove('block-mode-fullscreen');

        // Update block option icon
        btnFullscreen && btnFullscreen.querySelector('i').classList.replace(iconFullscreenActive, iconFullscreen);
      },
      content_toggle: () => {
        elBlock.classList.toggle('block-mode-hidden');

        // Update block option icon
        if (btnContentToggle) {
          if (elBlock.classList.contains('block-mode-hidden')) {
            btnContentToggle.querySelector('i').classList.replace(iconContent, iconContentActive);
          } else {
            btnContentToggle.querySelector('i').classList.replace(iconContentActive, iconContent);
          }
        }
      },
      content_hide: () => {
        elBlock.classList.add('block-mode-hidden');

        // Update block option icon
        btnContentToggle && btnContentToggle.querySelector('i').classList.replace(iconContent, iconContentActive);
      },
      content_show: () => {
        elBlock.classList.remove('block-mode-hidden');

        // Update block option icon
        btnContentToggle && btnContentToggle.querySelector('i').classList.replace(iconContentActive, iconContent);
      },
      state_toggle: () => {
        elBlock.classList.toggle('block-mode-loading');

        // Return block to normal state if the demostration mode is on in the refresh option button - data-action-mode="demo"
        if (elBlock.querySelector('[data-toggle="block-option"][data-action="state_toggle"][data-action-mode="demo"]')) {
          setTimeout(() => {
            elBlock.classList.remove('block-mode-loading');
          }, 2000);
        }
      },
      state_loading: () => {
        elBlock.classList.add('block-mode-loading');
      },
      state_normal: () => {
        elBlock.classList.remove('block-mode-loading');
      },
      pinned_toggle: () => {
        elBlock.classList.remove('block-mode-fullscreen');
        elBlock.classList.toggle('block-mode-pinned');
      },
      pinned_on: () => {
        elBlock.classList.remove('block-mode-fullscreen');
        elBlock.classList.add('block-mode-pinned');
      },
      pinned_off: () => {
        elBlock.classList.remove('block-mode-pinned');
      },
      close: () => {
        elBlock.classList.add('d-none');
      },
      open: () => {
        elBlock.classList.remove('d-none');
      }
    };

    if (mode === 'init') {
      // Call Block API
      blockAPI[mode]();
    } else {
      // Get block element
      elBlock = (block instanceof Element) ? block : document.querySelector(`${block}`);

      // If element exists, procceed with block functionality
      if (elBlock) {
        // Get block option buttons if exist (need them to update their icons)
        btnFullscreen = elBlock.querySelector('[data-toggle="block-option"][data-action="fullscreen_toggle"]');
        btnContentToggle = elBlock.querySelector('[data-toggle="block-option"][data-action="content_toggle"]');

        // Call Block API
        if (blockAPI[mode]) {
          blockAPI[mode]();
        }
      }
    }
  }

  /*
   ********************************************************************************************
   *
   * Helpers
   *
   *********************************************************************************************
   */

  /*
   * On DOM content loaded
   *
   */
  onLoad(fn) {
    if (document.readyState != 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  /*
   ********************************************************************************************
   *
   * Create aliases for easier/quicker access to vital methods
   *
   *********************************************************************************************
   */

  /*
   * Init base functionality
   *
   */
  init() {
    this._uiInit();
  }

  /*
   * Layout API
   *
   */
  layout(mode) {
    this._uiApiLayout(mode);
  }

  /*
   * Blocks API
   *
   */
  block(mode, block) {
    this._uiApiBlocks(mode, block);
  }

  /*
   * Handle Page Loader
   *
   */
  loader(mode, colorClass) {
    this._uiHandlePageLoader(mode, colorClass);
  }

  /*
   * Run Helpers
   *
   */
  helpers(helpers, options = {}) {
    Helpers.run(helpers, options);
  }

  /*
   * Run Helpers on DOM content loaded
   *
   */
  helpersOnLoad(helpers, options = {}) {
    this.onLoad(() => Helpers.run(helpers, options));
  }
}
