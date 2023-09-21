/*
 *  Document   : op_auth_lock.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Lock Page
 */

class pageAuthLock {
  /*
   * Init Lock Form Validation, for more examples you can check out https://github.com/jzaefferer/jquery-validation
   *
   */
  static initValidation() {
    // Load default options for jQuery Validation plugin
    Dashmix.helpers('jq-validation');

    // Init Form Validation
    jQuery('.js-validation-lock').validate({
      rules: {
        'lock-password': {
          required: true,
          minlength: 3
        }
      },
      messages: {
        'lock-password': {
          required: 'لطفا رمز عبور معتبر خود را وارد کنید'
        }
      }
    });
  }

  /*
   * Init functionality
   *
   */
  static init() {
    this.initValidation();
  }
}

// Initialize when page loads
Dashmix.onLoad(() => pageAuthLock.init());