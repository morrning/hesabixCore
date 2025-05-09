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
          <tree-node
            v-for="node in accountData"
            :key="node.code"
            :node="node"
            :selected-code="selectedAccount?.code"
            :selectable-only="selectableOnly"
            @select="handleNodeSelect"
            @toggle="toggleNode"
          />
        </div>
      </v-card-text>
      <v-card-text v-else>
        {{ isLoading ? 'در حال بارگذاری...' : 'هیچ حسابی یافت نشد' }}
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
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import TreeNode from '@/components/forms/TreeNode.vue';

// اضافه کردن کش سراسری
const globalCache = {
  data: null as AccountNode[] | null,
  isLoading: false,
  promise: null as Promise<void> | null,
  lastUpdate: 0,
};

type ValidationRule = (value: any) => boolean | string;

interface AccountNode {
  code: string;
  name: string;
  children?: AccountNode[];
  isOpen?: boolean;
  tableType?: string;
  type?: string;
}

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    default: 'حساب',
  },
  rules: {
    type: Array as () => ValidationRule[],
    default: () => [],
  },
  initialAccount: {
    type: Object,
    default: () => ({ code: '', name: '' }),
  },
  showSubTree: {
    type: Boolean,
    default: false,
  },
  selectableOnly: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue', 'select', 'tableType', 'accountSelected']);

const menu = ref(false);
const accountData = ref<AccountNode[]>([]);
const selectedAccount = ref<AccountNode | null>(null);
const cache = ref(new Map<string, AccountNode[]>());
const isLoading = ref(false);
const snackbar = ref({
  show: false,
  text: '',
  color: 'success',
});

const selectedAccountName = computed(() => {
  return selectedAccount.value?.name || '';
});

// نمایش پیام خطا یا موفقیت
const showMessage = (text: string, color = 'success') => {
  snackbar.value.text = text;
  snackbar.value.color = color;
  snackbar.value.show = true;
};

// رمزگشایی کاراکترهای یونیکد
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

// پردازش داده‌ها
const processTreeData = (items: any[]): any[] => {
  return items.map((item) => {
    if (cache.value.has(`processed-${item.code}`)) {
      return cache.value.get(`processed-${item.code}`);
    }
    const processedItem = {
      ...item,
      name: decodeUnicode(item.name),
      children: item.children ? processTreeData(item.children) : [],
      isOpen: false,
      tableType: item.type,
    };
    cache.value.set(`processed-${item.code}`, processedItem);
    return processedItem;
  });
};

// بارگذاری داده‌ها
const fetchHesabdariTables = async () => {
  const now = Date.now();
  const CACHE_DURATION = 5 * 60 * 1000; // 5 دقیقه

  // اگر کش معتبر است و در حالت showSubTree نیستیم، از کش استفاده می‌کنیم
  if (globalCache.data && !props.showSubTree && (now - globalCache.lastUpdate) < CACHE_DURATION) {
    accountData.value = globalCache.data;
    return;
  }

  // اگر در حال بارگذاری هستیم، صبر می‌کنیم
  if (globalCache.isLoading && globalCache.promise) {
    await globalCache.promise;
    if (!props.showSubTree) {
      accountData.value = globalCache.data || [];
    }
    return;
  }

  globalCache.isLoading = true;
  isLoading.value = true;
  
  try {
    globalCache.promise = new Promise(async (resolve) => {
      try {
        let response;
        // اگر initialAccount وجود داشت، از آن برای فیلتر کردن استفاده می‌کنیم
        if (props.initialAccount?.code) {
          response = await axios.get(`/api/hesabdari/tables/tree?rootCode=${props.initialAccount.code}`);
        } else if (props.showSubTree && selectedAccount.value) {
          response = await axios.get(`/api/hesabdari/tables/tree?rootCode=${selectedAccount.value.code}`);
        } else {
          response = await axios.get('/api/hesabdari/tables/all');
        }

        if (response.data.Success && response.data.data) {
          let data;
          if (props.initialAccount?.code) {
            // در حالت initialAccount فقط زیرمجموعه‌ها را نمایش می‌دهیم
            data = response.data.data.children || [];
          } else if (props.showSubTree) {
            data = [response.data.data];
          } else {
            data = response.data.data.children || [];
          }
          
          const processedData = processTreeData(data);
          
          if (!props.showSubTree && !props.initialAccount?.code) {
            globalCache.data = processedData;
            globalCache.lastUpdate = now;
          }
          accountData.value = processedData;
        } else {
          showMessage('هیچ حسابی یافت نشد', 'warning');
        }
      } catch (error) {
        console.error('خطا در بارگذاری حساب‌ها:', error);
        showMessage('خطا در بارگذاری حساب‌ها', 'error');
      } finally {
        globalCache.isLoading = false;
        isLoading.value = false;
        resolve();
      }
    });

    await globalCache.promise;
  } catch (error) {
    console.error('خطا در بارگذاری حساب‌ها:', error);
    showMessage('خطا در بارگذاری حساب‌ها', 'error');
    globalCache.isLoading = false;
    isLoading.value = false;
  }
};

