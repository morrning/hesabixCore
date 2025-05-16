<script lang="ts">
import { defineComponent } from 'vue'
import axios from 'axios';

interface NotificationItem {
  id: number;
  message: string;
  date: string;
  url: string;
}

export default defineComponent({
  name: "notifications_btn",
  data: () => ({
    items: [] as NotificationItem[],
    timeoutId: null as number | null, // برای ذخیره ID تایمر
  }),
  components: {},
  mounted() {
    this.loadData();
  },
  beforeUnmount() {
    // پاک کردن تایمر هنگام تخریب کامپوننت
    if (this.timeoutId) {
      clearTimeout(this.timeoutId);
    }
  },
  methods: {
    jump(item: NotificationItem) {
      axios.post('/api/notifications/read/' + item.id).then((response) => {
        if (item.url.startsWith('http')) {
          window.location.href = item.url;
        } else {
          this.$router.push(item.url);
        }
      });
    },
    loadData() {
      axios.post('/api/notifications/list/new').then((response) => {
        if (response.data.length != 0) {
          this.items = response.data;
        } else {
          this.items = [];
        }
      }).finally(() => {
        // تنظیم تایمر جدید و ذخیره ID آن
        this.timeoutId = setTimeout(this.loadData, 10000);
      });
    }
  }
});
</script>

<template>
  <v-menu location="bottom">
    <template #activator="{ props }">
      <v-btn v-bind="props" stacked>
        <v-badge color="error" :content="items.length">
          <v-icon icon="mdi-bell"></v-icon>
        </v-badge>
      </v-btn>
    </template>
    <v-card prepend-icon="mdi-bell" :subtitle="$t('dialog.unread_notifications')" :title="$t('dialog.notifications')">
      <v-list>
        <v-list-item v-for="(item, i) in items" :key="i" :value="item" @click="jump(item)">
          <template #prepend>
            <v-icon color="primary" icon="mdi-alert-box-outline"></v-icon>
          </template>
          <v-list-item-title class="text-primary" v-text="item.message"></v-list-item-title>
          <v-list-item-subtitle v-text="item.date"></v-list-item-subtitle>
        </v-list-item>
        <v-list-item v-if="items.length == 0">
          <template #prepend>
            <v-icon color="primary" icon="mdi-cards-heart"></v-icon>
          </template>
          <v-list-item-title class="text-primary" v-text="$t('dialog.no_notification')"></v-list-item-title>
        </v-list-item>
      </v-list>
      <v-card-actions>
        <v-btn block to="/acc/notifications/list" color="success" :text="$t('dialog.show_notifications')"
          prepend-icon="mdi-eye" />
      </v-card-actions>
    </v-card>
  </v-menu>
</template>

<style scoped></style>