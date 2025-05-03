<template>
  <v-toolbar color="grey-lighten-4" density="compact" title="دسته‌بندی‌های کالا و خدمات">
    <template v-slot:prepend>
      <v-btn icon variant="text" color="warning" class="d-none d-sm-none d-md-block" @click="$router.back()">
        <v-icon>mdi-arrow-right</v-icon>
      </v-btn>
    </template>
  </v-toolbar>
  <v-container>
    <v-card variant="outlined" class="pa-2">
      <v-skeleton-loader
        v-if="isLoading"
        type="table-heading"
        class="mb-2"
      ></v-skeleton-loader>
      <Tree v-else :nodes="tree" :config="config">
        <template #after-input="props">
          <v-tooltip location="top">
            <template v-slot:activator="{ props: tooltipProps }">
              <v-btn v-bind="tooltipProps" icon variant="text" size="small" @click="addChild(props.node.id)">
                <v-icon size="small">mdi-plus-circle</v-icon>
              </v-btn>
            </template>
            افزودن زیرمجموعه
          </v-tooltip>

          <v-tooltip location="top">
            <template v-slot:activator="{ props: tooltipProps }">
              <v-btn v-bind="tooltipProps" icon variant="text" color="warning" size="small"
                @click="editeNode(props.node)">
                <v-icon size="small">mdi-pencil</v-icon>
              </v-btn>
            </template>
            ویرایش دسته‌بندی
          </v-tooltip>

          <v-tooltip v-if="!props.node.children || props.node.children.length === 0" location="top">
            <template v-slot:activator="{ props: tooltipProps }">
              <v-btn v-bind="tooltipProps" icon variant="text" color="error" size="small"
                @click="deleteNode(props.node)">
                <v-icon size="small">mdi-delete</v-icon>
              </v-btn>
            </template>
            حذف دسته‌بندی
          </v-tooltip>
        </template>
      </Tree>
    </v-card>

    <!-- Modal add child -->
    <v-dialog v-model="showAddModal" persistent max-width="400">
      <v-card class="rounded-lg">
        <v-card-title class="text-h6 bg-grey-lighten-4 py-4">
          <v-icon color="primary" class="me-2">mdi-plus-circle</v-icon>
          افزودن دسته‌بندی
        </v-card-title>
        <v-card-text class="pt-6">
          <v-text-field
            v-model="addCatText"
            label="نام دسته بندی"
            required
            variant="outlined"
            density="comfortable"
            hide-details="auto"
            class="mb-2"
            :rules="[v => !!v || 'نام دسته‌بندی الزامی است']"
            :disabled="isLoading"
          >
            <template v-slot:label>
              <span class="text-danger">(لازم)</span> نام دسته بندی
            </template>
          </v-text-field>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn color="secondary" variant="text" @click="showAddModal = false" class="me-2" min-width="100" :disabled="isLoading">
            <v-icon start>mdi-close</v-icon>
            انصراف
          </v-btn>
          <v-btn color="primary" @click="submitChild" min-width="100" :loading="isLoading">
            <v-icon start>mdi-content-save</v-icon>
            ثبت
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal edit node -->
    <v-dialog v-model="showEditModal" persistent max-width="400">
      <v-card class="rounded-lg">
        <v-card-title class="text-h6 bg-grey-lighten-4 py-4">
          <v-icon color="warning" class="me-2">mdi-pencil</v-icon>
          ویرایش دسته‌بندی
        </v-card-title>
        <v-card-text class="pt-6">
          <v-text-field
            v-model="editCatText"
            label="نام دسته بندی"
            required
            variant="outlined"
            density="comfortable"
            hide-details="auto"
            class="mb-2"
            :rules="[v => !!v || 'نام دسته‌بندی الزامی است']"
            :disabled="isLoading"
          >
            <template v-slot:label>
              <span class="text-danger">(لازم)</span> نام دسته بندی
            </template>
          </v-text-field>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn color="secondary" variant="text" @click="showEditModal = false" class="me-2" min-width="100" :disabled="isLoading">
            <v-icon start>mdi-close</v-icon>
            انصراف
          </v-btn>
          <v-btn color="primary" @click="submitEditChild" min-width="100" :loading="isLoading">
            <v-icon start>mdi-content-save</v-icon>
            ثبت
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Dialog delete confirmation -->
    <v-dialog v-model="showDeleteDialog" persistent max-width="400">
      <v-card class="rounded-lg">
        <v-card-title class="text-h6 bg-error-lighten-5 py-4">
          <v-icon color="error" class="me-2">mdi-alert-circle</v-icon>
          حذف دسته‌بندی
        </v-card-title>
        <v-card-text class="pt-6">
          <v-alert
            type="warning"
            variant="tonal"
            class="mb-4"
            border="start"
          >
            <div class="text-subtitle-2 font-weight-bold mb-2">هشدار</div>
            آیا از حذف این دسته‌بندی اطمینان دارید؟
            <div class="text-caption mt-2">این عملیات غیرقابل بازگشت است.</div>
          </v-alert>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn color="secondary" variant="text" @click="showDeleteDialog = false" class="me-2" min-width="100" :disabled="isLoading">
            <v-icon start>mdi-close</v-icon>
            خیر
          </v-btn>
          <v-btn color="error" @click="confirmDelete" min-width="100" :loading="isLoading">
            <v-icon start>mdi-delete</v-icon>
            بله، حذف شود
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar for notifications -->
    <v-snackbar
      v-model="showSnackbar"
      :color="snackbarColor"
      :timeout="3000"
      location="bottom"
      class="rounded-lg"
      elevation="2"
    >
      <div class="d-flex align-center">
        <v-icon :color="snackbarColor" class="me-2">
          {{ snackbarColor === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle' }}
        </v-icon>
        {{ snackbarText }}
      </div>
      <template v-slot:actions>
        <v-btn icon variant="text" @click="showSnackbar = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script>
import axios from "axios";
import { ref, onMounted } from "vue";
import treeview from "vue3-treeview";
import "vue3-treeview/dist/style.css";

export default {
  name: "list",
  components: {
    Tree: treeview
  },
  setup() {
    const isLoading = ref(true);
    const showAddModal = ref(false);
    const showEditModal = ref(false);
    const showDeleteDialog = ref(false);
    const showSnackbar = ref(false);
    const snackbarText = ref('');
    const snackbarColor = ref('success');
    const selectedNode = ref(0);
    const nodeToDelete = ref(null);
    const addCatText = ref('');
    const editCatText = ref('');
    const tree = ref([]);
    const config = ref({
      roots: [],
      opened: true,
      openedIcon: {
        type: "shape",
        stroke: "black",
        strokeWidth: 3,
        viewBox: "0 0 24 24",
        draw: "M 2 12 L 22 12",
      },
      closedIcon: {
        type: "shape",
        stroke: "black",
        strokeWidth: 3,
        viewBox: "0 0 24 24",
        draw: `M 12 2 L 12 22 M 2 12 L 22 12`,
      },
    });

    const showNotification = (text, color = 'success') => {
      snackbarText.value = text;
      snackbarColor.value = color;
      showSnackbar.value = true;
    };

    const loadData = async () => {
      isLoading.value = true;
      try {
        const response = await axios.post('/api/commodity/cat/get');
        const convertIdsToString = (items) => {
          return Object.entries(items).reduce((acc, [key, value]) => {
            acc[key] = {
              ...value,
              id: String(value.id),
              parent: value.parent ? String(value.parent) : null,
              children: value.children ? value.children.map(child => String(child)) : [],
              state: {
                ...value.state,
                disabled: false,
                opened: true,
                selected: false,
                loading: false
              }
            };
            return acc;
          }, {});
        };

        tree.value = convertIdsToString(response.data.items);
        config.value = {
          ...config.value,
          roots: [String(response.data.root)],
          opened: true,
          editing: null,
          disabled: false
        };
      } catch (error) {
        console.error('Error loading data:', error);
        showNotification('خطا در بارگذاری اطلاعات', 'error');
      } finally {
        isLoading.value = false;
      }
    };

    const addChild = (id) => {
      selectedNode.value = String(id);
      showAddModal.value = true;
    };

    const submitChild = async () => {
      if (addCatText.value.trim().length === 0) {
        showNotification('نام دسته‌بندی الزامی است', 'error');
        return;
      }

      isLoading.value = true;
      try {
        const response = await axios.post('/api/commodity/cat/insert', {
          upper: selectedNode.value,
          text: addCatText.value
        });

        if (response.data && response.data.id) {
          const newNode = {
            id: String(response.data.id),
            text: addCatText.value,
            parent: selectedNode.value,
            children: [],
            state: {
              disabled: false,
              opened: true,
              selected: false,
              loading: false
            }
          };

          if (tree.value[selectedNode.value]) {
            if (!tree.value[selectedNode.value].children) {
              tree.value[selectedNode.value].children = [];
            }
            tree.value[selectedNode.value].children.push(String(response.data.id));
          }

          tree.value[String(response.data.id)] = newNode;
          showNotification('دسته‌بندی با موفقیت افزوده شد');
          showAddModal.value = false;
          addCatText.value = '';
        }
      } catch (error) {
        console.error('Error adding category:', error);
        showNotification('خطا در افزودن دسته‌بندی', 'error');
      } finally {
        isLoading.value = false;
      }
    };

    const editeNode = (node) => {
      selectedNode.value = String(node.id);
      editCatText.value = node.text;
      showEditModal.value = true;
    };

    const submitEditChild = async () => {
      if (editCatText.value.trim().length === 0) {
        showNotification('نام دسته‌بندی الزامی است', 'error');
        return;
      }

      isLoading.value = true;
      try {
        await axios.post('/api/commodity/cat/edit', {
          id: selectedNode.value,
          text: editCatText.value
        });

        if (tree.value[selectedNode.value]) {
          tree.value[selectedNode.value].text = editCatText.value;
        }

        showNotification('دسته‌بندی با موفقیت ویرایش شد');
        showEditModal.value = false;
        editCatText.value = '';
      } catch (error) {
        console.error('Error editing category:', error);
        showNotification('خطا در ویرایش دسته‌بندی', 'error');
      } finally {
        isLoading.value = false;
      }
    };

    const deleteNode = (node) => {
      nodeToDelete.value = node;
      showDeleteDialog.value = true;
    };

    const confirmDelete = async () => {
      if (!nodeToDelete.value) return;

      isLoading.value = true;
      try {
        await axios.post('/api/commodity/cat/delete', {
          id: nodeToDelete.value.id
        });

        if (tree.value[nodeToDelete.value.parent]) {
          const parentNode = tree.value[nodeToDelete.value.parent];
          parentNode.children = parentNode.children.filter(child => child !== nodeToDelete.value.id);
        }
        delete tree.value[nodeToDelete.value.id];

        showNotification('دسته‌بندی با موفقیت حذف شد');
        showDeleteDialog.value = false;
        nodeToDelete.value = null;
      } catch (error) {
        console.error('Error deleting category:', error);
        showNotification('خطا در حذف دسته‌بندی', 'error');
      } finally {
        isLoading.value = false;
      }
    };

    onMounted(() => {
      loadData();
    });

    return {
      isLoading,
      showAddModal,
      showEditModal,
      showDeleteDialog,
      showSnackbar,
      snackbarText,
      snackbarColor,
      selectedNode,
      addCatText,
      editCatText,
      tree,
      config,
      loadData,
      addChild,
      submitChild,
      editeNode,
      submitEditChild,
      deleteNode,
      confirmDelete
    };
  }
}
</script>

<style scoped>
.node-input,
.node-text,
.tree {
  font-family: 'vazir', sans-serif;
}

:deep(.v-btn) {
  min-width: 24px !important;
  width: 24px !important;
  height: 24px !important;
}

:deep(.v-btn:not(.v-btn--icon)) {
  min-width: 100px !important;
  width: auto !important;
  height: 36px !important;
}

:deep(.v-icon) {
  font-size: 16px !important;
}

:deep(.v-tooltip__content) {
  font-family: 'vazir', sans-serif !important;
  font-size: 12px !important;
}

:deep(.v-card-title) {
  padding: 16px !important;
}

:deep(.v-card-text) {
  padding: 0 16px !important;
}

:deep(.v-dialog) {
  border-radius: 8px !important;
}

:deep(.v-alert) {
  border-radius: 8px !important;
}

:deep(.v-snackbar) {
  border-radius: 8px !important;
  margin-bottom: 16px !important;
}

:deep(.v-snackbar__content) {
  padding: 12px !important;
  font-family: 'vazir', sans-serif !important;
}

:deep(.v-snackbar__wrapper) {
  min-width: 300px !important;
}

:deep(.v-skeleton-loader) {
  border-radius: 8px !important;
}
</style>