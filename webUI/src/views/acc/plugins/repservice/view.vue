<script lang="ts">
import { defineComponent, ref } from 'vue'
import axios from "axios";
import Swal from "sweetalert2";

import type { Header, Item } from "vue3-easy-data-table";

export default defineComponent({
  name: "view",
  components: {
   
  },
  watch: {
    
  },
  data: () => {
    return {
      
    }
  },
  setup() {

  },
  methods: {
    
  },
  mounted() {
    
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
        <i class="fas fa-file-invoice-dollar"></i>
        مشاهده  درخواست
      </h3>
      <div class="block-options">
      </div>
    </div>
    <div class="block-content pt-1 pb-3 d-none d-sm-block">
     
    </div>

    
  </div>

</template>

<style scoped>

</style>