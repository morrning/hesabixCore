<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button type="button" @click="$router.back()" class="btn text-warning mx-2 px-2">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        ویژگی کالا و خدمات </h3>
      <div class="block-options">
        <button :disabled="isLoading" @click="save()" type="button" class="btn btn-alt-primary"><i class="fa fa-save"></i> ثبت</button>
      </div>
    </div>
    <div class="block-content py-3 vl-parent">
      <loading color="blue" loader="dots" v-model:active="isLoading" :is-full-page="false"/>
      <div class="container">
        <div class="row py-3">
          <div class="col-sm-12 col-md-6">
            <div class="form-floating mb-4">
              <input v-model="data.name" class="form-control" type="text">
              <label class="form-label">نام ویژگی</label>
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
import Treeselect from 'vue3-treeselect'
// import the styles
import 'vue3-treeselect/dist/vue3-treeselect.css'
export default {
  name: "mod",
  components: {
    Loading,
    Money3,
    Treeselect:Treeselect
  },
  data: ()=>{return{
    isLoading: false,
    units:'',
    data: {
      id:0,
      name: '',
    },
    currencyConfig:{
      masked: false,
      prefix: '',
      suffix: 'ریال',
      thousands: ',',
      decimal: '.',
      precision: 0,
      disableNegative: false,
      disabled: false,
      min: 0,
      max: null,
      allowBlank: false,
      minimumNumberOfCharacters: 0,
      shouldRound: true,
      focusOnRight: false,
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
        axios.post('/api/commodity/drop/info/' + id).then((response) => {
          this.data = response.data;
        });
      }
      this.isLoading = false;
    },
    save() {
      if (this.data.name.length === 0)
        Swal.fire({
          text: 'نام ویژگی الزامی است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      else {
        this.isLoading = true;
        axios.post('/api/commodity/drop/mod/' + this.data.id, this.data).then((response) => {
          this.isLoading = false;
          if (response.data.result == 2) {
            Swal.fire({
              text: 'قبلا ثبت شده است.',
              icon: 'error',
              confirmButtonText: 'قبول'
            });
          } else {
            Swal.fire({
              text: 'ویژگی کالا و خدمات ثبت شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            }).then(() => {
              this.$router.push('/acc/commodity/drop/list')
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