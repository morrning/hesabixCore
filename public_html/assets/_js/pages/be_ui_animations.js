/*
 *  Document   : be_ui_animations.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Animations Page
 */

class pageAnimations {
  /*
   * Animation toggle functionality
   *
   */
  static animationsToggle() {
    let animationClass, animationButton, currentSection;

    // On button click
    document.querySelectorAll('.js-animation-section button').forEach(btn => {
      btn.addEventListener('click', e => {
        animationClass = btn.dataset.animationClass;
        currentSection = btn.closest('.js-animation-section');
  
        // Update class preview
        currentSection.querySelector('.js-animation-preview').textContent = animationClass;
  
        // Update animation object classes
        let animationObject = currentSection.querySelector('.js-animation-object')
          
        animationObject.classList = '';
        animationObject.classList.add('js-animation-object');
        animationObject.classList.add('animated');
        animationObject.classList.add(animationClass);
      });
    });
  }

  /*
   * Init functionality
   *
   */
  static init() {
    this.animationsToggle();
  }
}

// Initialize when page loads
Dashmix.onLoad(() => pageAnimations.init());
