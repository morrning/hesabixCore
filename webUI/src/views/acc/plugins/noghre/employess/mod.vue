<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <router-link class="text-warning mx-2 px-2" to="/acc/persons/list">
          <i class="fa fw-bold fa-arrow-right"></i>
        </router-link>
        مشخصات شاغل </h3>
    </div>
    <div class="block-content py-3 vl-parent">
      <loading color="blue" loader="dots" v-model:active="isLoading" :is-full-page="false"/>
      <div class="container">
        <div class="row">
          <div class="col-sm-12 col-md-6">
            <div class="alert alert-info">
              برای ثبت مشخصات شخص جدید از بخش اشخاص اقدام کنید.
            </div>
            <div class="form-control mb-2">
              <label class="form-label">شخص</label>
              <v-cob
                  dir="rtl"
                  :options="this.persons"
                  label="nikename"
                  v-model="this.person"
              >
                <template #no-options="{ search, searching, loading }">
                  نتیجه‌ای یافت نشد!
                </template>
              </v-cob>
            </div>
            <b>نوع همکاری</b>
            <div class="mt-1 mb-3 border rounded-2 p-3">
              <div class="form-check form-check-inline">
                <input v-model="this.morsa" class="form-check-input" type="checkbox" >
                <label class="form-check-label">مرصع‌کار</label>
              </div>
              <div class="form-check form-check-inline">
                <input v-model="this.hakak" class="form-check-input" type="checkbox" >
                <label class="form-check-label">حکاکی کننده</label>
              </div>
              <div class="form-check form-check-inline">
                <input v-model="this.tarash" class="form-check-input" type="checkbox" >
                <label class="form-check-label">تراشکار</label>
              </div>
              <div class="form-check form-check-inline">
                <input v-model="this.ghalam" class="form-check-input" type="checkbox" >
                <label class="form-check-label">قلم زن</label>
              </div>
            </div>

          </div>
        </div>
        <button @click="save()" type="button" class="btn btn-alt-primary">ثبت</button>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from "sweetalert2";
import axios from "axios";
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
export default {
  name: "mod",
  components:{
    Loading
  },
  data: ()=>{return{
    isLoading: false,
    person: null,
    persons:{},
    hakak:false,
    tarash:false,
    morsa:false,
    ghalam:false
  }},
  created() {
    this.loadData(this.$route.params.id);
  },
  methods:{
    loadData(id = ''){
      //load persons
      this.isLoading = true;
      axios.post('/api/person/list/limit')
          .then((response)=>{
            this.persons = response.data;
            if(id != ''){
              //load user info
              axios.post('/api/person/info/' + id).then((response)=>{
                this.person = response.data;
                this.morsa = response.data.plugNoghreMorsa;
                this.tarash = response.data.plugNoghreTarash;
                this.hakak = response.data.plugNoghreHakak;
                this.ghalam = response.data.plugNoghreGhalam;
              });
            }
            this.isLoading = false;
          });
    },
    save(){
      if(this.person === null)
        Swal.fire({
          text: 'شخصی انخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      else{
        this.isLoading = true;
        axios.post('/api/plugin/noghre/employess/mod/' + this.person.code,{
          'morsa': this.morsa,
          'tarash': this.tarash,
          'hakak': this.hakak,
          'ghalam':this.ghalam
        }).then((response)=>{
          this.isLoading = false;
          Swal.fire({
            text: 'مشخصات شاغل ثبت شد.',
            icon: 'success',
            confirmButtonText: 'قبول'
          }).then(()=>{
            this.$router.push('/acc/plugin/noghre/employees/list')
          } );
        })
      }

    }
  }
}
</script>

<style scoped>

</style>