// جستجوی حساب در داده‌های موجود
const findAccount = (nodes: any[]): any => {
  for (const node of nodes) {
    if (node.code === props.modelValue) {
      return node;
    }
    if (node.children && node.children.length) {
      const found = findAccount(node.children);
      if (found) return found;
    }
  }
  return null;
};

// تنظیم مقدار اولیه حساب
const initializeAccount = async () => {
  if (props.modelValue) {
    if (props.initialAccount && !selectedAccount.value) {
      selectedAccount.value = {
        code: props.modelValue,
        name: props.initialAccount.name || '',
        tableType: props.initialAccount.tableType || '',
      };
      emit('select', selectedAccount.value);
      emit('accountSelected', selectedAccount.value);
      if (selectedAccount.value.tableType) {
        emit('tableType', selectedAccount.value.tableType);
      }
    }

    // اگر initialAccount وجود داشت، داده‌ها را بارگذاری می‌کنیم
    if (props.initialAccount?.code || !accountData.value.length) {
      await fetchHesabdariTables();
    }

    const account = findAccount(accountData.value);
    if (account && (!selectedAccount.value || selectedAccount.value.code !== account.code)) {
      selectedAccount.value = account;
      emit('select', account);
      emit('accountSelected', account);
      if (account.tableType && account.tableType !== selectedAccount.value?.tableType) {
        emit('tableType', account.tableType);
      }
    }
  } else {
    if (selectedAccount.value) {
      selectedAccount.value = null;
      emit('select', null);
      emit('accountSelected', null);
      emit('tableType', null);
    }
  }
};

// تغییر وضعیت گره
const toggleNode = (node: any) => {
  node.isOpen = !node.isOpen;
};

// مدیریت انتخاب گره
const handleNodeSelect = async (node: any) => {
  if (props.selectableOnly && node.children && node.children.length > 0) {
    showMessage('این گزینه قابل انتخاب نیست زیرا دارای زیرمجموعه است', 'error');
    return;
  }
  
  selectedAccount.value = node;
  emit('select', node);
  emit('update:modelValue', node.code);
  emit('accountSelected', node);
  menu.value = false;

  if (props.showSubTree) {
    await fetchHesabdariTables();
  }
};

// باز کردن منو
const openMenu = async () => {
  menu.value = true;
  // اگر initialAccount وجود داشت یا کش منقضی شده بود، داده‌ها را بارگذاری می‌کنیم
  if (props.initialAccount?.code || !accountData.value.length || (Date.now() - globalCache.lastUpdate) > 5 * 60 * 1000) {
    await fetchHesabdariTables();
  }
};

// اصلاح watch برای modelValue
watch(
  () => props.modelValue,
  async (newVal) => {
    if (newVal === selectedAccount.value?.code) return;
    await initializeAccount();
  },
  { immediate: true }
);

// اضافه کردن watch برای initialAccount
watch(
  () => props.initialAccount,
  async (newVal) => {
    if (newVal && props.modelValue) {
      await initializeAccount();
    }
  },
  { immediate: true }
);

// اضافه کردن watch برای showSubTree
watch(
  () => props.showSubTree,
  async () => {
    if (menu.value) {
      await fetchHesabdariTables();
    }
  }
);

onMounted(() => {
  if (props.modelValue || props.initialAccount) {
    initializeAccount();
  }
});
</script>

<style scoped>
.tree-container {
  max-height: 300px;
  overflow-y: auto;
  padding: 8px;
}
</style> 