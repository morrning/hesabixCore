<script lang="ts">
import {defineComponent} from 'vue'
import axios from "axios";

export default defineComponent({
  name: "smsPanel",
  data:()=>{return {
      smsCharge: 0
  }},
  mounted() {
    this.loadData();
  },
  methods:{
    loadData(){
      axios.post('/api/business/get/info/' + localStorage.getItem('activeBid')).then((response) => {
        this.smsCharge = response.data.smsCharge;
      });
      setTimeout(this.loadData, 10000);
    }
  }
})
</script>

<template>
  <div class="">
    <router-link class="btn btn-link btn-sm" to="/acc/sms/panel">
          <i class="fa fa-plus"></i>
    </router-link>
    اعتبار :
    <span class="text-primary-dark">{{ $filters.formatNumber(smsCharge)}}</span>
    ریال
  </div>
</template>

<style scoped>

</style>