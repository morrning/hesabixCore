/*
 *  Document   : be_comp_dialogs.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Dialogs Page
 */

// SweetAlert2, for more examples you can check out https://github.com/sweetalert2/sweetalert2
class pageDialogs {
  /*
   * SweetAlert2 demo functionality
   *
   */
  static sweetAlert2() {
    // Set default properties
    let toast = Swal.mixin({
      buttonsStyling: false,
      target: '#page-container',
      customClass: {
        confirmButton: 'btn btn-success m-1',
        cancelButton: 'btn btn-danger m-1',
        input: 'form-control'
      }
    });

    // Init a simple dialog on button click
    document.querySelector('.js-swal-simple').addEventListener('click', e => {
      toast.fire('سلام، این فقط یک پیام ساده است!');
    });

    // Init an success dialog on button click
    document.querySelector('.js-swal-success').addEventListener('click', e => {
      toast.fire('موفقیت آمیز', 'همه چیز کاملا به روز شد!', 'success');
    });

    // Init an info dialog on button click
    document.querySelector('.js-swal-info').addEventListener('click', e => {
      toast.fire('اطلاع رسانی', 'فقط یک پیام اطلاع رسانی!', 'info');
    });

    // Init an warning dialog on button click
    document.querySelector('.js-swal-warning').addEventListener('click', e => {
      toast.fire('هشدار', 'چیزی به توجه شما نیاز دارد!', 'warning');
    });

    // Init an error dialog on button click
    document.querySelector('.js-swal-error').addEventListener('click', e => {
      toast.fire('اوه نه', 'مشکلی پیش آمد!', 'error');
    });

    // Init an question dialog on button click
    document.querySelector('.js-swal-question').addEventListener('click', e => {
      toast.fire('سوال', 'آیا مطمئن هستید؟', 'question');
    });

    // Init an example confirm dialog on button click
    document.querySelector('.js-swal-confirm').addEventListener('click', e => {
      toast.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'شما نمی توانید این فایل خیالی را بازیابی کنید!',
        icon: 'warning',
        showCancelButton: true,
        customClass: {
          confirmButton: 'btn btn-danger m-1',
          cancelButton: 'btn btn-secondary m-1'
        },
        confirmButtonText: 'بله حذفش کن!',
        html: false,
        preConfirm: e => {
          return new Promise(resolve => {
            setTimeout(() => {
              resolve();
            }, 50);
          });
        }
      }).then(result => {
        if (result.value) {
          toast.fire('حذف شد!', 'فایل خیالی شما حذف شده است.', 'success');
          // result.dismiss can be 'overlay', 'cancel', 'close', 'esc', 'timer'
        } else if (result.dismiss === 'cancel') {
          toast.fire('لغو شد', 'فایل خیالی شما امن است :)', 'error');
        }
      });
    });

    // Init an example confirm alert on button click
    document.querySelector('.js-swal-custom-position').addEventListener('click', e => {
      toast.fire({
        position: 'top-end',
        title: 'عالی!',
        text: 'موقعیت خوب!',
        icon: 'success'
      });
    });
  }

  /*
   * Init functionality
   *
   */
  static init() {
    this.sweetAlert2();
  }
}

// Initialize when page loads
Dashmix.onLoad(() => pageDialogs.init());
