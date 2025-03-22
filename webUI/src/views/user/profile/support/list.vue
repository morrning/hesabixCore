<template>
  <v-toolbar color="toolbar" :title="$t('pages.support.titlebar')">
    <v-spacer></v-spacer>
    <v-tooltip :text="$t('user.ticket_new')" location="bottom">
      <template #activator="{ props }">
        <v-btn v-bind="props" icon="mdi-chat-plus" color="primary" to="/profile/support-new"></v-btn>
      </template>
    </v-tooltip>
  </v-toolbar>

  <v-container class="pa-0 ma-0">
    <v-card :disabled="loading">
      <v-tabs v-model="activeTab" color="primary" grow>
        <v-tab value="pending" class="flex-grow-1">
          <v-icon start>mdi-progress-clock</v-icon> در حال پیگیری ({{ pendingItems.length }})
        </v-tab>
        <v-tab value="responded" class="flex-grow-1">
          <v-icon start>mdi-check-circle</v-icon> پاسخ داده شده ({{ respondedItems.length }})
        </v-tab>
        <v-tab value="closed" class="flex-grow-1">
          <v-icon start>mdi-lock</v-icon> خاتمه یافته ({{ closedItems.length }})
        </v-tab>
      </v-tabs>

      <v-window v-model="activeTab">
        <v-window-item value="pending">
          <v-data-table :headers="headers" :items="pendingItems" :loading="loading" :no-data-text="$t('table.no_data')"
            class="elevation-1">
            <template #item.operation="{ item }">
              <v-tooltip :text="$t('dialog.view')" location="bottom">
                <template #activator="{ props }">
                  <v-btn v-bind="props" color="primary" icon="mdi-eye" size="x-small" variant="flat"
                    :to="'/profile/support-view/' + item.id" @click="loading = true" />
                </template>
              </v-tooltip>
            </template>
          </v-data-table>
        </v-window-item>

        <v-window-item value="responded">
          <v-data-table :headers="headers" :items="respondedItems" :loading="loading"
            :no-data-text="$t('table.no_data')" class="elevation-1">
            <template #item.operation="{ item }">
              <v-tooltip :text="$t('dialog.view')" location="bottom">
                <template #activator="{ props }">
                  <v-btn v-bind="props" color="primary" icon="mdi-eye" size="x-small" variant="flat"
                    :to="'/profile/support-view/' + item.id" @click="loading = true" />
                </template>
              </v-tooltip>
            </template>
          </v-data-table>
        </v-window-item>

        <v-window-item value="closed">
          <v-data-table :headers="headers" :items="closedItems" :loading="loading" :no-data-text="$t('table.no_data')"
            class="elevation-1">
            <template #item.operation="{ item }">
              <v-tooltip :text="$t('dialog.view')" location="bottom">
                <template #activator="{ props }">
                  <v-btn v-bind="props" color="primary" icon="mdi-eye" size="x-small" variant="flat"
                    :to="'/profile/support-view/' + item.id" @click="loading = true" />
                </template>
              </v-tooltip>
            </template>
          </v-data-table>
        </v-window-item>
      </v-window>
    </v-card>
  </v-container>
</template>

<script>
import axios from "axios";

export default {
  name: "list",
  data() {
    return {
      loading: true,
      items: [],
      activeTab: null,
      headers: [
        { title: "", key: "operation", align: "center", sortable: false },
        { title: "شناسه", key: "id", align: "center", sortable: true },
        { 
          title: "کسب‌وکار", 
          key: "bid.name", // تغییر به bid.name
          align: "center", 
          sortable: true,
          value: item => item.bid ? item.bid.name : 'بدون کسب‌وکار' // مدیریت null
        },
        { title: "عنوان", key: "title", align: "center", sortable: true },
        { title: "تاریخ", key: "dateSubmit", align: "center", sortable: true },
      ],
    };
  },
  computed: {
    pendingItems() {
      return this.items.filter((item) => item.state === "در حال پیگیری");
    },
    respondedItems() {
      return this.items.filter((item) => item.state === "پاسخ داده شده");
    },
    closedItems() {
      return this.items.filter((item) => item.state === "خاتمه یافته");
    },
  },
  mounted() {
    this.loadData();
  },
  methods: {
    loadData() {
      axios.post("/api/support/list").then((response) => {
        this.items = response.data;
        this.loading = false;
        this.activeTab = this.pendingItems.length > 0 ? "pending" : "responded";
      }).catch((error) => {
        console.error("Error loading support list:", error);
        this.loading = false;
      });
    },
  },
};
</script>

<style scoped>
.v-tabs {
  width: 100%;
}

.v-tab {
  flex: 1;
}
</style>