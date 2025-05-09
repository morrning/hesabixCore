<script lang="ts">
import {defineComponent} from 'vue'

export default defineComponent({
  name: "helpBtn",
  props:{
    vsource: String,
    label: String,
    rndiv: String,
  }
})
</script>

<template>
  <!-- Button trigger modal -->
  <button type="button" class="btn btn-sm btn-link text-info mx-2" data-bs-toggle="modal" data-bs-target="#staticBackdrop" >
    <i class="fa fa-question-circle"></i>
  </button>

  <!-- Modal -->
  <div class="modal modal-lg fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary-light text-white">
          <h1 class="modal-title fs-5" id="staticBackdropLabel">{{this.$props.label}}</h1>
          <div class="block-options">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
          <div :id="this.$props.rndiv"><component is="script" :src="this.$props.vsource"></component></div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>

</style>