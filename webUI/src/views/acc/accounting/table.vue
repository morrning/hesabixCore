<template>
  <v-toolbar flat>
    <v-toolbar-title>
      <v-icon class="mx-2">mdi-table</v-icon>
      جدول حساب‌ها
    </v-toolbar-title>
  </v-toolbar>
  <v-container fluid>
    <v-alert v-if="!isAccproActive" type="warning" dense outlined class="mt-3">
      برای افزودن، ویرایش یا حذف ردیف‌های حساب، لطفاً افزونه "حسابداری پیشرفته" را از بخش بازار تهیه کنید.
    </v-alert>

    <loading color="blue" loader="dots" v-model:active="isLoading" :is-full-page="false" class="text-center" />

    <Tree :nodes="tree" :config="config" class="tree-view-style">
      <template #after-input="{ node }">
        <div class="node-actions" v-if="isAccproActive">
          <v-icon small color="success" class="mx-1" @click.stop="openAddDialog(node)">
            mdi-plus
          </v-icon>
          <v-icon v-if="!node.is_public" small color="primary" class="mx-1" @click.stop="openEditDialog(node)">
            mdi-pencil
          </v-icon>
          <v-icon v-if="!node.is_public && (!node.children || node.children.length === 0)" small color="error"
            class="mx-1" @click.stop="openDeleteDialog(node)">
            mdi-delete
          </v-icon>
        </div>
      </template>
    </Tree>

    <!-- دیالوگ اضافه کردن زیرمجموعه -->
    <v-dialog v-model="addDialog" max-width="400" persistent>
      <v-card>
        <v-toolbar flat color="success" dark>
          <v-toolbar-title>اضافه کردن زیرمجموعه</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon @click="addDialog = false" v-bind="attrs" v-on="on">
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </template>
            <span>بستن</span>
          </v-tooltip>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon @click="addNode" :loading="dialogLoading" :disabled="!newNodeText.trim()" v-bind="attrs"
                v-on="on">
                <v-icon>mdi-check</v-icon>
              </v-btn>
            </template>
            <span>ثبت</span>
          </v-tooltip>
        </v-toolbar>
        <v-card-text class="pt-4">
          <v-text-field v-model="newNodeText" label="نام ردیف حساب جدید"
            :rules="[v => !!v.trim() || 'این فیلد نمی‌تواند خالی باشد']" :disabled="dialogLoading"></v-text-field>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- دیالوگ ویرایش -->
    <v-dialog v-model="editDialog" max-width="400" persistent>
      <v-card>
        <v-toolbar flat color="primary" dark>
          <v-toolbar-title>ویرایش ردیف حساب</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon @click="editDialog = false" v-bind="attrs" v-on="on">
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </template>
            <span>بستن</span>
          </v-tooltip>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon @click="saveEditNode" :loading="dialogLoading" :disabled="!editNodeText.trim()" v-bind="attrs"
                v-on="on">
                <v-icon>mdi-check</v-icon>
              </v-btn>
            </template>
            <span>ذخیره</span>
          </v-tooltip>
        </v-toolbar>
        <v-card-text class="pt-4">
          <v-text-field v-model="editNodeText" label="نام ردیف حساب"
            :rules="[v => !!v.trim() || 'این فیلد نمی‌تواند خالی باشد']" :disabled="dialogLoading"></v-text-field>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- دیالوگ حذف -->
    <v-dialog v-model="deleteDialog" max-width="400" persistent>
      <v-card>
        <v-toolbar flat color="error" dark>
          <v-toolbar-title>حذف ردیف حساب</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon @click="deleteDialog = false" v-bind="attrs" v-on="on">
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </template>
            <span>بستن</span>
          </v-tooltip>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon @click="confirmDeleteNode" :loading="dialogLoading" v-bind="attrs" v-on="on">
                <v-icon>mdi-check</v-icon>
              </v-btn>
            </template>
            <span>تأیید حذف</span>
          </v-tooltip>
        </v-toolbar>
        <v-card-text class="pt-4">
          آیا مطمئنی که می‌خوای ردیف حساب "{{ selectedNode?.text }}" رو حذف کنی؟
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- دیالوگ پیام عمومی -->
    <v-dialog v-model="messageDialog" max-width="400">
      <v-card>
        <v-toolbar flat :color="messageTitle === 'موفقیت' ? 'success' : 'error'" dark>
          <v-toolbar-title>{{ messageTitle }}</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn icon @click="messageDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>
        <v-card-text class="pt-4">{{ messageText }}</v-card-text>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import { ref } from "vue";
import Tree from "vue3-treeview";
import "vue3-treeview/dist/style.css";
import axios from "axios";
import Loading from "vue-loading-overlay";

