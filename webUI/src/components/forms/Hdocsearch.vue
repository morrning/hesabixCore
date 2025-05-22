<template>
  <div>
    <v-menu v-model="menu" :close-on-content-click="false">
      <template v-slot:activator="{ props }">
        <v-text-field
          v-bind="props"
          v-model="displayValue"
          variant="outlined"
          :error-messages="errorMessages"
          :rules="combinedRules"
          :label="label"
          class="my-0"
          prepend-inner-icon="mdi-file-document"
          clearable
          @click:clear="clearSelection"
          :loading="loading"
          @keydown.enter="handleEnter"
          hide-details
          density="compact"
          style="font-size: 0.7rem;"
        >
          <template v-slot:append-inner>
            <v-icon>{{ menu ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
          </template>
        </v-text-field>
      </template>

      <v-card min-width="300" max-width="500" class="search-card">
        <v-card-text class="pa-2">
          <template v-if="!loading">
            <v-list density="compact" class="list-container">
              <template v-if="filteredItems.length > 0">
                <v-list-item
                  v-for="item in filteredItems"
                  :key="item.code"
                  @click="selectItem(item)"
                  class="search-item"
                >
                  <div class="d-flex flex-column w-100">
                    <div class="d-flex justify-space-between align-center mb-1">
                      <span class="text-grey-darken-1 text-caption">{{ item.code }}</span>
                      <span class="text-warning text-caption">{{ item.date }}</span>
                    </div>
                    <div class="text-subtitle-1 font-weight-medium mb-1">{{ item.des }}</div>
                    <div class="d-flex justify-space-between align-center">
                      <span class="text-success text-caption">مبلغ: {{ formatPrice(item.amount) }} ریال</span>
                      <span class="text-primary text-caption">وضعیت: {{ item.status }}</span>
                    </div>
                    <div v-if="item.personName || item.personNikename" class="text-caption text-grey-darken-1 mt-1">
                      مشتری: {{ item.personName }} {{ item.personNikename ? `(${item.personNikename})` : '' }}
                    </div>
                  </div>
                </v-list-item>
              </template>
              <template v-else>
                <v-list-item>
                  <v-list-item-title class="text-center text-grey">
                    نتیجه‌ای یافت نشد
                  </v-list-item-title>
                </v-list-item>
              </template>
            </v-list>
          </template>
          <v-progress-circular
            v-else
            indeterminate
            color="primary"
            class="d-flex mx-auto my-4"
          ></v-progress-circular>
        </v-card-text>
      </v-card>
    </v-menu>

    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="3000"
    >
      {{ snackbar.text }}
      <template v-slot:actions>
        <v-btn
          color="white"
          variant="text"
          @click="snackbar.show = false"
        >
          بستن
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import axios from 'axios'

interface SearchItem {
  id: number;
  code: string;
  date: string;
  dateSubmit: string;
  type: string;
  des: string;
  amount: number;
  submitter: string;
  status: string;
  personName?: string;
  personNikename?: string;
  person?: {
    id: number;
    name: string;
    nikename: string;
  };
  label?: {
    code: string;
    label: string;
  };
}

export default defineComponent({
  name: 'Hdocsearch',
  props: {
    modelValue: {
      type: [Object, String],
      default: null
    },
    label: {
      type: String,
      default: 'جستجوی فاکتور'
    },
    docType: {
      type: String,
      required: true,
      validator: (value: string) => ['invoice', 'receipt', 'order', 'sell'].includes(value)
    },
    returnObject: {
      type: Boolean,
      default: false
    },
    rules: {
      type: Array as () => any[],
      default: () => []
    }
  },
  data() {
    return {
      selectedItem: null as SearchItem | null,
      items: [] as SearchItem[],
      loading: false,
      menu: false,
      searchQuery: '',
      totalItems: 0,
      currentPage: 1,
      itemsPerPage: 10,
      searchTimeout: null as number | null,
      snackbar: {
        show: false,
        text: '',
        color: 'success'
      },
      errorMessages: [] as string[]
    }
  },
  computed: {
    filteredItems() {
      return Array.isArray(this.items) ? this.items : []
    },
    displayValue: {
      get() {
        if (this.menu) {
          return this.searchQuery
        }
        if (this.selectedItem) {
          return `${this.selectedItem.code} - ${this.formatPrice(this.selectedItem.amount)} ریال`
        }
        return this.searchQuery
      },
      set(value: string) {
        this.searchQuery = value
        if (!value) {
          this.clearSelection()
        } else if (value !== this.selectedItem?.des) {
          this.menu = true
        }
      }
    },
    combinedRules() {
      return [
        (v: any) => !!v || 'انتخاب فاکتور الزامی است',
        ...this.rules
      ]
    }
  },
  watch: {
    modelValue: {
      handler(newVal: SearchItem | string | null) {
        if (newVal) {
          if (this.returnObject) {
            this.selectedItem = newVal as SearchItem
          } else {
            const code = typeof newVal === 'string' ? newVal : (newVal as SearchItem).code
            this.selectedItem = this.items.find(item => item.code === code) || null
            if (!this.selectedItem) {
              this.fetchSingleDocument(code)
            }
          }
        } else {
          this.selectedItem = null
        }
      },
      immediate: true
    },
    searchQuery: {
      handler(newVal: string) {
        this.currentPage = 1
        if (this.searchTimeout) {
          clearTimeout(this.searchTimeout)
        }
        this.searchTimeout = setTimeout(() => {
          this.fetchData()
          if (newVal && newVal !== this.selectedItem?.des) {
            this.menu = true
          }
        }, 500)
      }
    },
    menu: {
      handler(newVal: boolean) {
        if (!newVal) {
          this.searchQuery = this.selectedItem?.des || ''
        }
      }
    }
  },
  methods: {
    showMessage(text: string, color = 'success') {
      this.snackbar.text = text
      this.snackbar.color = color
      this.snackbar.show = true
    },
    async fetchData() {
      this.loading = true
      try {
        const response = await axios.post('/api/componenets/doc/search', {
          page: this.currentPage,
          itemsPerPage: this.itemsPerPage,
          search: this.searchQuery,
          docType: this.docType,
          sortBy: null
        })

        if (response.data && Array.isArray(response.data)) {
          this.items = response.data
          this.totalItems = response.data.length
        } else if (response.data && response.data.items) {
          this.items = response.data.items
          this.totalItems = response.data.total || response.data.items.length
        } else {
          this.items = []
          this.totalItems = 0
        }

        if (this.modelValue) {
          if (this.returnObject) {
            this.selectedItem = this.modelValue
          } else {
            this.selectedItem = this.items.find(item => item.code === this.modelValue)
            if (!this.selectedItem) {
              await this.fetchSingleDocument(this.modelValue)
            }
          }
        }
      } catch (error) {
        this.showMessage('خطا در بارگذاری داده‌ها', 'error')
        this.items = []
        this.totalItems = 0
      } finally {
        this.loading = false
      }
    },
    async fetchSingleDocument(code: string) {
      try {
        const response = await axios.get(`/api/componenets/doc/get/${code}`)
        if (response.data && response.data.code) {
          this.items.push(response.data)
          this.selectedItem = response.data
          this.searchQuery = response.data.des
          this.$emit('update:modelValue', this.returnObject ? response.data : response.data.code)
        }
      } catch (error) {
        this.showMessage('خطا در بارگذاری فاکتور', 'error')
      }
    },
    selectItem(item: SearchItem) {
      this.selectedItem = item
      this.searchQuery = item.des
      this.$emit('update:modelValue', this.returnObject ? item : item.code)
      this.menu = false
      this.errorMessages = []
    },
    clearSelection() {
      this.selectedItem = null
      this.searchQuery = ''
      this.$emit('update:modelValue', null)
      this.errorMessages = ['انتخاب فاکتور الزامی است']
      this.menu = false
    },
    handleEnter() {
      if (!this.loading && this.filteredItems.length === 0) {
        this.showMessage('نتیجه‌ای یافت نشد', 'warning')
      }
    },
    formatPrice(price: number): string {
      return new Intl.NumberFormat('fa-IR').format(price || 0)
    }
  },
  created() {
    this.fetchData()
  }
})
</script>

<style scoped>
.list-container {
  max-height: 400px;
  overflow-y: auto;
}

.search-card {
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.search-item {
  border-radius: 4px;
  transition: all 0.2s ease;
  border: 1px solid transparent;
  padding: 0px 0px;
  margin-bottom: 4px;
}

.search-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.05);
  border-color: rgba(var(--v-theme-primary), 0.1);
}
</style>
