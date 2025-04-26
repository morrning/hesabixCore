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
            :key="node.id"
            :node="node"
            :selected-id="selectedAccount?.id"
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
};

type ValidationRule = (value: any) => boolean | string;

interface AccountNode {
  id: number;
  name: string;
  children?: AccountNode[];
  isOpen?: boolean;
  tableType?: string;
  type?: string;
}

const props = defineProps({
  modelValue: {
    type: Number,
    default: null,
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
    type: Object as () => AccountNode | null,
    default: null,
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
    if (cache.value.has(`processed-${item.id}`)) {
      return cache.value.get(`processed-${item.id}`);
    }
    const processedItem = {
      ...item,
      name: decodeUnicode(item.name),
      children: item.children ? processTreeData(item.children) : [],
      isOpen: false,
      tableType: item.type,
    };
    cache.value.set(`processed-${item.id}`, processedItem);
    return processedItem;
  });
};

// بارگذاری تمام داده‌ها
const fetchAllHesabdariTables = async () => {
  // اگر داده‌ها در کش سراسری وجود دارند
  if (globalCache.data) {
    accountData.value = globalCache.data;
    return;
  }

  // اگر در حال بارگذاری است، منتظر بمان
  if (globalCache.isLoading && globalCache.promise) {
    await globalCache.promise;
    accountData.value = globalCache.data || [];
    return;
  }

  // شروع بارگذاری جدید
  globalCache.isLoading = true;
  isLoading.value = true;
  
  try {
    globalCache.promise = new Promise(async (resolve) => {
      try {
        const response = await axios.get('/api/hesabdari/tables/all');
        if (response.data.Success && response.data.data) {
          const allNodes = processTreeData(response.data.data.children || []);
          globalCache.data = allNodes;
          accountData.value = allNodes;
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
    if (node.id === props.modelValue) {
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
    // اگر initialAccount وجود دارد و هنوز حساب انتخاب نشده
    if (props.initialAccount && !selectedAccount.value) {
      selectedAccount.value = {
        id: props.modelValue,
        name: props.initialAccount.name || '',
        tableType: props.initialAccount.tableType || '',
      };
      emit('select', selectedAccount.value);
      emit('accountSelected', selectedAccount.value);
      if (selectedAccount.value.tableType) {
        emit('tableType', selectedAccount.value.tableType);
      }
    }

    // لود داده‌ها اگر هنوز لود نشده‌اند
    if (!accountData.value.length) {
      await fetchAllHesabdariTables();
    }

    // جستجوی حساب در داده‌ها
    const account = findAccount(accountData.value);
    if (account && (!selectedAccount.value || selectedAccount.value.id !== account.id)) {
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
const handleNodeSelect = (node: any) => {
  selectedAccount.value = node;
  emit('update:modelValue', node.id);
  emit('select', node);
  emit('accountSelected', node);
  if (node.tableType) {
    emit('tableType', node.tableType);
  }
};

// باز کردن منو
const openMenu = () => {
  menu.value = true;
  if (!accountData.value.length && !isLoading.value) {
    fetchAllHesabdariTables();
  }
};

// اصلاح watch برای modelValue
watch(
  () => props.modelValue,
  async (newVal, oldVal) => {
    if (newVal === oldVal || newVal === selectedAccount.value?.id) return;
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