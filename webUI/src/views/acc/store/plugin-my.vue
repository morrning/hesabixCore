<script>
import {defineComponent} from 'vue'
import axios from "axios";

export default defineComponent({
  name: "plugin-my",
  data: ()=>{return {
    plugins:{}
  }},
  methods:{
    loadData(){
      axios.post('/api/plugin/get/actives').then((response)=>{
        this.plugins = response.data;
      })
    }
  },
  created() {
    this.loadData();
  }
})
</script>

<template>
  <div class="block block-content-full">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light" >
      <h3 class="block-title text-primary-dark">
        <i class="fa fa-cog"></i>
        افزونه‌های فعال
      </h3>
      <div class="block-options">
      </div>
    </div>
    <div class="block-content pb-3">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="bg-body-extra-light">
              <table class="table table-striped table-hover table-borderless table-vcenter">
                <tbody>
                <tr v-for="plugin in plugins">
                  <td>
                    <div class="d-sm-flex">
                      <div class="ms-sm-2 me-sm-4 py-3">
                        <a class="item item-rounded bg-body-dark text-dark fs-2 mb-2 mx-auto" href="javascript:void(0)">
                          <i class="fa fa-fw fa-plug-circle-plus"></i>
                        </a>
                      </div>
                      <div class="py-3">
                        <RouterLink :to="'/acc/plugins/' + plugin.name + '/intro'" class="link-fx h4 mb-1 d-inline-block text-dark"> {{plugin.des}} </RouterLink>
                        <div class="fs-sm fw-semibold text-muted mb-2"> تاریخ پایان اعتبار : {{plugin.dateExpire}} </div>
                      </div>
                    </div>
                  </td>
                </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<style scoped>

</style>