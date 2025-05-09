<script lang="ts">
import {defineComponent, ref} from 'vue'
import axios from "axios";
import Swal from "sweetalert2";

export default defineComponent({
  name: "change-password",
  data: ()=>{return {
    loading: ref(true),
    password:'',
    sendSMS: false,
    userInfo:{},
  }},
  methods: {
    loadData(){
        this.loading = true;
        axios.post('/api/admin/user/info/'+this.$route.params.id).then((resp)=>{
            this.userInfo = resp.data;
            this.loading = false;
        });
    },
    submit(){
        alert()
    }
  },
  beforeMount() {
    this.loadData();
  }
})
</script>

<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-lock px-2"></i>
        تغییر کلمه عبور
      </h3>
      <div class="block-options">
        <button :disabled="this.loading" @click="this.submit()" type="button" class="btn btn-sm btn-primary">
          <div v-show="this.loading" class="spinner-grow spinner-grow-sm me-2" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <i class="fa fa-save"></i>
          ذخیره تغییرات
        </button>
      </div>
    </div>
    <div class="block-content pt-1 pb-3">
      <div class="row">
        <div class="col-sm-12 col-md-12 m-0 p-0">
            <div class="row my-2 mx-1">
                <div class="col-sm-12 col-md-12">
                    <div class="alert alert-danger">
                        شما در حال تغییر کلمه عبور کاربر با مشخصات ذیل هستید:
                        <ul>
                            <li>نام:{{ userInfo.fullname }}</li>
                            <li>تلفن همراه:{{ userInfo.mobile }}</li>
                            <li>پست الکترونیکی:{{ userInfo.email }}</li>
                        </ul> 
                    </div>
                </div>
            </div>
            <div class="row my-2 mx-1">
                <div class="col-sm-12 col-md-4">
                    <div class="mb-4">
                        <label class="form-label">کلمه عبور جدید</label>
                        <input style="direction:ltr" v-model="password" class="form-control" type="password" :disabled="loading">
                    </div>
                </div>
            </div>
            <div class="row my-2 mx-1">
                <div class="col-sm-12 col-md-12">
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" v-model="this.sendSMS">
                        <label class="form-check-label" >ارسال پیامک به کاربر</label>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>