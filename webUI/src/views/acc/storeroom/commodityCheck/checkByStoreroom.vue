<script lang="ts">
import { defineComponent, ref, watch, onMounted } from 'vue'
import axios from "axios";
import { getApiUrl } from '@/hesabixConfig';

export default defineComponent({
  name: "checkByStoreroom",
  setup() {
    const storerooms = ref([])
    const storeroom = ref(null)
    const searchValue = ref('')
    const loading = ref(true)
    const items = ref([])
    const orgItems = ref([])
    const showZeroTransactions = ref(true)
    const headers = [
      { title: "کد", key: "commodity.code" },
      { title: "دسته بندی", key: "commodity.cat.name", sortable: true },
      { title: "نام", key: "commodity.name", sortable: true },
      { title: "واحد", key: "commodity.unit.name", sortable: true },
      { title: "ورودی", key: "input", sortable: true },
      { title: "خروجی", key: "output", sortable: true },
      { title: "موجودی انبار", key: "existCount" },
      { title: "نقطه سفارش", key: "commodity.orderPoint" },
      { title: "وضعیت", key: "operation" },
    ]

    const loadData = async () => {
      loading.value = true
      try {
        const response = await axios.post('/api/storeroom/list')
        storerooms.value = response.data.data.map((item: any) => ({
          ...item,
          name: `${item.name} انباردار : ${item.manager}`
        }))
      } finally {
        loading.value = false
      }
    }

    const loadStoreItems = async () => {
      if (!storeroom.value) return
      loading.value = true
      try {
        const response = await axios.post('/api/storeroom/commodity/list/' + storeroom.value)
        orgItems.value = response.data
        filterItems()
      } finally {
        loading.value = false
      }
    }

    const filterItems = () => {
      let filteredItems = [...orgItems.value]
      
      if (!showZeroTransactions.value) {
        filteredItems = filteredItems.filter((item: any) => {
          return item.input !== 0 || item.output !== 0 || item.existCount !== 0
        })
      }

      if (searchValue.value) {
        filteredItems = filteredItems.filter((item: any) => {
          const commodity = item?.commodity ?? {}
          const cat = commodity?.cat ?? {}
          const unit = commodity?.unit ?? {}
          
          return (
            (commodity.name || '').includes(searchValue.value) ||
            (cat.name || '').includes(searchValue.value) ||
            (unit.name || '').includes(searchValue.value)
          )
        })
      }

      items.value = filteredItems
    }

    const print = () => {
      if (!storeroom.value) {
        alert('لطفا ابتدا انبار را انتخاب کنید')
        return
      }
      
      loading.value = true
      axios.post('/api/storeroom/exist/print', {
        storeroom: storeroom.value,
        items: items.value,
      }).then((res) => {
        if (res.data.id) {
          window.open(getApiUrl() + `/front/print/${res.data.id}`, '_blank')
        }
      }).catch((error) => {
        console.error('خطا در چاپ:', error)
        alert('خطا در چاپ گزارش')
      }).finally(() => {
        loading.value = false
      })
    }

    watch(searchValue, filterItems)
    watch(showZeroTransactions, filterItems)
    onMounted(loadData)

    return {
      storerooms,
      storeroom,
      searchValue,
      loading,
      items,
      headers,
      loadStoreItems,
      showZeroTransactions,
      print
    }
  }
})
</script>

<template>
  <v-toolbar color="toolbar" title="موجودی کالا">
    <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.back')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
          </template>
        </v-tooltip>
      </template>
    <v-spacer></v-spacer>
    <v-tooltip text="نمایش/عدم نمایش کالاهای بدون تراکنش" location="bottom">
      <template v-slot:activator="{ props }">
        <v-switch
          v-bind="props"
          v-model="showZeroTransactions"
          class="mx-4"
          hide-details
          color="primary"
        ></v-switch>
      </template>
    </v-tooltip>
    <v-tooltip :text="$t('dialog.print')" location="bottom">
      <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon @click="print()" color="primary">
          <v-icon>mdi-printer</v-icon>
        </v-btn>
      </template>
    </v-tooltip>
  </v-toolbar>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <v-select
              v-model="storeroom"
              :items="storerooms"
              item-title="name"
              item-value="id"
              label="انبار"
              hint="برای مشاهده موجودی ابتدا انبار را انتخاب نمایید."
              persistent-hint
              @update:model-value="loadStoreItems"
              clearable
            ></v-select>
          </v-col>

          <v-col cols="12" class="my-0 py-0">
            <v-text-field
              v-model="searchValue"
              prepend-inner-icon="mdi-magnify"
              label="جستجو"
              variant="outlined"
              density="compact"
              hide-details
            ></v-text-field>
          </v-col>

          <v-col cols="12">
            <v-data-table
              :headers="headers"
              :items="items"
              :loading="loading"
              :search="searchValue"
              multi-sort
              density="comfortable"
              class="elevation-1"
              :header-props="{ class: 'custom-header' }"
            >
              <template v-slot:item.existCount="{ item }">
                <b>{{ Math.abs(item.existCount) }}</b>
                <span v-if="parseInt(item.existCount) < 0" class="text-error"> (منفی) </span>
              </template>

              <template v-slot:item.operation="{ item }">
                <span v-if="parseInt(item.existCount) < parseInt(item.commodity.orderPoint)" class="text-error">
                  نیاز به شارژ انبار
                </span>
              </template>
        </v-data-table>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>
.text-error {
  color: rgb(var(--v-theme-error));
}
</style>