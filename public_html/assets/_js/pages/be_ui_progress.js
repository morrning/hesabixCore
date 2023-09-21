/*
 *  Document   : be_ui_progress.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Progress Page
 */

class pageProgress {
  /*
   * Bars randomize functionality
   *
   */
  static barsRandomize() {
    document.querySelectorAll('.js-bar-randomize').forEach(item => {
      item.addEventListener('click', e => {
        item.closest('.block').querySelectorAll('.progress-bar').forEach(bar => {
          let random = Math.floor((Math.random() * 91) + 10);
          let content = bar.querySelector('span');

          // Update progress width
          bar.style.width = random + '%';

          // Update progress label
          if (content) {
            content.textContent = random + '%';
          }
        });
      });
    });
  }

  /*
   * Init functionality
   *
   */
  static init() {
    this.barsRandomize();
  }
}

// Initialize when page loads
Dashmix.onLoad(() => pageProgress.init());
