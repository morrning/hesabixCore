<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-list"></i>
        صف چاپ
      </h3>
      <div class="block-options">
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#PrintNewModal">
          <i class="fa fa-plus"></i>
        </button>

        <!-- Modal -->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="PrintNewModal" tabindex="-1" aria-labelledby="printerModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5 text-primary" id="printerModalLabel">افزودن چاپگر جدید</h1>
                <div class="block-options">
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
              </div>
              <div class="modal-body">
                <p>
                  با ایجاد توکن یک رابط برای چاپ اسناد ایجاد می‌شود. بعد از ایجاد توکن آن را در داخل نرم افزار
                  Hesabix Printer
                  نصب شده به رایانه مورد نظر وارد کنید.
                </p>
                <div class="form-floating mb-4">
                  <input :disabled="loading" v-model="name" class="form-control" type="text">
                  <label class="form-label"><span class="text-danger">(لازم)</span> نام چاپگر</label>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بازگشت</button>
                <button :disabled="loading" @click="save()" type="button" class="btn btn-primary">ایجاد کلید دسترسی</button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
    <div class="block-content pt-1 pb-3">
      <div class="row">
        <div class="col-sm-12 col-md-12 m-0 p-0">
          <EasyDataTable table-class-name="customize-table" show-index alternating :search-value="searchValue"
            :headers="headers" :items="items" theme-color="#1d90ff" header-text-direction="center"
            body-text-direction="center" rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
            rowsOfPageSeparatorMessage="از" :loading="loading">
            <template #item-operation="{ id }">
              <button aria-expanded="false" aria-haspopup="true" class="btn btn-sm btn-link" data-bs-toggle="dropdown"
                id="dropdown-align-center-alt-primary" type="button">
                <i class="fa-solid fa-ellipsis"></i>
              </button>
              <div aria-labelledby="dropdown-align-center-outline-primary" class="dropdown-menu dropdown-menu-end"
                style="">
                <button type="button" @click="deleteItem(id)" class="dropdown-item text-danger">
                  <i class="fa fa-trash pe-2"></i>
                  حذف
                </button>
              </div>
            </template>
          </EasyDataTable>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";
export default {
  name: "list",
  data: () => {
    return {
      loading: ref(true),
      name: '',
      items: [],
      headers: [
        { text: "عملیات", value: "operation" },
        { text: "نام چاپگر", value: "name", sortable: true },
        { text: "توکن", value: "token", sortable: true },
        { text: "وضعیت", value: "status", sortable: true },
      ]
    }
  },
  methods: {
    loadData() {
      axios.post('/api/printers/list/queue',{
        id:this.$route.params.id
      })
        .then((response) => {
          this.items = response.data;
          this.loading = false;
        });
    },
    deleteItem(id) {
      Swal.fire({
        text: 'آیا برای حذف این چاپگر مطمئن هستید؟ دسترسی چاپ تا زمان نصب توکن جدید متوقف و اسناد در صف چاپ نیز حذف خواهند شد.!',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
        icon: 'warning'
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          axios.post('/api/printers/queue/' + id).then((response) => {
            if (response.data.result == 1) {
              let index = 0;
              for (let z = 0; z < this.items.length; z++) {
                index++;
                if (this.items[z]['id'] == id) {
                  this.items.splice(index - 1, 1);
                }
              }
              Swal.fire({
                text: 'چاپگر با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            }
            else if (response.data.result == 2) {
              Swal.fire({
                text: response.data.message,
                icon: 'warning',
                confirmButtonText: 'قبول'
              });
            }
          })
        }
      })
    },
    save() {
      if (this.name.length === 0)
        Swal.fire({
          text: 'نام چاپگر الزامی است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      else {
        this.loading = true;
        axios.post('/api/printers/insert',{name:this.name}).then((response) => {
          this.loading = false;
          if (response.data.result == 2) {
            Swal.fire({
              text: 'قبلا ثبت شده است.',
              icon: 'error',
              confirmButtonText: 'قبول'
            });
          } else {
            this.closeModal();
            this.loadData();
            Swal.fire({
              text: 'چاپگر افزوده شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            }).then(() => {
              this.name='';
            });
          }
        })
      }

    },
    closeModal() {
            var genericModalE2 = document.getElementById('PrintNewModal');
            var modal2 = bootstrap.Modal.getInstance(genericModalE2);
            modal2.hide();
        },
  },
  beforeMount() {
    this.loadData();
  },
}
</script>

<style scoped></style>