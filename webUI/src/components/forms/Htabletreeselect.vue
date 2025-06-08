<template>
  <v-menu v-model="menu" :close-on-content-click="false">
    <template v-slot:activator="{ props }">
      <v-text-field
        v-bind="props"
        :model-value="selectedItem ? decodeLabel(selectedItem.label) + ` (${selectedItem.id})` : ''"
        readonly
        variant="outlined"
        hide-details
        density="compact"
        :label="label"
        class=""
        prepend-inner-icon="mdi-table"
        clearable
        @click:clear="clearSelection"
      >
        <template v-slot:append-inner>
          <v-icon>{{ menu ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
        </template>
      </v-text-field>
    </template>

    <v-card min-width="300" max-width="400">
      <v-card-text class="pa-2">
        <v-text-field
          v-model="searchQuery"
          density="compact"
          variant="outlined"
          hide-details
          class="mb-2"
          placeholder="جستجو..."
          prepend-inner-icon="mdi-magnify"
          clearable
          @click:clear="searchQuery = ''"
        ></v-text-field>
        
        <v-treeview
          :items="filteredItems"
          item-children="children"
          item-title="label"
          item-value="id"
          :loading="loading"
          density="compact"
          class="tree-container rtl-tree"
          hoverable
        >
          <template v-slot:prepend="{ item }">
            <div class="d-flex align-center">
              <v-radio
                :model-value="selectedItem?.id"
                :value="item.id"
                hide-details
                density="compact"
                @click.stop
                @change="selectItem(item)"
              ></v-radio>
              <v-icon size="small" class="mr-1">
                {{ item.children && item.children.length > 0 ? 'mdi-folder' : 'mdi-file-outline' }}
              </v-icon>
            </div>
          </template>

          <template v-slot:label="{ item }">
            {{ decodeLabel(item.label) }} ({{ item.id }})
          </template>
        </v-treeview>
      </v-card-text>
    </v-card>
  </v-menu>
</template>

<script>
import axios from 'axios';

export default {
  name: 'CostTreeSelect',
  props: {
    modelValue: {
      type: [Object, Number, String],
      default: null
    },
    label: {
      type: String,
      default: 'جدول حساب'
    },
    returnObject: {
      type: Boolean,
      default: false
    },
    tableType: {
      type: String,
      required: true,
      validator: (value) => ['cost', 'income'].includes(value)
    }
  },
  data() {
    return {
      selectedItem: null,
      treeItems: [],
      loading: false,
      menu: false,
      searchQuery: ''
    };
  },
  computed: {
    filteredItems() {
      if (!this.searchQuery) return this.treeItems;
      
      const searchTerm = this.searchQuery.toLowerCase();
      return this.filterNodes(this.treeItems, (node) => {
        const labelMatch = this.decodeLabel(node.label).toLowerCase().includes(searchTerm);
        const idMatch = node.id.toString().includes(searchTerm);
        return labelMatch || idMatch;
      });
    }
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (this.returnObject) {
          this.selectedItem = newVal;
        } else {
          this.selectedItem = this.findItemById(this.treeItems, newVal);
        }
      },
      immediate: true
    }
  },
  methods: {
    findItemById(items, id) {
      for (const item of items) {
        if (item.id === id) return item;
        if (item.children?.length) {
          const found = this.findItemById(item.children, id);
          if (found) return found;
        }
      }
      return null;
    },
    async fetchTreeData() {
      this.loading = true;
      try {
        const response = await axios.get(`/api/accounting/table/childs/${this.tableType}`);
        this.treeItems = response.data;
        
        if (this.modelValue) {
          if (this.returnObject) {
            this.selectedItem = this.modelValue;
          } else {
            this.selectedItem = this.findItemById(this.treeItems, this.modelValue);
          }
        }
      } catch (error) {
        console.error('خطا در دریافت داده‌ها:', error);
        this.$toast.error('خطا در بارگذاری داده‌ها');
      } finally {
        this.loading = false;
      }
    },
    decodeLabel(label) {
      try {
        return decodeURIComponent(JSON.parse(`"${label}"`));
      } catch (e) {
        return label;
      }
    },
    selectItem(item) {
      this.selectedItem = item;
      this.$emit('update:modelValue', this.returnObject ? item : item.id);
      this.menu = false;
    },
    clearSelection() {
      this.selectedItem = null;
      this.$emit('update:modelValue', null);
    },
    filterNodes(nodes, predicate) {
      return nodes.reduce((filtered, node) => {
        const clone = { ...node };
        
        if (predicate(clone)) {
          if (clone.children?.length) {
            clone.children = this.filterNodes(clone.children, predicate);
          }
          filtered.push(clone);
        } else if (clone.children?.length) {
          clone.children = this.filterNodes(clone.children, predicate);
          if (clone.children.length) {
            filtered.push(clone);
          }
        }
        
        return filtered;
      }, []);
    }
  },
  created() {
    this.fetchTreeData();
  },
};
</script>
