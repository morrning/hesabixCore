/*
 *  Document   : op_auth_signin.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Sign In Page
 */

class pageAuthSignIn {
  /*
   * Init Sign In Form Validation, for more examples you can check out https://github.com/jzaefferer/jquery-validation
   *
   */
  static initValidation() {
    // Load default options for jQuery Validation plugin
    Dashmix.helpers('jq-validation');

    // Init Form Validation
    jQuery('.js-validation-signin').validate({
      rules: {
        'login-username': {
          required: true,
          minlength: 3
        },
        'login-password': {
          required: true,
          minlength: 5
        }
      },
      messages: {
        'login-username': {
          required: 'لطفا یک نام کاربری وارد کنید',
          minlength: 'نام کاربری شما باید حداقل از 3 کاراکتر تشکیل شده باشد'
        },
        'login-password': {
          required: 'لطفا رمز عبور بدهید',
          minlength: 'رمز عبور شما باید حداقل 5 کاراکتر باشد'
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
Dashmix.onLoad(() => pageAuthSignIn.init());