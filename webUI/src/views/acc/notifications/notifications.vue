<script lang="ts">
import {defineComponent} from 'vue'
import axios from "axios";

export default defineComponent({
  name: "notifications",
  data: ()=>{return{
    items:undefined,
  }},
  mounted() {
    axios.post('/api/notifications/list/all').then((response)=>{
      if(response.data.length !== 0){
        this.items = response.data;
      }
      else{
        this.items = undefined;
      }
    })
  },
  methods:{
    jump(item){
      axios.post('/api/notifications/read/' + item.id).then((response)=>{
        if(item.url.startsWith('http')){
            window.location.href = item.url;
        }
        else{
            this.$router.push(item.url);
        }
      })
    }
  }
})
</script>

<template>
  <div class="block block-content-full">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light" >
      <h3 class="block-title text-primary-dark">
        <i class="fa fa-bell"></i>
        اعلان‌ها
      </h3>
      <div class="block-options">
      </div>
    </div>
    <div class="block-content pb-3">
      <div class="container-fluid">
        <div class="row">
          <div v-if="items == undefined" class="col-auto">
            <h3 class="text-center">
              همه چی آرومه...
            </h3>
            <p>
              فعلا هیچ اعلانی ندارید.
            </p>
          </div>
          <div v-else class="col-12">
            <ul class="nav-items my-2">
              <li v-for="item in items">
                <a class="d-flex text-dark py-2" @click.prevent="jump(item)">
                  <div class="flex-shrink-0 mx-3">
                    <i :class="'text-' + item.icon" class="fa fa-fw fa-check-circle"></i>
                  </div>
                  <div class="flex-grow-1 fs-sm pe-2">
                    <div class="fw-semibold">{{item.message}}</div>
                    <div class="text-muted">{{item.date}} قبل</div>
                  </div>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>