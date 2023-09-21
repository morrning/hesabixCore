/*
 *  Document   : be_comp_onboarding.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Onboarding Page
 */

// Onboarding Modal using Bootstrap Modal and Slick slider
class pageCompOnboarding {
  /*
   * Init example onboarding modal functionality
   *
   */
  static initOnboardingModal() {
    // Get onboarding modal
    let modalEl = document.getElementById('modal-onboarding');
    let modal = new bootstrap.Modal(modalEl);

    // Show Onboarding Modal by default
    modal.show();

    // Re-init Slick Slider every time the modal is shown
    modalEl.addEventListener('shown.bs.modal', e => {
      // Remove enabled class added by the helper to prevent re-init
      document.querySelector('#modal-onboarding .js-slider').classList.remove('js-slider-enabled');

      // Re-init Slick Slider
      Dashmix.helpers('jq-slick');
    });
  }

  /*
   * Init functionality
   *
   */
  static init() {
    this.initOnboardingModal();
  }
}

// Initialize when page loads
Dashmix.onLoad(() => pageCompOnboarding.init());
