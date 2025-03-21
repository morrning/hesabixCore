<script>
import {defineComponent} from 'vue'
import axios from "axios";

export default defineComponent({
  name: "plugin-world",
  data: ()=>{return{
    plugins:{}
  }},
  methods:{
    loadData(){
      axios.post('/api/plugin/get/all').then((response)=>{
        this.plugins = response.data;
      })
    }
  },
  created() {
    this.loadData()
  }
})
</script>

<template>
  <div class="block block-content-full">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light" >
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-cog"></i>
        فهرست افزونه‌ها
      </h3>
      <div class="block-options">
      </div>
    </div>
    <div class="block-content pb-3">
      <div class="container-fluid">
        <div class="row">
          <div v-for="plugin in plugins" class="col-md-6 col-xl-4">
            <!-- House -->
            <div class="block block-rounded border">
              <div class="block-content p-0 overflow-hidden">
                <a class="img-link img-fluid-100" data-action="side_overlay_open" data-toggle="layout" href="javascript:void(0)">
                  <img alt="" class="img-fluid rounded-top" :src="'/img/plugins/' + plugin.icon">
                </a>
              </div>
              <div class="block-content">
                <h4 class="h6 mb-2">
                  <i class="fa fa-plug-circle-plus"></i>
                  {{plugin.name}}
                </h4>
                <h5 class="h2 fw-light push">{{Intl.NumberFormat('en-US').format(plugin.price)}}  تومان<br><span class="fs-3 text-muted"><i class="fa fa-clock"></i> {{plugin.timelabel}} </span>
                </h5>
              </div>
              <div class="block-content p-0">
                <div class="row text-center m-0 border-top border-bottom bg-body-light">
                  <div class="col-6 border-end">
                    <p class="py-3 mb-0">
                      <i class="fa fa-fw fa-ticket text-muted me-1"></i>  پشتیبانی دارد </p>
                  </div>
                  <div class="col-6">
                    <p class="py-3 mb-0">
                      <i class="fa fa-fw fa-users-rectangle text-muted me-1"></i>  کاربر نامحدود </p>
                  </div>
                </div>
              </div>
              <div class="block-content block-content-full">
                <div class="row">
                  <div class="col-6">
                    <RouterLink class="btn btn-sm btn-primary w-100" :to="'/acc/plugin-center/view-end/' + plugin.code"> خرید </RouterLink>
                  </div>
                  <div class="col-6">
                    <RouterLink :to="'/acc/plugins/' + plugin.code +'/intro'" class="btn btn-sm btn-alt-primary w-100"> کاتالوگ </RouterLink>
                  </div>
                </div>
              </div>
            </div>
            <!-- END House -->
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<style scoped>

</style>