<template>
  <v-toolbar color="toolbar" :title="$t('pages.support.titlebar_admin')">
    <v-spacer></v-spacer>
    <v-select v-if="selectedTickets.length > 0" v-model="bulkState" :items="states" label="تغییر وضعیت دسته‌جمعی"
      prepend-inner-icon="mdi-list-status" class="mx-2" style="max-width: 200px"></v-select>
    <v-btn v-if="selectedTickets.length > 0" color="primary" prepend-icon="mdi-content-save" @click="bulkUpdateState"
      :loading="loading">
      اعمال
    </v-btn>
  </v-toolbar>

  <v-container class="pa-0 ma-0">
    <v-card :loading="loading ? 'red' : null" :disabled="loading">
      <!-- فرم جست‌وجو -->
      <v-row class="pa-4">
        <v-col cols="12">
          <v-text-field v-model="searchQuery" label="جست‌وجو (شماره تیکت، نام کسب‌وکار، نام کاربر)"
            prepend-inner-icon="mdi-magnify" clearable></v-text-field>
        </v-col>
      </v-row>

      <v-tabs v-model="activeTab" color="primary" grow>
        <v-tab value="pending" class="flex-grow-1">
          <v-icon start>mdi-progress-clock</v-icon> در حال پیگیری ({{ totalPending }})
        </v-tab>
        <v-tab value="responded" class="flex-grow-1">
          <v-icon start>mdi-check-circle</v-icon> پاسخ داده شده ({{ totalResponded }})
        </v-tab>
        <v-tab value="closed" class="flex-grow-1">
          <v-icon start>mdi-lock</v-icon> خاتمه یافته ({{ totalClosed }})
        </v-tab>
      </v-tabs>

      <v-window v-model="activeTab">
        <v-window-item value="pending">
          <v-data-table-server v-model:items-per-page="itemsPerPagePending" v-model:page="pagePending"
          :header-props="{ class: 'custom-header' }" :headers="headers" :items="pendingItems" :items-length="totalPending" :loading="loading"
            :no-data-text="$t('table.no_data')" class="elevation-1" show-select v-model="selectedTickets"
            @update:options="loadPendingData">
            <template #item.operation="{ item }">
              <v-tooltip :text="$t('dialog.view')" location="bottom">
                <template #activator="{ props }">
                  <v-btn v-bind="props" color="primary" icon="mdi-eye" size="x-small" variant="flat"
                    :to="'/profile/manager/support-view/' + item.id" @click="loading = true" />
                </template>
              </v-tooltip>
            </template>
          </v-data-table-server>
        </v-window-item>

        <v-window-item value="responded">
          <v-data-table-server v-model:items-per-page="itemsPerPageResponded" v-model:page="pageResponded"
          :header-props="{ class: 'custom-header' }" :headers="headers" :items="respondedItems" :items-length="totalResponded" :loading="loading"
            :no-data-text="$t('table.no_data')" class="elevation-1" show-select v-model="selectedTickets"
            @update:options="loadRespondedData">
            <template #item.operation="{ item }">
              <v-tooltip :text="$t('dialog.view')" location="bottom">
                <template #activator="{ props }">
                  <v-btn v-bind="props" color="primary" icon="mdi-eye" size="x-small" variant="flat"
                    :to="'/profile/manager/support-view/' + item.id" @click="loading = true" />
                </template>
              </v-tooltip>
            </template>
          </v-data-table-server>
        </v-window-item>

        <v-window-item value="closed">
          <v-data-table-server v-model:items-per-page="itemsPerPageClosed" v-model:page="pageClosed" :headers="headers"
          :header-props="{ class: 'custom-header' }":items="closedItems" :items-length="totalClosed" :loading="loading" :no-data-text="$t('table.no_data')"
            class="elevation-1" show-select v-model="selectedTickets" @update:options="loadClosedData">
            <template #item.operation="{ item }">
              <v-tooltip :text="$t('dialog.view')" location="bottom">
                <template #activator="{ props }">
                  <v-btn v-bind="props" color="primary" icon="mdi-eye" size="x-small" variant="flat"
                    :to="'/profile/manager/support-view/' + item.id" @click="loading = true" />
                </template>
              </v-tooltip>
            </template>
          </v-data-table-server>
        </v-window-item>
      </v-window>
    </v-card>
  </v-container>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import debounce from "lodash/debounce";

