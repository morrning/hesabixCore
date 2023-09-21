/*
 *  Document   : be_forms_validation.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Forms Validation Page
 */

// Form Validation, for more examples you can check out https://github.com/jzaefferer/jquery-validation
class pageFormsValidation {
  /*
   * Init Validation functionality
   *
   */
  static initValidation() {
    // Load default options for jQuery Validation plugin
    Dashmix.helpers('jq-validation');

    // Init Form Validation
    jQuery('.js-validation').validate({
      ignore: [],
      rules: {
        'val-username': {
          required: true,
          minlength: 3
        },
        'val-email': {
          required: true,
          emailWithDot: true
        },
        'val-password': {
          required: true,
          minlength: 5
        },
        'val-confirm-password': {
          required: true,
          equalTo: '#val-password'
        },
        'val-suggestions': {
          required: true,
          minlength: 5
        },
        'val-skill': {
          required: true
        },
        'val-currency': {
          required: true,
          currency: ['$', true]
        },
        'val-website': {
          required: true,
          url: true
        },
        'val-phoneus': {
          required: true,
          phoneUS: true
        },
        'val-digits': {
          required: true,
          digits: true
        },
        'val-number': {
          required: true,
          number: true
        },
        'val-range': {
          required: true,
          range: [1, 5]
        },
        'val-terms': {
          required: true
        },
        'val-select2': {
          required: true
        },
        'val-select2-multiple': {
          required: true,
          minlength: 2
        }
      },
      messages: {
        'val-username': {
          required: 'لطفا یک نام کاربری وارد کنید',
          minlength: 'نام کاربری شما باید حداقل از 3 کاراکتر تشکیل شده باشد'
        },
        'val-email': 'لطفا یک آدرس ایمیل معتبر وارد کنید',
        'val-password': {
          required: 'لطفا رمز عبور بدهید',
          minlength: 'رمز عبور شما باید حداقل 5 کاراکتر باشد'
        },
        'val-confirm-password': {
          required: 'لطفا رمز عبور بدهید',
          minlength: 'رمز عبور شما باید حداقل 5 کاراکتر باشد',
          equalTo: 'لطفا رمز عبور مشابه بالا را وارد کنید'
        },
        'val-select2': 'لطفا یک مقدار را انتخاب کنید!',
        'val-select2-multiple': 'لطفا حداقل 2 مقدار را انتخاب کنید!',
        'val-suggestions': 'برای بهتر شدن چه کنیم؟',
        'val-skill': 'لطفا یک مهارت را انتخاب کنید!',
        'val-currency': 'لطفا قیمت را وارد کنید',
        'val-website': 'لطفا وب سایت خود را وارد کنید!',
        'val-phoneus': 'لطفا یک تلفن ایالات متحده وارد کنید!',
        'val-digits': 'لطفا فقط اعداد را وارد کنید!',
        'val-number': 'لطفا یک عدد وارد کنید!',
        'val-range': 'لطفا عددی بین 1 تا 5 وارد کنید!',
        'val-terms': 'شما باید با شرایط خدمات موافقت کنید!'
      }
    });

    // Init Validation on Select2 change
    jQuery('.js-select2').on('change', e => {
      jQuery(e.currentTarget).valid();
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
Dashmix.onLoad(() => pageFormsValidation.init());