export default {
  name: "AccountingTable",
  components: {
    Tree,
    Loading,
  },
  setup() {
    const isLoading = ref(true);
    const dialogLoading = ref(false);
    const isAccproActive = ref(true);
    const tree = ref({});
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
        draw: "M 12 2 L 12 22 M 2 12 L 22 12",
      },
    });
    const addDialog = ref(false);
    const editDialog = ref(false);
    const deleteDialog = ref(false);
    const messageDialog = ref(false);
    const messageTitle = ref("");
    const messageText = ref("");
    const newNodeText = ref("");
    const editNodeText = ref("");
    const selectedNode = ref(null);

    const checkAccproPlugin = async () => {
      try {
        const response = await axios.post("/api/plugin/get/actives");
        isAccproActive.value = !!response.data["accpro"];
      } catch (error) {
        console.error("خطا در بررسی پلاگین‌ها:", error);
        isAccproActive.value = false;
      }
    };

    const loadData = async () => {
      try {
        const response = await axios.post("/api/accounting/table/get");
        tree.value = response.data;
        config.value.roots = tree.value["1"]?.children || [];
        if (!tree.value["1"]) console.warn("ردیف حساب ریشه '1' پیدا نشد!");
      } catch (error) {
        console.error("خطا در دریافت داده‌ها:", error);
      } finally {
        isLoading.value = false;
      }
    };

    const showMessage = (title, text) => {
      messageTitle.value = title;
      messageText.value = text;
      messageDialog.value = true;
    };

    const openAddDialog = (node) => {
      selectedNode.value = node;
      newNodeText.value = "";
      addDialog.value = true;
    };

    const addNode = async () => {
      if (!newNodeText.value.trim()) return; // اگر خالی بود، ادامه نمی‌دهیم
      dialogLoading.value = true;
      try {
        const response = await axios.post("/api/accounting/table/add", {
          text: newNodeText.value,
          parentId: selectedNode.value.id,
        });
        if (response.data.result === 1) {
          const newNode = response.data.node;
          tree.value[newNode.id] = newNode;
          if (!tree.value[selectedNode.value.id].children) {
            tree.value[selectedNode.value.id].children = [];
          }
          tree.value[selectedNode.value.id].children.push(newNode.id);
          addDialog.value = false;
          showMessage("موفقیت", "ردیف حساب با موفقیت اضافه شد!");
        } else {
          showMessage("خطا", response.data.message || "خطا در افزودن ردیف حساب!");
        }
      } catch (error) {
        console.error("خطا در افزودن ردیف حساب:", error);
        showMessage("خطا", "خطا در افزودن ردیف حساب!");
      } finally {
        dialogLoading.value = false;
      }
    };

    const openEditDialog = (node) => {
      selectedNode.value = node;
      editNodeText.value = node.text;
      editDialog.value = true;
    };

    const saveEditNode = async () => {
      if (!editNodeText.value.trim()) return; // اگر خالی بود، ادامه نمی‌دهیم
      dialogLoading.value = true;
      try {
        const response = await axios.post("/api/accounting/table/edit", {
          id: selectedNode.value.id,
          text: editNodeText.value,
        });
        if (response.data.result === 1) {
          tree.value[selectedNode.value.id] = {
            ...tree.value[selectedNode.value.id],
            ...response.data.node,
          };
          editDialog.value = false;
          showMessage("موفقیت", "ردیف حساب با موفقیت ویرایش شد!");
        } else {
          showMessage("خطا", response.data.message || "خطا در ویرایش ردیف حساب!");
        }
      } catch (error) {
        console.error("خطا در ویرایش ردیف حساب:", error);
        showMessage("خطا", "خطا در ویرایش ردیف حساب!");
      } finally {
        dialogLoading.value = false;
      }
    };

    const openDeleteDialog = (node) => {
      selectedNode.value = node;
      deleteDialog.value = true;
    };

    const confirmDeleteNode = async () => {
      dialogLoading.value = true;
      try {
        const response = await axios.post("/api/accounting/table/delete", {
          id: selectedNode.value.id,
        });
        if (response.data.result === 1) {
          delete tree.value[selectedNode.value.id];
          Object.values(tree.value).forEach((node) => {
            if (node.children) {
              node.children = node.children.filter(
                (childId) => childId !== selectedNode.value.id
              );
            }
          });
          deleteDialog.value = false;
          showMessage("موفقیت", "ردیف حساب با موفقیت حذف شد!");
        } else {
          showMessage("خطا", response.data.message || "خطا در حذف ردیف حساب!");
        }
      } catch (error) {
        console.error("خطا در حذف ردیف حساب:", error);
        showMessage("خطا", "خطا در حذف ردیف حساب!");
      } finally {
        dialogLoading.value = false;
      }
    };

    checkAccproPlugin();
    loadData();

    return {
      isLoading,
      dialogLoading,
      isAccproActive,
      tree,
      config,
      addDialog,
      editDialog,
      deleteDialog,
      messageDialog,
      messageTitle,
      messageText,
      newNodeText,
      editNodeText,
      selectedNode,
      openAddDialog,
      addNode,
      openEditDialog,
      saveEditNode,
      openDeleteDialog,
      confirmDeleteNode,
    };
  },
};
</script>

<style scoped>
:deep(.tree-node) {
  padding: 8px 16px;
  transition: background-color 0.3s;
}

:deep(.tree-node:hover) {
  background-color: #f5f5f5;
}

.node-actions {
  display: inline-flex;
  align-items: center;
  margin-right: 8px;
}

:deep(.tree-node) .node-actions {
  opacity: 0;
  transition: opacity 0.2s;
}

:deep(.tree-node:hover) .node-actions {
  opacity: 1;
}
</style>