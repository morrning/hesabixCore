<template>
  <div>
    <v-toolbar color="toolbar" title="سند حسابداری">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.back')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
              icon="mdi-arrow-right" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-toolbar-items>
        <archive-upload v-if="item.doc.id != 0" :docid="item.doc.id" doctype="accounting" cat="accounting" />
        <notes :stat="notes" :code="$route.params.id" type-note="accounting" />
        <document-log-button :doc-code="$route.params.id" />
        <v-btn icon color="primary" class="ml-2" @click="print">
          <v-icon>mdi-printer</v-icon>
          <v-tooltip activator="parent" location="bottom">چاپ سند</v-tooltip>
        </v-btn>
      </v-toolbar-items>
    </v-toolbar>

    <div id="dm-print" class="pa-4">
      <v-row>
        <v-col cols="12" md="4">
          <v-skeleton-loader v-if="isLoading" type="text" />
          <v-text-field v-else v-model="item.doc.code" label="شماره سند" readonly variant="outlined"></v-text-field>
        </v-col>
        <v-col cols="12" md="4">
          <v-skeleton-loader v-if="isLoading" type="text" />
          <v-text-field v-else v-model="item.doc.date" label="تاریخ" readonly variant="outlined"></v-text-field>
        </v-col>
        <v-col cols="12" md="4">
          <v-skeleton-loader v-if="isLoading" type="text" />
          <v-text-field v-else v-model="item.doc.amount" label="تراز تجمیعی(بستانکار / بدهکار)" readonly
            variant="outlined"></v-text-field>
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="12">
          <v-skeleton-loader v-if="isLoading" type="text" />
          <v-text-field v-else v-model="item.doc.des" label="شرح" readonly variant="outlined"></v-text-field>
        </v-col>
      </v-row>

      <v-data-table
        :headers="headers"
        :items="item.rows"
        :loading="isLoading"
        class="mt-4"
        :header-props="{ class: 'custom-header' }"
        hide-default-footer
        :items-per-page="-1"
      >
        <template v-slot:item="{ item, index }">
          <tr>
            <td class="text-center">{{ index + 1 }}</td>
            <td>{{ item.table }}</td>
            <td>{{ item.refCode + '-' + item.ref }}</td>
            <td>{{ item.des }}</td>
            <td>{{ proxy.$filters.formatNumber(item.bd) }}</td>
            <td>{{ proxy.$filters.formatNumber(item.bs) }}</td>
          </tr>
        </template>
      </v-data-table>
    </div>
  </div>
</template>

<script setup>
import { ref, onBeforeMount } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import ArchiveUpload from '../component/archive/archiveUpload.vue'
import DocumentLogButton from '../component/documentLogButton.vue'
import Notes from '../component/notes.vue'
import { getCurrentInstance } from 'vue'

const { proxy } = getCurrentInstance()
const route = useRoute()
const notes = ref({ count: 0 })
const isLoading = ref(true)
const item = ref({
  doc: {
    id: 0,
    code: ''
  }
})

const headers = [
  { title: 'ردیف', key: 'index', align: 'center', sortable: false },
  { title: 'حساب', key: 'table', sortable: false },
  { title: 'تفضیل', key: 'refCode', sortable: false },
  { title: 'شرح', key: 'des', sortable: false },
  { title: 'بدهکار', key: 'bd', sortable: false },
  { title: 'بستانکار', key: 'bs', sortable: false }
]

const loadData = async () => {
  try {
    const response = await axios.post('/api/accounting/doc/get', {
      code: route.params.id
    })
    item.value = response.data
    item.value.doc.amount = proxy.$filters.formatNumber(item.value.doc.amount)
    isLoading.value = false
  } catch (error) {
    console.error('Error loading data:', error)
  }
}

const print = () => {
  window.print()
}

onBeforeMount(() => {
  loadData()
})
</script>

<style scoped></style>