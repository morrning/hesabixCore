<script lang="ts" setup>
import { defineComponent, ref, watch } from 'vue'
import axios from "axios";
import Swal from "sweetalert2";
import type { Header, Item, ServerOptions } from "vue3-easy-data-table";


const headers: Header[] = [
  { text: "نام", value: "name" },
  { text: "مالک", value: "owner" },
  { text: "موبایل", value: "ownerMobile" },
  { text: "تاریخ ایجاد", value: "dateRegister" },
  { text: "کالا و خدمات", value: "commodityCount" },
  { text: "اشخاص", value: "personsCount" },
  { text: "اسناد حسابداری", value: "hesabdariDocsCount" },
  { text: "اسناد انبار", value: "StoreroomDocsCount" },
];

const items = ref<Item[]>([]);

const loading = ref(false);
const serverItemsLength = ref(0);
const searchValue = ref('');
const serverOptions = ref<ServerOptions>({
  page: 1,
  rowsPerPage: 25,
  sortBy: 'id',
  sortType: 'desc',
});

const loadFromServer = async () => {
  loading.value = true;
  axios.post('/api/admin/business/count')
    .then((response) => {
      serverItemsLength.value = response.data;
    });
  //load items
  await axios.post('/api/admin/business/search', {
    options: serverOptions.value,
    search: searchValue.value
  })
    .then((response) => {
      items.value = response.data.data;
      loading.value = false;
    });
};

// initial load
loadFromServer();

watch(serverOptions, (value) => { loadFromServer(); }, { deep: true });
watch(searchValue, (value) => { loadFromServer(); }, { deep: true });
</script>

<template>
  <v-toolbar color="toolbar" :title="$t('user.businesses') + ' : (' + serverItemsLength + ')'">
    <v-spacer></v-spacer>
  </v-toolbar>
  <v-container class="pa-0 ma-0">
    <v-card :loading="loading ? 'red' : null" :disabled="loading">
      <v-card-text class="pa-0">
        <v-row>
          <v-col>
            <v-text-field color="info" hide-details="auto" rounded="0" variant="outlined"
            density="compact" :placeholder="$t('dialog.search_txt')" v-model="searchValue" type="text" clearable>
              <template v-slot:prepend-inner>
                <v-tooltip location="bottom" :text="$t('dialog.search')">
                  <template v-slot:activator="{ props }">
                    <v-icon v-bind="props" color="danger" icon="mdi-magnify"></v-icon>
                  </template>
                </v-tooltip>
              </template>
            </v-text-field>
            <EasyDataTable table-class-name="customize-table" show-index alternating :headers="headers" :items="items"
              rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
              rowsOfPageSeparatorMessage="از" theme-color="#1d90ff" header-text-direction="center"
              body-text-direction="center" :loading="loading" v-model:server-options="serverOptions"
              :server-items-length="serverItemsLength">
              <template #pagination="{ prevPage, nextPage, isFirstPage, isLastPage }">
                <v-btn size="small" color="success" class="me-1" :disabled="isFirstPage" @click="prevPage" prepend-icon="mdi-skip-next">
                  {{ $t('dialog.prev_page') }}
                </v-btn>
                <v-btn size="small" color="success" class="me-1" :disabled="isLastPage" @click="nextPage" append-icon="mdi-skip-previous">
                  {{ $t('dialog.next_page') }}
                </v-btn>
              </template>
            </EasyDataTable>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<style scoped></style>