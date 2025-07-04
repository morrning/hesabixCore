<template>
  <div class="sticky-container">
    <v-toolbar color="toolbar" :title="$t('dialog.person_with_det_report')">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.back')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
          </template>
        </v-tooltip>
      </template>
    </v-toolbar>
    <v-container fluid>
      <v-row>
        <v-col cols="12" md="4">
          <Hpersonsearch
            v-model="selectedPerson"
            label="شخص"
            :rules="[v => !!v || 'انتخاب شخص الزامی است']"
          />
        </v-col>
        <v-col cols="12" md="4">
          <Hdatepicker
            v-model="startDate"
            label="تاریخ شروع"
            :rules="[v => !!v || 'تاریخ شروع الزامی است']"
          />
        </v-col>
        <v-col cols="12" md="4">
          <Hdatepicker
            v-model="endDate"
            label="تاریخ پایان"
            :rules="[v => !!v || 'تاریخ پایان الزامی است']"
          />
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" md="12">
          <v-data-table-server
            v-model:items-per-page="serverOptions.rowsPerPage"
            v-model:page="serverOptions.page"
            :headers="tableHeaders"
            :items="items"
            :items-length="totalItems"
            :loading="loading"
            class="elevation-1"
            :items-per-page-options="[5, 10, 20, 50]"
            item-value="id"
            no-data-text="اطلاعاتی برای نمایش وجود ندارد"
            :header-props="{ class: 'custom-header' }"
          >
            <template v-slot:item.index="{ index }">
              {{ (serverOptions.page - 1) * serverOptions.rowsPerPage + index + 1 }}
            </template>
            <template v-slot:item.actions="{ item }">
              <v-btn icon size="small" color="primary">
                <v-icon>mdi-eye</v-icon>
              </v-btn>
            </template>
            <template v-slot:item.docType="{ item }">
              {{ item.docType || '-' }}
            </template>
            <template v-slot:item.debit="{ item }">
              {{ formatNumber(item.debit) }}
            </template>
            <template v-slot:item.credit="{ item }">
              {{ formatNumber(item.credit) }}
            </template>
            <template v-slot:item.description="{ item }">
              {{ item.description }}
            </template>
          </v-data-table-server>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
import Hpersonsearch from '@/components/forms/Hpersonsearch.vue';
import Hdatepicker from '@/components/forms/Hdatepicker.vue';
import axios from 'axios';

export default {
  name: 'PersonWithDetReport',
  components: {
    Hpersonsearch,
    Hdatepicker
  },
  data() {
    return {
      selectedPerson: null,
      startDate: '',
      endDate: '',
      loading: false,
      items: [],
      totalItems: 0,
      serverOptions: {
        page: 1,
        rowsPerPage: 10,
        sortBy: [],
      },
      tableHeaders: [
        { text: 'ردیف', value: 'index', align: 'center', sortable: false },
        { text: 'عملیات', value: 'actions', align: 'center', sortable: false },
        { text: 'نوع سند', value: 'docType', align: 'center' },
        { text: 'بدهکار', value: 'debit', align: 'center' },
        { text: 'بستانکار', value: 'credit', align: 'center' },
        { text: 'شرح', value: 'description', align: 'center' },
      ],
    };
  },
  methods: {
    formatNumber(num) {
      if (!num) return '0';
      return Number(num).toLocaleString('fa-IR');
    },
    async fetchData() {
      if (!this.selectedPerson) {
        this.items = [];
        this.totalItems = 0;
        return;
      }
      this.loading = true;
      try {
        const response = await axios.post('/api/persons/listwithdet', {
          person: this.selectedPerson.code,
          startDate: this.startDate,
          endDate: this.endDate,
          page: this.serverOptions.page,
          itemsPerPage: this.serverOptions.rowsPerPage,
        });
        this.items = response.data.items || [];
        this.totalItems = response.data.total || this.items.length;
      } catch (error) {
        this.items = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
  },
  watch: {
    selectedPerson: 'fetchData',
    startDate: 'fetchData',
    endDate: 'fetchData',
    serverOptions: {
      handler: 'fetchData',
      deep: true,
    },
  },
  mounted() {
    // بارگذاری اولیه اگر شخص انتخاب شده باشد
    if (this.selectedPerson) {
      this.fetchData();
    }
  },
};
</script>

<style scoped>
.sticky-container {
  position: relative;
}
</style>