<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: "clock",
  data() {
    return {
      dateNow: '',
      clock: {
        h: '00',
        m: '00'
      },
      interval: 0, // بدون نیاز به تایپ NodeJS.Timeout
    };
  },
  mounted() {
    this.updateTime();
    this.startTimer();
  },
  beforeUnmount() {
    clearInterval(this.interval);
  },
  methods: {
    startTimer() {
      this.interval = setInterval(() => {
        this.updateTime();
      }, 1000);
    },
    updateTime() {
      const now = new Date();
      this.dateNow = now.toLocaleDateString('fa-IR', {
        weekday: 'long',
        year: 'numeric',
        month: 'numeric',
        day: 'numeric'
      });
      const hours = now.getHours();
      const minutes = now.getMinutes();
      this.clock.h = hours < 10 ? '0' + hours : String(hours);
      this.clock.m = minutes < 10 ? '0' + minutes : String(minutes);
    }
  }
});
</script>

<template>
  <div class="d-none d-sm-flex">
    <v-icon icon="mdi-clock" color="primary"></v-icon>
    {{ $t('drawer.today') }}
    <span class="text-primary">{{ dateNow }}</span>
    {{ $t('drawer.clock') }}
    <span class="text-primary">{{ clock.h + ':' + clock.m }}</span>
  </div>
</template>

<style scoped></style>