export default defineComponent({
  name: "SupportList",
  data() {
    return {
      loading: false,
      activeTab: "pending",
      headers: [
        { title: "", key: "operation", align: "center", sortable: false },
        { title: "شناسه", key: "id", align: "center", sortable: true },
        { title: "کاربر", key: "submitter.fullName", align: "center", sortable: true },
        {
          title: "کسب‌وکار",
          key: "bid.name",
          align: "center",
          sortable: true,
          value: (item) => (item.bid ? item.bid.name : "بدون کسب‌وکار"),
        },
        { title: "عنوان", key: "title", align: "center", sortable: true },
        { title: "تاریخ", key: "dateSubmit", align: "center", sortable: true },
      ],
      pendingItems: [],
      respondedItems: [],
      closedItems: [],
      pagePending: 1,
      pageResponded: 1,
      pageClosed: 1,
      itemsPerPagePending: 10,
      itemsPerPageResponded: 10,
      itemsPerPageClosed: 10,
      totalPending: 0,
      totalResponded: 0,
      totalClosed: 0,
      selectedTickets: [],
      bulkState: null,
      states: ["در حال پیگیری", "پاسخ داده شده", "خاتمه یافته"],
      searchQuery: "",
    };
  },
  watch: {
    searchQuery: {
      handler: debounce(function () {
        this.pagePending = 1;
        this.pageResponded = 1;
        this.pageClosed = 1;
        this.refreshAllData();
      }, 1000), // 1 ثانیه تأخیر
      immediate: false,
    },
  },
  methods: {
    async loadPendingData(options: { page: number; itemsPerPage: number }) {
      await this.loadData("در حال پیگیری", options, "pending");
    },
    async loadRespondedData(options: { page: number; itemsPerPage: number }) {
      await this.loadData("پاسخ داده شده", options, "responded");
    },
    async loadClosedData(options: { page: number; itemsPerPage: number }) {
      await this.loadData("خاتمه یافته", options, "closed");
    },
    async loadData(state: string, options: { page: number; itemsPerPage: number }, type: string) {
      this.loading = true;
      try {
        const response = await axios.post("/api/admin/support/list", {
          state,
          page: options.page,
          itemsPerPage: options.itemsPerPage,
          searchQuery: this.searchQuery || null,
        });
        const data = response.data.data;
        if (type === "pending") {
          this.pendingItems = data.items;
          this.totalPending = data.total;
          if (this.totalPending > 0 && this.activeTab !== "pending") this.activeTab = "pending";
        } else if (type === "responded") {
          this.respondedItems = data.items;
          this.totalResponded = data.total;
          if (this.totalPending === 0 && this.totalResponded > 0 && this.activeTab !== "responded")
            this.activeTab = "responded";
        } else if (type === "closed") {
          this.closedItems = data.items;
          this.totalClosed = data.total;
        }
      } catch (error) {
        console.error(`Error loading ${type} tickets:`, error);
        Swal.fire({
          text: `خطا در بارگذاری تیکت‌های ${state}: ${error.message}`,
          icon: "error",
          confirmButtonText: "تأیید",
        });
      } finally {
        this.loading = false;
      }
    },
    async bulkUpdateState() {
      if (!this.bulkState) {
        Swal.fire({
          text: "لطفاً یک وضعیت انتخاب کنید",
          icon: "warning",
          confirmButtonText: "تأیید",
        });
        return;
      }
      this.loading = true;
      try {
        const response = await axios.post("/api/admin/support/bulk-update", {
          ticketIds: this.selectedTickets.map((ticket) => ticket.id),
          state: this.bulkState,
        });
        if (response.data.error === 0) {
          Swal.fire({
            text: "وضعیت تیکت‌ها با موفقیت تغییر کرد",
            icon: "success",
            confirmButtonText: "تأیید",
          });
          this.selectedTickets = [];
          this.bulkState = null;
          await this.refreshAllData();
        } else {
          throw new Error(response.data.message || "خطای ناشناخته");
        }
      } catch (error) {
        Swal.fire({
          text: "خطا در تغییر وضعیت: " + error.message,
          icon: "error",
          confirmButtonText: "تأیید",
        });
      } finally {
        this.loading = false;
      }
    },
    async refreshAllData() {
      await this.loadPendingData({ page: this.pagePending, itemsPerPage: this.itemsPerPagePending });
      await this.loadRespondedData({ page: this.pageResponded, itemsPerPage: this.itemsPerPageResponded });
      await this.loadClosedData({ page: this.pageClosed, itemsPerPage: this.itemsPerPageClosed });
    },
  },
  mounted() {
    this.refreshAllData();
  },
});
</script>

<style scoped>
.v-tabs {
  width: 100%;
}

.v-tab {
  flex: 1;
}
</style>