<script lang="ts">
import {defineComponent} from 'vue'
import axios from "axios";
import Swal from "sweetalert2";

export default defineComponent({
  name: "add-mobile",
  data:()=>{return{
    phoneNumber:'',
    user:{
      id:''
    },
    isLoading:false
  }},
  mounted() {
    this.loadData();
  },
  methods:{
    loadData(){
      axios.post('/api/user/current/info')
          .then((res) => {
            this.user = res.data;
            if(res.data.mobile){
              this.$router.push('/user/active/'+this.user.id.toString() )
            }
          });
    },
    save(){
      if(this.phoneNumber.length === 11){
        this.isLoading = true;
        axios.post('/api/user/save/mobile-number',{
          mobile:this.phoneNumber
        }).then((response)=>{
          this.isLoading = false;
          this.$router.push('/user/active/'+this.user.id )
        });
      }
      else{
        Swal.fire({
          text: 'شماره وارد شده صحیح نیست.',
          confirmButtonText: 'قبول',
        })
      }
    }
  }
})
</script>

<template>
  <div class="block block-rounded">
    <div class="block-header block-header-default">
      <h3 class="block-title">
        ثبت شماره تلفن همراه
      </h3>
    </div>
    <div class="block-content mt-0">
      <div class="row pb-sm-3 pb-md-5">
        <div class="col-sm-12 col-md-6">
          <div class="form-floating mb-3">
            <input class="form-control" type="text" v-model="phoneNumber" placeholder="09181234567">
            <label>شماره تلفن همراه</label>
          </div>
          <button @click="save()" :disabled="isLoading" type="button" class="btn btn-alt-primary mt-3 mb-4">ثبت شماره تلفن</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>