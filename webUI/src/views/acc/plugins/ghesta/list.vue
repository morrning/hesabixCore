<template>
  <div class="sticky-container">
    <v-toolbar color="toolbar" :title="$t('drawer.ghesta_invoices')">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.back')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-tooltip :text="$t('dialog.add_new')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon="mdi-plus" color="primary" to="/acc/plugins/ghesta/mod/"></v-btn>
        </template>
      </v-tooltip>
    </v-toolbar>

    <v-text-field
      hide-details
      color="green"
      class="pt-0 rounded-0 mb-0"
      density="compact"
      :placeholder="$t('dialog.search_txt')"
      v-model="search"
      type="text"
      clearable
      @update:model-value="onSearch"
    >
      <template v-slot:prepend-inner>
        <v-tooltip location="bottom" :text="$t('dialog.search')">
          <template v-slot:activator="{ props }">
            <v-icon v-bind="props" color="danger" icon="mdi-magnify"></v-icon>
          </template>
        </v-tooltip>
      </template>
      <template v-slot:append-inner>
        <v-menu :close-on-content-click="false">
          <template v-slot:activator="{ props }">
            <v-icon size="sm" v-bind="props" icon="" color="primary">
              <v-tooltip activator="parent" variant="plain" :text="$t('dialog.filters')" location="bottom" />
              <v-icon icon="mdi-filter"></v-icon>
            </v-icon>
          </template>
          <v-list>
            <v-list-subheader color="primary">
              <v-icon icon="mdi-filter"></v-icon>
              {{ $t('dialog.filters') }}
            </v-list-subheader>
            <v-list-item>
              <v-select
                class="py-2 my-2"
                v-model="dateFilter"
                :items="dateFilterOptions"
                label="فیلتر تاریخ"
                @update:model-value="onSearch"
                density="compact"
              />
            </v-list-item>
            <v-list-item>
              <v-select
                class="py-2 my-2"
                v-model="statusFilter"
                :items="statusFilterOptions"
                label="وضعیت پرداخت"
                @update:model-value="onSearch"
                density="compact"
              />
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-text-field>

    <v-data-table-server
      v-model:items-per-page="serverOptions.rowsPerPage"
      v-model:page="serverOptions.page"
      :headers="headers"
      :items="items"
      :items-length="total"
      :loading="loading"
      :no-data-text="$t('table.no_data')"
      @update:options="updateServerOptions"
      class="elevation-1 data-table-wrapper"
      item-value="id"
      :max-height="tableHeight"
      :header-props="{ class: 'custom-header' }"
      multi-sort
    >
      <!-- ستون ردیف -->
      <template v-slot:item.row="{ index }">
        {{ (serverOptions.page - 1) * serverOptions.rowsPerPage + index + 1 }}
      </template>

      <!-- ستون عملیات -->
      <template v-slot:item.actions="{ item }">
        <v-menu>
          <template v-slot:activator="{ props }">
            <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
          </template>
          <v-list>
            <v-list-item class="text-dark" :title="$t('dialog.view')" @click="onView(item)">
              <template v-slot:prepend>
                <v-icon icon="mdi-eye"></v-icon>
              </template>
            </v-list-item>
            <v-list-item class="text-dark" :title="$t('dialog.edit')" @click="onEdit(item)">
              <template v-slot:prepend>
                <v-icon icon="mdi-file-edit"></v-icon>
              </template>
            </v-list-item>
            <v-list-item class="text-dark" :title="$t('dialog.payment')" @click="onPayment(item)">
              <template v-slot:prepend>
                <v-icon icon="mdi-cash"></v-icon>
              </template>
            </v-list-item>
            <v-list-item class="text-dark" :title="$t('dialog.delete')" @click="onDelete(item)">
              <template v-slot:prepend>
                <v-icon color="deep-orange-accent-4" icon="mdi-trash-can"></v-icon>
              </template>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>

      <!-- ستون شماره فاکتور -->
      <template v-slot:item.code="{ item }">
        {{ item.code || '-' }}
      </template>
      <!-- ستون تاریخ اولین قسط -->
      <template v-slot:item.firstGhestaDate="{ item }">
        {{ item.firstGhestaDate }}
      </template>

      <!-- ستون مبلغ -->
      <template v-slot:item.amount="{ item }">
        <span class="text-dark">
          {{ formatNumber(item.amount) }}
        </span>
      </template>

      <!-- ستون سود -->
      <template v-slot:item.profitAmount="{ item }">
        <span class="text-dark">
          {{ formatNumber(item.profitAmount) }}
        </span>
      </template>

      <!-- ستون درصد سود -->
      <template v-slot:item.profitPercent="{ item }">
        {{ item.profitPercent }}%
      </template>

      <!-- ستون تعداد اقساط -->
      <template v-slot:item.count="{ item }">
        {{ item.count }}
      </template>

      <!-- ستون نوع سود -->
      <template v-slot:item.profitType="{ item }">
        {{ getProfitTypeLabel(item.profitType) }}
      </template>

      <!-- ستون مشتری -->
      <template v-slot:item.person="{ item }">
        <router-link v-if="item.person" :to="'/acc/persons/card/view/' + item.person.id">
          {{ item.person.nikename }}
        </router-link>
        <span v-else>-</span>
      </template>
    </v-data-table-server>
  </div>
</template>

