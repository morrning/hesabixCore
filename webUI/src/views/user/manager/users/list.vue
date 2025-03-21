<script lang="ts" setup>
import { ref, watch } from 'vue'
import axios from "axios";
import Swal from "sweetalert2";
import ChangePasswordDialog from '@/components/ChangePasswordDialog.vue';

interface User {
  id: number;
  email: string;
  mobile: string;
  fullname: string;
  status: boolean;
  dateRegister: string;
  bidCount: number;
  roles: string[];
  inviteCode: string;
}

const headers = [
  { title: "عملیات", key: "operation", sortable: false },
  { title: "نام", key: "fullname", sortable: true },
  { title: "شماره تلفن", key: "mobile", sortable: true },
  { title: "پست الکترونیکی", key: "email", sortable: true },
  { title: "تاریخ عضویت", key: "dateRegister", sortable: true },
  { title: "تعداد کسب‌و‌کار", key: "bidCount", sortable: true },
  { title: "وضعیت", key: "status", sortable: true },
];

const items = ref<User[]>([]);
const loading = ref(false);
const totalItems = ref(0);
const searchValue = ref('');

const options = ref({
  page: 1,
  itemsPerPage: 25,
  sortBy: [{ key: 'id', order: 'desc' }],
});

const showChangePasswordDialog = ref(false);
const selectedUserId = ref<number>(0);

const loadFromServer = async () => {
  try {
    loading.value = true;
    
    const payload = {
      pagination: {
        page: options.value.page,
        limit: options.value.itemsPerPage,
      },
      sort: {
        sortBy: options.value.sortBy[0]?.key || 'id',
        sortDesc: options.value.sortBy[0]?.order === 'desc',
      },
      filters: {
        search: searchValue.value
      }
    };

    const response = await axios.post('/api/admin/users/search', payload);

    if (response.data?.status === 'success') {
      items.value = response.data.data.items || [];
      totalItems.value = response.data.data.total || 0;
    } else {
      items.value = [];
      totalItems.value = 0;
      throw new Error(response.data?.message || 'خطا در دریافت اطلاعات');
    }
  } catch (error) {
    console.error('Error loading data:', error);
    items.value = [];
    totalItems.value = 0;
    Swal.fire({
      text: error.message || 'خطا در بارگذاری اطلاعات',
      icon: 'error',
      confirmButtonText: 'تایید',
    });
  } finally {
    loading.value = false;
  }
};

watch([options, searchValue], () => {
  loadFromServer();
}, { deep: true });

const openChangePasswordDialog = (userId: number) => {
  selectedUserId.value = userId;
  showChangePasswordDialog.value = true;
};

// اجرای اولیه
loadFromServer();
</script>

<template>
  <v-toolbar color="toolbar" :title="$t('user.users') + ' : (' + totalItems + ')'">
    <v-spacer />
  </v-toolbar>

  <v-container class="pa-0 ma-0">
    <v-card :loading="loading" :disabled="loading">
      <v-card-text class="pa-0">
        <v-row>
          <v-col>
            <v-text-field
              v-model="searchValue"
              color="info"
              hide-details="auto"
              rounded="0"
              variant="outlined"
              density="compact"
              :placeholder="$t('dialog.search_txt')"
              type="text"
              clearable
            >
              <template v-slot:prepend-inner>
                <v-tooltip location="bottom" :text="$t('dialog.search')">
                  <template v-slot:activator="{ props }">
                    <v-icon v-bind="props" color="danger" icon="mdi-magnify" />
                  </template>
                </v-tooltip>
              </template>
            </v-text-field>

            <v-data-table-server
              v-model:options="options"
              :headers="headers"
              :items="items"
              :items-length="totalItems"
              :loading="loading"
              class="elevation-1"
              item-value="id"
              return-object
            >
              <template #item.operation="{ item }">
                <v-menu>
                  <template v-slot:activator="{ props }">
                    <v-btn variant="text" color="error" icon="mdi-menu" v-bind="props" />
                  </template>
                  <v-list>
                    <v-list-item
                      class="text-dark"
                      @click="openChangePasswordDialog(item.id)"
                      :title="$t('dialog.change_password')"
                      prepend-icon="mdi-lock-reset"
                    />
                  </v-list>
                </v-menu>
              </template>

              <template #item.status="{ item }">
                <v-chip
                  :color="item.status ? 'success' : 'error'"
                  :text="item.status ? 'فعال' : 'غیرفعال'"
                  size="small"
                />
              </template>

              <template #bottom>
                <div class="text-center pt-2">
                  <v-pagination
                    v-model="options.page"
                    :length="Math.ceil(totalItems / options.itemsPerPage)"
                    :total-visible="7"
                  />
                </div>
              </template>
            </v-data-table-server>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>

  <ChangePasswordDialog
    :userId="selectedUserId"
    v-model:dialog="showChangePasswordDialog"
    v-if="selectedUserId !== null"
  />
</template>

<style scoped>
.v-data-table {
  width: 100%;
}

:deep(.v-data-table-header th) {
  text-align: center !important;
  white-space: nowrap;
}

:deep(.v-data-table__wrapper table td) {
  text-align: center !important;
}

:deep(.v-data-table__wrapper) {
  overflow-x: auto;
}
</style>