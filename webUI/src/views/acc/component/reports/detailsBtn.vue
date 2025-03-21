<template>
  <div class="text-center pa-4">
    <v-btn
      @click="dialog = true; loadData(1)"
      color="primary"
      size="xs"
      variant="plain"
      icon="mdi-magnify"
    ></v-btn>
    <v-dialog v-model="dialog" fullscreen>
      <v-card :loading="loading">
        <v-toolbar
          class="position-sticky top-0"
          style="z-index: 1000;"
          flat
        >
          <v-btn
            icon
            small
            @click="dialog = false"
            :title="$t('dialog.close')"
          >
            <v-icon>mdi-close</v-icon>
          </v-btn>
          <v-toolbar-title>{{ $t('dialog.details') }}</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn
            icon
            small
            @click="exportToExcel"
            :title="$t('button.export_excel')"
          >
            <v-icon>mdi-file-excel</v-icon>
          </v-btn>
        </v-toolbar>
        <v-card-text class="p-0 m-0" style="max-height: 500px; overflow-y: auto;">
          <EasyDataTable
            table-class-name="customize-table"
            multi-sort
            show-index
            alternating
            :search-value="searchValue"
            :headers="headers"
            :items="items"
            theme-color="#1d90ff"
            header-text-direction="center"
            body-text-direction="center"
            :server-items-length="totalItems"
            v-model:server-options="serverOptions"
            :loading="loading"
            :rows-per-page-message="$t('table.rows_per_page')"
            :empty-message="$t('table.no_data')"
            :rows-of-page-separator-message="$t('table.of')"
          >
            <template #item-bs="{ bs }">
              {{ formatNumber(bs) }}
            </template>
            <template #item-bd="{ bd }">
              {{ formatNumber(bd) }}
            </template>
            <template #item-doc_code="{ doc_code }">
              <v-btn
                color="primary"
                block
                variant="plain"
                :text="formatNumber(doc_code)"
                :to="'/acc/accounting/view/' + doc_code"
              ></v-btn>
            </template>
          </EasyDataTable>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { defineComponent, ref, watch } from 'vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

export default defineComponent({
  name: 'DetailsBtn',
  props: {
    node: {
      type: Number,
      required: true,
    },
    nodeType: {
      type: String,
      required: true,
    },
    isObject: {
      type: Boolean,
      required: true,
    },
    upperId: {
      type: Number,
      default: null,
    },
  },
  setup(props) {
    const { t } = useI18n();
    const loading = ref(false);
    const dialog = ref(false);
    const items = ref([]);
    const searchValue = ref('');
    const totalItems = ref(0);
    const serverOptions = ref({
      page: 1,
      rowsPerPage: 10,
    });

    const headers = [
      { text: t('reports.details.date'), value: 'date' },
      { text: t('reports.details.doc_code'), value: 'doc_code' },
      { text: t('reports.details.description'), value: 'des' },
      { text: t('reports.details.debit'), value: 'bd' },
      { text: t('reports.details.credit'), value: 'bs' },
      { text: t('reports.details.quantity'), value: 'commodity_count' },
    ];

    const loadData = async () => {
      loading.value = true;
      try {
        const response = await axios.post('/api/report/acc/get_details', {
          node: props.node,
          type: props.nodeType,
          isObject: props.isObject,
          upperId: props.upperId,
          page: serverOptions.value.page,
          perPage: serverOptions.value.rowsPerPage,
        });
        items.value = response.data.items || [];
        totalItems.value = response.data.pagination.totalItems || 0;
        serverOptions.value.page = response.data.pagination.currentPage || 1;
        serverOptions.value.rowsPerPage = response.data.pagination.perPage || 10;
        
      } catch (error) {
        console.error('Error loading details:', error.response?.data || error);
      } finally {
        loading.value = false;
      }
    };

    const exportToExcel = async () => {
      loading.value = true;
      try {
        const response = await axios.post(
          '/api/report/acc/export_details_excel',
          {
            node: props.node,
            type: props.nodeType,
            isObject: props.isObject,
            upperId: props.upperId,
          },
          { responseType: 'blob' }
        );
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'details_export.xlsx');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      } catch (error) {
        console.error('Error exporting to Excel:', error);
      } finally {
        loading.value = false;
      }
    };

    const formatNumber = (value) => {
      return value ? Number(value).toLocaleString('fa-IR') : '0';
    };

    // مشاهده تغییرات serverOptions و بارگذاری داده‌ها
    watch(serverOptions, () => {
      loadData();
    }, { deep: true });

    return {
      loading,
      dialog,
      items,
      searchValue,
      totalItems,
      serverOptions,
      headers,
      loadData,
      exportToExcel,
      formatNumber,
    };
  },
});
</script>

<style scoped>
.customize-table {
  font-family: 'Vazir', sans-serif;
}
.position-sticky.top-0 {
  position: sticky;
  top: 0;
  z-index: 1000;
}
</style>