<script>
import { defineComponent, ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'
import debounce from 'lodash/debounce'

export default defineComponent({
  name: 'GhestaList',

  setup() {
    const router = useRouter()
    const loading = ref(false)
    const items = ref([])
    const total = ref(0)
    const search = ref('')
    const dateFilter = ref('all')
    const statusFilter = ref('all')
    const serverOptions = ref({
      page: 1,
      rowsPerPage: 10,
      sortBy: []
    })

    const headers = [
      { title: 'ردیف', key: 'row', align: 'center', sortable: false },
      { title: 'عملیات', key: 'actions', align: 'center', sortable: false },
      { title: 'فاکتور', key: 'code', align: 'center', sortable: true },
      { title: 'اولین قسط', key: 'firstGhestaDate', align: 'center', sortable: true },
      { title: 'مشتری', key: 'person', align: 'center', sortable: true },
      { title: 'مبلغ', key: 'amount', align: 'center', sortable: true },
      { title: 'سود', key: 'profitAmount', align: 'center', sortable: true },
      { title: 'درصد سود', key: 'profitPercent', align: 'center', sortable: true },
      { title: 'تعداد اقساط', key: 'count', align: 'center', sortable: true },
      { title: 'نوع سود', key: 'profitType', align: 'center' }
    ]

    const dateFilterOptions = [
      { title: 'همه', value: 'all' },
      { title: 'امروز', value: 'today' },
      { title: 'هفته جاری', value: 'week' },
      { title: 'ماه جاری', value: 'month' }
    ]

    const statusFilterOptions = [
      { title: 'همه', value: 'all' },
      { title: 'پرداخت شده', value: 'paid' },
      { title: 'پرداخت نشده', value: 'unpaid' },
      { title: 'نیمه پرداخت', value: 'partial' }
    ]

    const tableHeight = computed(() => window.innerHeight - 200)

    const getProfitTypeLabel = (type) => {
      const types = {
        'simple': 'سود ساده',
        'compound': 'سود مرکب',
        'yearly': 'سود سالانه',
        'monthly': 'سود ماهانه'
      }
      return types[type] || type
    }

    const formatDate = (date) => {
      // تبدیل تاریخ به فرمت فارسی
      return new Date(date).toLocaleDateString('fa-IR')
    }

    const formatNumber = (number) => {
      // تبدیل اعداد به فرمت فارسی
      return new Intl.NumberFormat('fa-IR').format(number)
    }

    const loadData = async () => {
      try {
        loading.value = true
        const response = await axios.post('/api/plugins/ghesta/invoices/search', {
          search: search.value,
          page: serverOptions.value.page,
          perPage: serverOptions.value.rowsPerPage,
          dateFilter: dateFilter.value,
          statusFilter: statusFilter.value,
          sortBy: serverOptions.value.sortBy
        })

        if (response.data.result === 1) {
          items.value = response.data.items
          total.value = response.data.total
        } else {
          Swal.fire({
            text: 'خطا در دریافت اطلاعات',
            icon: 'error',
            confirmButtonText: 'قبول'
          })
        }
      } catch (error) {
        console.error('Error loading data:', error)
        Swal.fire({
          text: 'خطا در دریافت اطلاعات: ' + error.message,
          icon: 'error',
          confirmButtonText: 'قبول'
        })
      } finally {
        loading.value = false
      }
    }

    const updateServerOptions = (options) => {
      serverOptions.value = {
        page: options.page,
        rowsPerPage: options.itemsPerPage,
        sortBy: options.sortBy || []
      }
      loadData()
    }

    const onSearch = debounce(() => {
      serverOptions.value.page = 1
      loadData()
    }, 300)

    const onEdit = (item) => {
      router.push(`/acc/plugins/ghesta/mod/${item.id}`)
    }

    const onDelete = (item) => {
      Swal.fire({
        text: 'آیا از حذف این فاکتور اطمینان دارید؟',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: 'خیر',
        icon: 'warning'
      }).then((result) => {
        if (result.isConfirmed) {
          loading.value = true
          axios.delete(`/api/plugins/ghesta/invoice/${item.id}`)
            .then((response) => {
              if (response.data.result === 1) {
                Swal.fire({
                  text: 'فاکتور با موفقیت حذف شد',
                  icon: 'success',
                  confirmButtonText: 'قبول'
                })
                loadData()
              } else {
                Swal.fire({
                  text: 'خطا در حذف فاکتور',
                  icon: 'error',
                  confirmButtonText: 'قبول'
                })
              }
            })
            .catch((error) => {
              console.error('Error:', error)
              Swal.fire({
                text: 'خطا در حذف فاکتور',
                icon: 'error',
                confirmButtonText: 'قبول'
              })
            })
            .finally(() => {
              loading.value = false
            })
        }
      })
    }

    const onView = (item) => {
      router.push(`/acc/plugins/ghesta/view/${item.id}`)
    }

    const onPayment = (item) => {
      router.push(`/acc/plugins/ghesta/payment/${item.id}`)
    }

    onMounted(() => {
      loadData()
    })

    return {
      loading,
      items,
      total,
      search,
      dateFilter,
      statusFilter,
      serverOptions,
      headers,
      dateFilterOptions,
      statusFilterOptions,
      tableHeight,
      getProfitTypeLabel,
      formatDate,
      formatNumber,
      updateServerOptions,
      onSearch,
      onEdit,
      onDelete,
      onView,
      onPayment
    }
  }
})
</script>

<style>
.sticky-container {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.data-table-wrapper {
  flex: 1;
  overflow: auto;
}

.custom-header {
  background-color: #f5f5f5;
  font-weight: bold;
}
</style>