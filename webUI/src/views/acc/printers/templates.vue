<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-print"></i>
        قالب‌های چاپ
      </h3>
      <div class="block-options">
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-sm btn-success">
          <i class="fa fa-save"></i>
          ذخیره تغییرات
        </button>
      </div>
    </div>
    <div class="block-content pt-1 pb-3 mx-0 px-0">
      <div class="row mx-0 px-0">
        <div class="col-sm-12 col-lg-12">
          <!-- Vertical Block Tabs Default Style -->
          <div class="block block-rounded row g-0">
            <ul class="nav nav-tabs nav-tabs-block flex-md-column col-md-3" role="tablist">
              <li class="nav-item d-md-flex flex-md-column" role="presentation">
                <button aria-controls="btabs-vertical-home" aria-selected="false" class="nav-link text-md-start"
                  data-bs-target="#btabs-vertical-home" data-bs-toggle="tab" id="btabs-vertical-home-tab" role="tab"
                  tabindex="-1">
                  <i class="fa fa-fw fa-ticket opacity-50 me-1 d-none d-sm-inline-block"></i>
                  صورت حساب فاکتور سریع
                </button>
              </li>
             
            </ul>
            <div class="tab-content col-md-9">
              <div aria-labelledby="btabs-vertical-home-tab" class="block-content tab-pane" id="btabs-vertical-home"
                role="tabpanel" tabindex="0">
                <h4 class="fw-semibold">کد قالب</h4>
                <textarea class="form-control" rows="10" style="direction: ltr;">dddd</textarea>
              </div>
            </div>
          </div>
          <!-- END Vertical Block Tabs Default Style -->
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
  name: "templates",
  data: () => {
    return {
      loading: ref(true),
    }
  },
  methods: {
    loadData() {
      axios.post('/api/printers/list')
        .then((response) => {
          this.items = response.data;
          this.loading = false;
        });
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
        axios.post('/api/printers/insert', { name: this.name }).then((response) => {
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
              this.name = '';
            });
          }
        })
      }

    }
  },
  beforeMount() {
    this.loadData();
  },
}
</script>

<style scoped></style>