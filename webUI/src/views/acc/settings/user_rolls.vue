<template>
  <div class="block block-content-full">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light" >
      <h3 class="block-title text-primary-dark">
        <i class="fa fa-users-gear"></i>
        کاربران و دسترسی‌ها </h3>
      <div class="block-options">
      </div>
    </div>
    <div class="block-content p-0">
      <div class="mb-4">
        <div class="input-group p-3">
          <div class="form-floating">
            <input v-model="newEmail" class="form-control" type="email">
            <label for="example-group3-floating2">برای افزودن کاربر جدید پست الکترونیکی را وارد کنید.</label>
          </div>
          <button @click="submitData" class="btn btn-primary" type="button"> افزودن </button>
        </div>
      </div>
      <table class="table table-hover table-vcenter table-sm">
        <thead>
        <tr>
          <th class="text-center" style="width: 50px;">#</th>
          <th>نام / نام خانوادگی</th>
          <th>پست الکترونیکی</th>
          <th class="text-center" style="width: 100px;">عملیات</th>
        </tr>
        </thead>
        <tbody>
          <tr v-for="(item,index) in users">
            <th class="text-center" scope="row">{{ index + 1 }}</th>
            <td class="fw-semibold text-primary-dark">{{ item.name}}</td>
            <td class="d-none d-sm-table-cell">
              <span class="badge bg-primary">{{item.email}}</span>
            </td>
            <td class="text-center">
              <div class="btn-group btn-group-sm" v-if="item.owner != 1">
                <router-link :to="{'name':'business_user_roll_edit','params':{'email':item.email}}"  class="btn btn-alt-primary" type="button" aria-label=" ویرایش ">
                  <i class="fa fa-pencil-alt"></i>
                </router-link>
                <button @click="deleteUser(item.email)"  class="btn btn-alt-primary" type="button" aria-label=" حذف ">
                  <i class="fa fa-times"></i>
                </button>
              </div>
              <span class="badge bg-success" v-if="item.owner == 1">مدیر کل</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: "user_rolls",
  data: ()=>{return{
    users : {},
    newEmail: '',
  }},
  methods:{
    deleteUser(email){
      Swal.fire({
        title: 'آیا برای حذف کاربر مطمئن هستید؟',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          axios.post('/api/business/delete/user',{
            'bid':localStorage.getItem('activeBid'),
            'email': email}
          ).then((response)=>{
            if(response.data.result == 1){
              let index = 0;
              for(let z=0; z<this.users.length; z++){
                index ++;
                if(this.users[z]['email'] == email){
                  this.users.splice(index -1 ,1);
                }
              }
              Swal.fire({
                text: 'کاربر با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            }
          })
        }
      })
    },
    submitData(){
      if(this.newEmail == ''){
        Swal.fire({
          text: 'پست الکترونیکی را وارد کنید.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      else {
        axios.post("/api/business/add/user",{'bid':localStorage.getItem('activeBid'),'email': this.newEmail}).then((response)=>{
            if(response.data.result == 0){
              Swal.fire({
                text: 'کاربری با این پست الکترونیکی یافت نشد.',
                icon: 'error',
                confirmButtonText: 'قبول'
              });
            }
            else if(response.data.result == 1){
              Swal.fire({
                text: 'قبلا این کاربر به کسب و کار افزوده شده است.',
                icon: 'error',
                confirmButtonText: 'قبول'
              });
            }
            else{
              let temp = {
                'name':response.data.data.name,
                'email':response.data.data.email,
                'owner':response.data.data.owner
              };
              this.users.push(temp);
              Swal.fire({
                text: 'کاربر با موفقیت عضو کسب و کار شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            }
          this.newEmail = '';
        });
      }
    }
  },
  mounted() {
    axios.post('/api/user/get/users/of/business/' + localStorage.getItem('activeBid')).then((response)=>{
      this.users = response.data;
    })
  }
}
</script>

<style scoped>

</style>