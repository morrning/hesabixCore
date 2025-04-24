<template>
    <v-menu
      v-model="menu"
      :close-on-content-click="false"
      location="bottom"
      width="400"
    >
      <template v-slot:activator="{ props }">
        <v-text-field
          v-bind="props"
          :model-value="selectedAccountName"
          :label="label"
          :rules="rules"
          readonly
          hide-details
          density="compact"
          @click="openMenu"
        >
          <template v-slot:append-inner>
            <v-icon>{{ menu ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
          </template>
        </v-text-field>
      </template>
  
      <v-card>
        <v-progress-linear v-if="isLoading" indeterminate color="primary" />
        <v-card-text v-if="accountData.length > 0" class="pa-0">
          <div class="tree-container">
            <div
              v-for="item in accountData"
              :key="item.id"
              class="tree-node"
              :class="{ 'has-children': item.children && item.children.length > 0 }"
            >
              <div class="tree-node-content" @click="handleNodeClick(item)">
                <div class="tree-node-toggle" @click.stop="toggleNode(item)">
                  <v-icon v-if="item.children && item.children.length > 0">
                    {{ item.isOpen ? 'mdi-chevron-down' : 'mdi-chevron-right' }}
                  </v-icon>
                </div>
                <div class="tree-node-icon">
                  <v-icon v-if="item.children && item.children.length > 0">mdi-folder</v-icon>
                  <v-icon v-else>mdi-file-document</v-icon>
                </div>
                <div class="tree-node-label" :class="{ 'selected': selectedAccount?.id === item.id }">
                  {{ item.name }}
                </div>
              </div>
              <div v-if="item.isOpen && item.children && item.children.length > 0" class="tree-children">
                <div
                  v-for="child in item.children"
                  :key="child.id"
                  class="tree-node"
                  :class="{ 'has-children': child.children && child.children.length > 0 }"
                >
                  <div class="tree-node-content" @click="handleNodeClick(child)">
                    <div class="tree-node-toggle" @click.stop="toggleNode(child)">
                      <v-icon v-if="child.children && child.children.length > 0">
                        {{ child.isOpen ? 'mdi-chevron-down' : 'mdi-chevron-right' }}
                      </v-icon>
                    </div>
                    <div class="tree-node-icon">
                      <v-icon v-if="child.children && child.children.length > 0">mdi-folder</v-icon>
                      <v-icon v-else>mdi-file-document</v-icon>
                    </div>
                    <div class="tree-node-label" :class="{ 'selected': selectedAccount?.id === child.id }">
                      {{ child.name }}
                    </div>
                  </div>
                  <div v-if="child.isOpen && child.children && child.children.length > 0" class="tree-children">
                    <div
                      v-for="grandChild in child.children"
                      :key="grandChild.id"
                      class="tree-node"
                    >
                      <div class="tree-node-content" @click="handleNodeClick(grandChild)">
                        <div class="tree-node-icon">
                          <v-icon>mdi-file-document</v-icon>
                        </div>
                        <div class="tree-node-label" :class="{ 'selected': selectedAccount?.id === grandChild.id }">
                          {{ grandChild.name }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </v-card-text>
        <v-card-text v-else>
          در حال بارگذاری...
        </v-card-text>
      </v-card>
    </v-menu>
  </template>
  
  <script setup lang="ts">
  import { ref, onMounted, computed, watch } from 'vue';
  import axios from 'axios';
  import { debounce } from 'lodash'; // برای دیبانس کردن loadChildren
  
  const props = defineProps({
    modelValue: {
      type: Number,
      default: null
    },
    label: {
      type: String,
      default: 'حساب'
    },
    rules: {
      type: Array,
      default: () => []
    }
  });
  
  const emit = defineEmits(['update:modelValue', 'select']);
  
  const menu = ref(false);
  const accountData = ref([]);
  const selectedAccount = ref(null);
  const cache = ref(new Map());
  const isLoading = ref(false);
  
  const selectedAccountName = computed(() => {
    return selectedAccount.value?.name || '';
  });
  
  // تابع برای رمزگشایی کاراکترهای یونیکد
  const decodeUnicode = (str: string): string => {
    try {
      return decodeURIComponent(
        str.replace(/\\u([\dA-F]{4})/gi, (match, grp) =>
          String.fromCharCode(parseInt(grp, 16))
        )
      );
    } catch (e) {
      console.error('خطا در رمزگشایی یونیکد:', e);
      return str;
    }
  };
  
  // پردازش داده‌ها برای رمزگشایی نام‌ها
  const processTreeData = (items: any[]): any[] => {
    return items.map(item => {
      if (cache.value.has(`processed-${item.id}`)) {
        return cache.value.get(`processed-${item.id}`);
      }
      const processedItem = {
        ...item,
        name: decodeUnicode(item.name),
        children: item.children ? processTreeData(item.children) : [],
        isOpen: false
      };
      cache.value.set(`processed-${item.id}`, processedItem);
      return processedItem;
    });
  };
  
  // بارگذاری تنبل زیرشاخه‌ها با دیبانس
  const loadChildren = debounce(async (node: any) => {
    if (cache.value.has(node.id)) {
      node.children = cache.value.get(node.id);
      return;
    }
    try {
      const response = await axios.get(`/api/hesabdari/tables/${node.id}/children`);
      if (response.data.Success) {
        const children = processTreeData(response.data.data || []);
        node.children = children;
        cache.value.set(node.id, children);
      }
    } catch (error) {
      console.error(`خطا در بارگذاری زیرشاخه‌های گره ${node.id}:`, error);
    }
  }, 300);
  
  const toggleNode = (node: any) => {
    if (node.children && node.children.length > 0) {
      node.isOpen = !node.isOpen;
      if (node.isOpen && (!node.children || node.children.length === 0)) {
        loadChildren(node);
      }
    }
  };
  
  // مدیریت انتخاب آیتم‌ها
  const handleNodeClick = (node: any) => {
    selectedAccount.value = node;
    emit('update:modelValue', node.id);
    emit('select', node);
    menu.value = false;
  };
  
  // باز کردن منو
  const openMenu = () => {
    menu.value = true;
    if (!accountData.value.length && !isLoading.value) {
      fetchHesabdariTables();
    }
  };
  
  // بارگذاری اولیه گره‌های ریشه
  const fetchHesabdariTables = async () => {
    if (cache.value.has('root')) {
      accountData.value = cache.value.get('root');
      return;
    }
    isLoading.value = true;
    try {
      const response = await axios.get('/api/hesabdari/tables');
      if (response.data.Success && response.data.data) {
        accountData.value = processTreeData(response.data.data[0].children || []);
        cache.value.set('root', accountData.value);
      }
    } catch (error) {
      console.error('خطا در بارگذاری حساب‌ها:', error);
    } finally {
      isLoading.value = false;
    }
  };
  
  // دیباگ تعداد مونت‌ها
  onMounted(() => {
    fetchHesabdariTables();
  });
  
  // بررسی تغییرات در vue-router
  watch(
    () => props.modelValue,
    () => {
      console.log('modelValue تغییر کرد، احتمالاً به دلیل ناوبری');
    }
  );
  </script>
  
  <style scoped>
  .tree-container {
    max-height: 300px;
    overflow-y: auto;
    padding: 8px;
  }
  
  .tree-node {
    margin-left: 24px;
  }
  
  .tree-node-content {
    display: flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  
  .tree-node-content:hover {
    background-color: rgba(var(--v-theme-primary), 0.1);
  }
  
  .tree-node-toggle {
    width: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .tree-node-icon {
    width: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 8px;
  }
  
  .tree-node-label {
    flex: 1;
    font-size: 0.9rem;
    font-family: 'Vazir', sans-serif;
  }
  
  .tree-node-label.selected {
    color: rgb(var(--v-theme-primary));
    font-weight: 500;
  }
  
  .tree-children {
    margin-left: 24px;
    border-right: 2px solid rgba(var(--v-theme-primary), 0.1);
    padding-right: 8px;
  }
  
  :deep(.v-menu__content) {
    position: fixed !important;
    z-index: 9999 !important;
    transform-origin: center top !important;
  }
  
  :deep(.v-overlay__content) {
    position: fixed !important;
  }
  </style>