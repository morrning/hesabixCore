/*
 *  Document   : op_auth_signup.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Sign Up Page
 */

class pageAuthSignUp {
  /*
   * Init Sign Up Form Validation, for more examples you can check out https://github.com/jzaefferer/jquery-validation
   *
   */
  static initValidation() {
    // Load default options for jQuery Validation plugin
    Dashmix.helpers('jq-validation');

    // Init Form Validation
    jQuery('.js-validation-signup').validate({
      rules: {
        'signup-username': {
          required: true,
          minlength: 3
        },
        'signup-email': {
          required: true,
          emailWithDot: true
        },
        'signup-password': {
          required: true,
          minlength: 5
        },
        'signup-password-confirm': {
          required: true,
          equalTo: '#signup-password'
        },
        'signup-terms': {
          required: true
        }
      },
      messages: {
        'signup-username': {
          required: 'لطفا یک نام کاربری وارد کنید',
          minlength: 'نام کاربری شما باید حداقل از 3 کاراکتر تشکیل شده باشد'
        },
        'signup-email': 'لطفا یک آدرس ایمیل معتبر وارد کنید',
        'signup-password': {
          required: 'لطفا رمز عبور بدهید',
          minlength: 'رمز عبور شما باید حداقل 5 کاراکتر باشد'
        },
        'signup-password-confirm': {
          required: 'لطفا رمز عبور بدهید',
          minlength: 'رمز عبور شما باید حداقل 5 کاراکتر باشد',
          equalTo: 'لطفا رمز عبور مشابه بالا را وارد کنید'
        },
        'signup-terms': 'شما باید با شرایط خدمات موافقت کنید!'
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
Dashmix.onLoad(() => pageAuthSignUp.init());
