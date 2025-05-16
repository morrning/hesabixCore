<template>
  <div>
    <v-menu location="bottom end" :close-on-content-click="false">
      <template v-slot:activator="{ props }">
        <v-btn class="" stacked v-bind="props">
          <v-icon>mdi-apps</v-icon>
        </v-btn>
      </template>

      <v-card min-width="300" class="shortcuts-menu">
        <v-list>
          <v-list-item>
            <template v-slot:prepend>
              <v-icon color="primary">mdi-apps</v-icon>
            </template>
            <v-list-item-title class="text-h6">دسترسی‌های سریع</v-list-item-title>
            <template v-slot:append>
              <v-tooltip
                location="top"
                text="افزودن دسترسی سریع جدید"
              >
                <template v-slot:activator="{ props }">
                  <v-btn
                    icon
                    variant="text"
                    color="primary"
                    size="small"
                    v-bind="props"
                    @click="showAddDialog = true"
                  >
                    <v-icon>mdi-plus</v-icon>
                  </v-btn>
                </template>
              </v-tooltip>
            </template>
          </v-list-item>

          <v-divider class="my-0"></v-divider>

          <template v-if="!customShortcuts || customShortcuts.length === 0">
            <v-list-item>
              <v-list-item-title class="text-center py-4">
                <v-icon
                  size="32"
                  color="grey-lighten-1"
                  class="mb-2 d-block mx-auto"
                >
                  mdi-star-outline
                </v-icon>
                <span class="text-body-2 text-grey">
                  هیچ دسترسی سریعی تعریف نشده است
                </span>
              </v-list-item-title>
            </v-list-item>
          </template>

          <template v-else>
            <v-list-item
              v-for="(shortcut, index) in customShortcuts"
              :key="index"
              class="shortcut-item"
              @click="navigateTo(shortcut.path)"
            >
              <template v-slot:prepend>
                <v-avatar
                  :color="getRandomColor(index)"
                  size="32"
                  class="shortcut-icon"
                >
                  <v-icon :icon="shortcut.icon" color="white" size="18"></v-icon>
                </v-avatar>
              </template>

              <v-list-item-title class="text-body-2">
                {{ shortcut.name }}
              </v-list-item-title>

              <template v-slot:append>
                <div class="shortcut-actions" :class="{ 'always-show': showActions }">
                  <v-btn
                    v-show="showActions"
                    icon
                    variant="text"
                    size="x-small"
                    color="primary"
                    @click.stop="editShortcut(index)"
                  >
                    <v-icon>mdi-pencil</v-icon>
                  </v-btn>
                  <v-btn
                    v-show="showActions"
                    icon
                    variant="text"
                    size="x-small"
                    color="error"
                    @click.stop="deleteShortcut(index)"
                  >
                    <v-icon>mdi-delete</v-icon>
                  </v-btn>
                </div>
              </template>
            </v-list-item>
          </template>

          <v-divider class="my-0"></v-divider>

          <v-list-item class="edit-mode-item">
            <template v-slot:prepend>
              <v-icon size="small" color="primary">mdi-pencil</v-icon>
            </template>
            <v-list-item-title class="text-caption">حالت ویرایش</v-list-item-title>
            <template v-slot:append>
              <v-tooltip
                location="bottom"
                text="نمایش دکمه‌های ویرایش و حذف"
              >
                <template v-slot:activator="{ props }">
                  <v-switch
                    v-model="showActions"
                    color="primary"
                    hide-details
                    density="compact"
                    class="edit-mode-switch"
                    v-bind="props"
                  ></v-switch>
                </template>
              </v-tooltip>
            </template>
          </v-list-item>
        </v-list>
      </v-card>
    </v-menu>

    <!-- دیالوگ افزودن/ویرایش دسترسی سریع -->
    <v-dialog v-model="showAddDialog" max-width="500">
      <v-card class="shortcut-dialog">
        <v-card-title class="text-h6 pa-4 d-flex align-center">
          <v-icon color="primary" class="me-2">{{ editingIndex === null ? 'mdi-plus' : 'mdi-pencil' }}</v-icon>
          {{ editingIndex === null ? 'افزودن دسترسی سریع' : 'ویرایش دسترسی سریع' }}
        </v-card-title>

        <v-divider></v-divider>

        <v-card-text class="pa-6">
          <v-form @submit.prevent="saveShortcut">
            <v-text-field
              v-model="newShortcut.name"
              label="نام"
              required
              variant="outlined"
              density="comfortable"
              class="mb-4"
              :rules="[v => !!v || 'نام الزامی است']"
              autofocus
            ></v-text-field>

            <v-text-field
              v-model="newShortcut.path"
              label="مسیر"
              required
              hint="برای آدرس داخلی: /acc/dashboard - برای آدرس خارجی: https://example.com"
              persistent-hint
              variant="outlined"
              density="comfortable"
              class="mb-4"
              :rules="[
                v => !!v || 'مسیر الزامی است',
                v => isInternalPath(v) || v.startsWith('http') || 'مسیر باید با / یا http شروع شود'
              ]"
            ></v-text-field>

            <v-select
              v-model="newShortcut.icon"
              :items="availableIcons"
              label="آیکون"
              required
              variant="outlined"
              density="comfortable"
              item-title="title"
              item-value="value"
              return-object
              :rules="[v => !!v || 'آیکون الزامی است']"
            >
              <template v-slot:item="{ props, item }">
                <v-list-item v-bind="props">
                  <template v-slot:prepend>
                    <v-icon :icon="item.raw.value" color="primary"></v-icon>
                  </template>
                </v-list-item>
              </template>
              <template v-slot:selection="{ item }">
                <div class="d-flex align-center">
                  <v-icon :icon="item.raw.value" color="primary" class="me-2"></v-icon>
                  <span>{{ item.raw.title }}</span>
                </div>
              </template>
            </v-select>
          </v-form>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn
            color="error"
            variant="text"
            @click="showAddDialog = false"
            class="px-4"
          >
            انصراف
          </v-btn>
          <v-btn
            color="primary"
            @click="saveShortcut"
            :loading="saving"
            class="px-4"
          >
            {{ editingIndex === null ? 'افزودن' : 'ویرایش' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  name: 'ShortcutsButton',
  data() {
    return {
      showAddDialog: false,
      editingIndex: null,
      customShortcuts: [],
      saving: false,
      showActions: false,
      newShortcut: {
        name: '',
        path: '',
        icon: 'mdi-link'
      },
      availableIcons: [
        { title: 'لینک', value: 'mdi-link' },
        { title: 'داشبورد', value: 'mdi-view-dashboard' },
        { title: 'کاربران', value: 'mdi-account-group' },
        { title: 'گزارش‌ها', value: 'mdi-chart-bar' },
        { title: 'تنظیمات', value: 'mdi-cog' },
        { title: 'فایل', value: 'mdi-file' },
        { title: 'پیام', value: 'mdi-message' },
        { title: 'نوتیفیکیشن', value: 'mdi-bell' },
        { title: 'فروش', value: 'mdi-cart' },
        { title: 'خرید', value: 'mdi-cart-arrow-down' },
        { title: 'انبار', value: 'mdi-warehouse' },
        { title: 'بانک', value: 'mdi-bank' },
        { title: 'صندوق', value: 'mdi-cash-register' },
        { title: 'چک', value: 'mdi-checkbook' },
        { title: 'حسابداری', value: 'mdi-calculator' },
        { title: 'گزارش', value: 'mdi-file-document' },
        { title: 'فاکتور جدید', value: 'mdi-file-plus' },
        { title: 'فاکتور فروش', value: 'mdi-file-document-edit' },
        { title: 'فاکتور خرید', value: 'mdi-file-document-edit-outline' },
        { title: 'شخص جدید', value: 'mdi-account-plus' },
        { title: 'مشتری', value: 'mdi-account' },
        { title: 'تامین کننده', value: 'mdi-account-tie' },
        { title: 'کالا جدید', value: 'mdi-package-variant-plus' },
        { title: 'کالا', value: 'mdi-package-variant' },
        { title: 'دسته‌بندی', value: 'mdi-shape' },
        { title: 'قیمت', value: 'mdi-currency-usd' },
        { title: 'تخفیف', value: 'mdi-tag-multiple' },
        { title: 'مالیات', value: 'mdi-percent' },
        { title: 'پرداخت', value: 'mdi-cash' },
        { title: 'دریافت', value: 'mdi-cash-plus' },
        { title: 'انتقال وجه', value: 'mdi-bank-transfer' },
        { title: 'صورتحساب', value: 'mdi-receipt' },
        { title: 'سند حسابداری', value: 'mdi-book-open-variant' },
        { title: 'دفتر کل', value: 'mdi-book' },
        { title: 'دفتر روزنامه', value: 'mdi-book-open' },
        { title: 'ترازنامه', value: 'mdi-scale-balance' },
        { title: 'سود و زیان', value: 'mdi-chart-line' },
        { title: 'جریان نقدی', value: 'mdi-cash-flow' },
        { title: 'بودجه', value: 'mdi-chart-box' },
        { title: 'پیش‌بینی', value: 'mdi-chart-timeline-variant' },
        { title: 'دوره مالی', value: 'mdi-calendar-clock' },
        { title: 'سال مالی', value: 'mdi-calendar-range' },
        { title: 'بستن دوره', value: 'mdi-calendar-check' },
        { title: 'تنظیمات مالی', value: 'mdi-cog-outline' },
        { title: 'تعریف حساب', value: 'mdi-account-cog' },
        { title: 'تعریف مرکز هزینه', value: 'mdi-account-group-outline' },
        { title: 'تعریف پروژه', value: 'mdi-briefcase-outline' }
      ],
      colors: [
        'primary',
        'secondary',
        'success',
        'info',
        'warning',
        'error'
      ]
    }
  },
  mounted() {
    this.loadShortcuts()
  },
  methods: {
    loadShortcuts() {
      const savedShortcuts = localStorage.getItem('customShortcuts')
      if (savedShortcuts) {
        this.customShortcuts = JSON.parse(savedShortcuts)
      }
    },
    isInternalPath(path) {
      return path.startsWith('/acc/') || path.startsWith('/')
    },
    navigateTo(path) {
      if (!this.showActions) {
        if (this.isInternalPath(path)) {
          this.$router.push(path)
        } else {
          window.open(path, '_blank')
        }
      }
    },
    saveShortcuts() {
      localStorage.setItem('customShortcuts', JSON.stringify(this.customShortcuts))
    },
    getRandomColor(index) {
      return this.colors[index % this.colors.length]
    },
    async saveShortcut() {
      this.saving = true
      try {
        if (!this.newShortcut.path) {
          throw new Error('مسیر نمی‌تواند خالی باشد')
        }
        
        if (!this.isInternalPath(this.newShortcut.path) && !this.newShortcut.path.startsWith('http')) {
          throw new Error('برای آدرس‌های خارجی باید از http یا https استفاده کنید')
        }

        const shortcutToSave = {
          name: this.newShortcut.name,
          path: this.newShortcut.path,
          icon: this.newShortcut.icon.value
        }

        if (this.editingIndex === null) {
          this.customShortcuts.push(shortcutToSave)
        } else {
          this.customShortcuts[this.editingIndex] = shortcutToSave
        }
        this.saveShortcuts()
        this.showAddDialog = false
        this.resetNewShortcut()
      } catch (error) {
        this.$toast.error(error.message)
      } finally {
        this.saving = false
      }
    },
    editShortcut(index) {
      this.editingIndex = index
      this.newShortcut = { ...this.customShortcuts[index] }
      this.showAddDialog = true
      event.preventDefault()
      event.stopPropagation()
    },
    deleteShortcut(index) {
      this.customShortcuts.splice(index, 1)
      this.saveShortcuts()
      event.preventDefault()
      event.stopPropagation()
    },
    resetNewShortcut() {
      this.newShortcut = {
        name: '',
        path: '',
        icon: 'mdi-link'
      }
      this.editingIndex = null
    }
  }
}
</script>

<style scoped>
.shortcuts-menu {
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.shortcut-item {
  transition: all 0.2s ease;
}

.shortcut-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.shortcut-actions {
  opacity: 0;
  transition: opacity 0.2s ease;
}

.shortcut-actions.always-show {
  opacity: 1;
}

.shortcut-item:hover .shortcut-actions:not(.always-show) {
  opacity: 1;
}

.shortcut-icon {
  transition: transform 0.2s ease;
}

.shortcut-item:hover .shortcut-icon {
  transform: scale(1.1);
}

.shortcut-btn {
  position: relative;
}

.shortcut-btn::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 2px;
  background-color: rgb(var(--v-theme-primary));
  transition: width 0.2s ease;
}

.shortcut-btn:hover::after {
  width: 100%;
}

.edit-mode-item {
  min-height: 36px !important;
  padding: 0 16px !important;
}

.edit-mode-switch {
  transform: scale(0.8);
  margin-right: -8px;
}

.text-caption {
  font-size: 0.75rem;
  color: rgba(0, 0, 0, 0.6);
}

.shortcut-dialog {
  border-radius: 12px;
}

.shortcut-dialog .v-card-title {
  font-weight: 600;
}

.shortcut-dialog .v-card-text {
  padding-top: 24px;
  padding-bottom: 24px;
}

.shortcut-dialog .v-btn {
  min-width: 100px;
}
</style> 