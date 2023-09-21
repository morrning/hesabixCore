<template>
  <!-- Your Block -->
  <div class="block block-content-full">
    <div class="block-header block-header-default" style="background-color: #0E2231">
      <h3 class="block-title text-white"> پیشخوان </h3>
      <div class="block-options">
        <button class="btn-block-option" data-action="fullscreen_toggle" data-toggle="block-option" type="button"></button>
        <button class="btn-block-option" data-action="pinned_toggle" data-toggle="block-option" type="button">
          <i class="si si-pin"></i>
        </button>
        <button class="btn-block-option" data-action="state_toggle" data-action-mode="demo" data-toggle="block-option" type="button">
          <i class="si si-refresh"></i>
        </button>
        <button class="btn-block-option" data-action="content_toggle" data-toggle="block-option" type="button"></button>
        <button class="btn-block-option" data-action="close" data-toggle="block-option" type="button">
          <i class="si si-close"></i>
        </button>
      </div>
    </div>
    <div class="block-content">
      <p>پیشخوان</p>
    </div>
  </div>
  <!-- END Your Block -->
</template>

<script>
export default {
  name: "base"
}
</script>

<style scoped>

</style>