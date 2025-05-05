<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <router-link class="text-warning mx-2 px-2" to="/acc/cashdesk/list">
          <i class="fa fw-bold fa-arrow-right"></i>
        </router-link>
        مشخصات صندوق </h3>
      <div class="block-options">
        <button @click="save()" type="button" class="btn btn-alt-primary"><i class="fa fa-save"></i> ثبت</button>
      </div>
    </div>
    <div class="block-content py-3 vl-parent">
      <loading color="blue" loader="dots" v-model:active="isLoading" :is-full-page="false"/>
      <div class="container">
        <div class="row">
          <div class="col-sm-12 col-md-6">
            <div class="form-floating mb-4">
              <input v-model="data.name" class="form-control" type="text">
              <label class="form-label"><span class="text-danger">(لازم)</span> نام</label>
            </div>
            <div class="form-floating mb-4">
              <input v-model="data.des" class="form-control" type="text">
              <label class="form-label">توضیحات</label>
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

export default {
  name: "mod",
  components: {
    Loading
  },
  data: ()=>{return{
    isLoading: false,
    data: {
      code: 0,
      name: '',
      des: '',
    }
  }},
  mounted() {
    this.loadData(this.$route.params.id);
  },
  methods: {
    loadData(id = '') {
      if (id != '') {
        //load user info
        this.isLoading = true;
        axios.post('/api/cashdesk/info/' + id).then((response) => {
          this.data = response.data;
          this.isLoading = false;
        });
      } else {
        //salary is new
        this.data.name = '';
        this.data.des = '';
      }
    },
    save() {
      if (this.data.name.length === 0)
        Swal.fire({
          text: 'نام الزامی است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      else {
        this.isLoading = true;
        axios.post('/api/cashdesk/mod/' + this.data.code, this.data).then((response) => {
          this.isLoading = false;
          if (response.data.result == 2) {
            Swal.fire({
              text: 'قبلا ثبت شده است.',
              icon: 'error',
              confirmButtonText: 'قبول'
            });
          } else {
            Swal.fire({
              text: 'مشخصات صندوق ثبت شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            }).then(() => {
              this.$router.push('/acc/cashdesk/list')
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