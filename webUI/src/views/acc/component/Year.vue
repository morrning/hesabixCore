<template>
  <div class="py-1 text-light">
    <i class="fa fa-calendar-days"></i>
    سال مالی</div>
  <select v-model="activeYear" @change="changeyear($event)" class="form-select form-select-sm" >
    <option class="" v-for="item in years" :value="item.id">{{item.label}}</option>
  </select>
</template>

<script>
import axios from "axios";

export default {
  name: "Year",
  data: ()=>{return{
    activeYear: null,
    years:{}
  }},
  async mounted() {
    await axios.post('/api/year/list').then((response)=>{
      this.years = response.data;
      if(localStorage.getItem('activeYear') == ''){
        this.activeYear = localStorage.getItem('activeYear');
      }
      else{
        this.years.forEach((item)=>{
          if(item.head == '1') {
            this.activeYear = item.id;
            localStorage.setItem('activeYear',item.id);
          }
        });
      }
    })
  },
  methods:{
      changeyear(event){
        localStorage.setItem('activeYear',event.target.value);
        window.location.reload();
      }
  }
}
</script>

<style scoped>

</style>