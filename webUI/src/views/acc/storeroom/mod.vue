<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button type="button" @click="$router.back()" class="btn text-warning mx-2 px-2">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        مشخصات انبار </h3>
      <div class="block-options">
        <button @click="save()" type="button" class="btn btn-sm btn-alt-primary">
          <i class="fa fa-save me-2"></i>
          ثبت
        </button>
      </div>
    </div>
    <div class="block-content py-3 vl-parent">
      <loading color="blue" loader="dots" v-model:active="isLoading" :is-full-page="false"/>
      <div class="container">
        <div class="row py-3">
          <div class="col-sm-12 col-md-12">
            <div>
              <label class="me-4 text-primary">وضعیت انبار</label>
              <div class="form-check form-check-inline">
                <input v-model="this.data.active" class="form-check-input" type="radio" value="true">
                <label class="form-check-label" for="inlineCheckbox1">فعال</label>
              </div>
              <div class="form-check form-check-inline">
                <input v-model="this.data.active" class="form-check-input" type="radio" value="false">
                <label class="form-check-label" for="inlineCheckbox2">غیرفعال</label>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12 col-md-6">
            <div class="form-floating mb-4">
              <input v-model="data.name" class="form-control" type="text">
              <label class="form-label"><span class="text-danger">(لازم)</span> نام انبار</label>
            </div>
            <div class="form-floating mb-4">
              <input v-model="data.tel" class="form-control" type="text">
              <label class="form-label">تلفن</label>
            </div>
          </div>
          <div class="col-sm-12 col-md-6">
            <div class="form-floating mb-4">
              <input v-model="data.manager" class="form-control" type="text">
              <label class="form-label">انباردار</label>
            </div>
          </div>
          <div class="col-sm-12 col-md-12">
            <div class="form-floating mb-4">
              <input v-model="data.adr" class="form-control" type="text">
              <label class="form-label">آدرس</label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import {Money3} from "v-money3";

export default {
  name: "mod",
  components: {
    Loading,
    Money3
  },
  data: ()=>{return{
    isLoading: false,
    units:'',
    data: {
      id:0,
      name: '',
      manager: '',
      active: true,
      tel: '',
      adr: '',
    },
  }},
  mounted() {
    this.loadData(this.$route.params.id);
  },
  beforeRouteUpdate(to,from){
    this.loadData(to.params.id);
  },
  methods: {
    loadData(id = '') {
      this.isLoading = true;
      if (id != '') {
        //load user info
        this.isLoading = true;
        axios.post('/api/storeroom/info/' + id).then((response) => {
          this.data = response.data;
        });
      }
      this.isLoading = false;
    },
    save() {
      if (this.data.name.length === 0)
        Swal.fire({
          text: 'نام کالا یا خدمات الزامی است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      else {
        this.isLoading = true;
        axios.post('/api/storeroom/mod/' + this.data.id, this.data).then((response) => {
          this.isLoading = false;
          if (response.data.result == 2) {
            Swal.fire({
              text: 'قبلا ثبت شده است.',
              icon: 'error',
              confirmButtonText: 'قبول'
            });
          } else {
            Swal.fire({
              text: 'مشخصات انبار ثبت شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            }).then(() => {
              this.$router.push('/acc/storeroom/list')
            });
          }
        })
      }

    }
  }
}
</script>

<style scoped>

</style>