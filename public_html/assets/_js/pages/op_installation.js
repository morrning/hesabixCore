/*
 *  Document   : op_installation.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Installation Page
 */

// Form Validation, for more examples you can check out https://github.com/jzaefferer/jquery-validation
class pageInstallation {
  /*
   * Init Installation Form Validation
   *
   */
  static initValidationInstallation() {
    // Load default options for jQuery Validation plugin
    Dashmix.helpers('jq-validation');

    // Init Form Validation
    jQuery('.js-validation-installation').validate({
      rules: {
        'install-db-name': {
          required: true
        },
        'install-db-username': {
          required: true
        },
        'install-db-password': {
          required: true
        },
        'install-admin-email': {
          required: true,
          emailWithDot: true
        },
        'install-admin-password': {
          required: true,
          minlength: 5
        },
        'install-admin-password-confirm': {
          required: true,
          equalTo: '#install-admin-password'
        }
      },
      messages: {
        'install-db-name': {
          required: 'لطفا نام پایگاه داده خود را وارد کنید'
        },
        'install-db-username': {
          required: 'لطفا نام کاربری را با دسترسی به پایگاه داده خود وارد کنید'
        },
        'install-db-password': {
          required: 'لطفا رمز عبور کاربر پایگاه داده خود را وارد کنید'
        },
        'install-admin-email': {
          required: 'لطفا ایمیل خود را وارد کن'
        },
        'install-admin-password': {
          required: 'لطفا رمز عبور بدهید',
          minlength: 'رمز عبور شما باید حداقل 5 کاراکتر باشد'
        },
        'install-admin-password-confirm': {
          required: 'لطفا رمز عبور بدهید',
          minlength: 'رمز عبور شما باید حداقل 5 کاراکتر باشد',
          equalTo: 'لطفا رمز عبور مشابه بالا را وارد کنید'
        }
      }
    });
  }

  /*
   * Init functionality
   *
   */
  static init() {
    this.initValidationInstallation();
  }
}

// Initialize when page loads
Dashmix.onLoad(() => pageInstallation.init());
