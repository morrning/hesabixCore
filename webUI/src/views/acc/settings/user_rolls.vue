<template> <v-toolbar color="toolbar" title="کاربران و دسترسی‌ها">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
  </v-toolbar>
  <v-container>
    <v-row>
      <v-col cols="12">
        <v-form @submit.prevent="submitData">
          <v-row>
            <v-col cols="10">
              <v-text-field v-model="newEmail" label="برای افزودن کاربر جدید پست الکترونیکی را وارد کنید" type="email"
                variant="outlined" density="comfortable" class="email-field"></v-text-field>
            </v-col>
            <v-col cols="2">
              <v-btn color="primary" type="submit" block class="submit-btn">
                افزودن
              </v-btn>
            </v-col>
          </v-row>
        </v-form>
      </v-col>
    </v-row>

    <v-data-table :headers="headers" :items="users" :items-per-page="10" class="elevation-1">
      <template v-slot:item.index="{ index }">
        {{ index + 1 }}
      </template>

      <template v-slot:item.name="{ item }">
        <span class="font-weight-bold text-primary">{{ item.name }}</span>
      </template>

      <template v-slot:item.email="{ item }">
        <v-chip color="primary" size="small">{{ item.email }}</v-chip>
      </template>

      <template v-slot:item.mobile="{ item }">
        <v-chip color="info" size="small">{{ item.mobile || '-' }}</v-chip>
      </template>

      <template v-slot:item.actions="{ item }">
        <template v-if="item.owner != 1">
          <v-btn-group>
            <v-btn :to="{ name: 'business_user_roll_edit', params: { email: item.email } }" icon="mdi-pencil"
              variant="text" color="primary" size="small"></v-btn>
            <v-btn @click="confirmDelete(item.email)" icon="mdi-delete" variant="text" color="error"
              size="small"></v-btn>
          </v-btn-group>
        </template>
        <v-chip v-else color="success" size="small">مدیر کل</v-chip>
      </template>
    </v-data-table>
  </v-container>

  <!-- دیالوگ تایید حذف -->
  <v-dialog v-model="deleteDialog" max-width="400" transition="dialog-bottom-transition">
    <v-card class="rounded-lg" title="تایید حذف کاربر">
      <template v-slot:prepend>
        <v-avatar>
          <v-icon color="error">mdi-alert-circle</v-icon>
        </v-avatar>
      </template>
      <v-divider></v-divider>

      <v-card-text class="pt-4">
        <div class="text-body-1">
          آیا از حذف کاربر <span class="font-weight-bold text-primary">{{ userToDelete?.name }}</span> با ایمیل <span class="font-weight-bold text-primary">{{ userToDelete?.email }}</span> اطمینان دارید؟
        </div>
        <div class="text-caption text-medium-emphasis mt-2">
          این عملیات غیرقابل بازگشت است.
        </div>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions class="pa-4">
        <v-spacer></v-spacer>
        <v-btn color="grey-darken-1" variant="text" @click="deleteDialog = false" class="px-4">
          انصراف
        </v-btn>
        <v-btn color="error" variant="flat" @click="confirmDeleteAction" class="px-4">
          <v-icon start>mdi-delete</v-icon>
          حذف کاربر
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- اسنک‌بار برای نمایش پیام‌ها -->
  <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000">
    {{ snackbar.text }}
    <template v-slot:actions>
      <v-btn variant="text" @click="snackbar.show = false">
        بستن
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script>
import axios from "axios";

export default {
  name: "user_rolls",
  data: () => ({
    users: [],
    newEmail: '',
    headers: [
      { title: '#', key: 'index', align: 'center', sortable: false },
      { title: 'نام / نام خانوادگی', key: 'name', align: 'center' },
      { title: 'پست الکترونیکی', key: 'email', align: 'center' },
      { title: 'شماره موبایل', key: 'mobile', align: 'center' },
      { title: 'عملیات', key: 'actions', align: 'center', sortable: false }
    ],
    deleteDialog: false,
    userToDelete: null,
    snackbar: {
      show: false,
      text: '',
      color: 'success'
    }
  }),
  methods: {
    showSnackbar(text, color = 'success') {
      this.snackbar.text = text;
      this.snackbar.color = color;
      this.snackbar.show = true;
    },
    confirmDelete(email) {
      const user = this.users.find(u => u.email === email);
      this.userToDelete = {
        email: email,
        name: user ? user.name : email
      };
      this.deleteDialog = true;
    },
    confirmDeleteAction() {
      if (this.userToDelete) {
        const emailToDelete = this.userToDelete.email;
        axios.post('/api/business/delete/user', {
          'bid': localStorage.getItem('activeBid'),
          'email': emailToDelete
        }).then((response) => {
          if (response.data.result == 1) {
            this.users = this.users.filter(user => user.email !== emailToDelete);
            this.showSnackbar('کاربر با موفقیت حذف شد.');
          } else {
            this.showSnackbar('خطا در حذف کاربر', 'error');
          }
          this.deleteDialog = false;
          this.userToDelete = null;
        }).catch((error) => {
          this.showSnackbar('خطا در حذف کاربر', 'error');
          this.deleteDialog = false;
          this.userToDelete = null;
        });
      }
    },
    submitData() {
      if (this.newEmail == '') {
        this.showSnackbar('پست الکترونیکی را وارد کنید.', 'error');
        return;
      }

      axios.post("/api/business/add/user", {
        'bid': localStorage.getItem('activeBid'),
        'email': this.newEmail
      }).then((response) => {
        if (response.data.result == 0) {
          this.showSnackbar('کاربری با این پست الکترونیکی یافت نشد.', 'error');
        } else if (response.data.result == 1) {
          this.showSnackbar('قبلا این کاربر به کسب و کار افزوده شده است.', 'error');
        } else {
          this.users.push({
            'name': response.data.data.name,
            'email': response.data.data.email,
            'owner': response.data.data.owner,
            'mobile': response.data.data.mobile || null
          });
          this.showSnackbar('کاربر با موفقیت عضو کسب و کار شد.');
        }
        this.newEmail = '';
      }).catch(() => {
        this.showSnackbar('خطا در افزودن کاربر', 'error');
      });
    }
  },
  mounted() {
    axios.post('/api/user/get/users/of/business/' + localStorage.getItem('activeBid')).then((response) => {
      this.users = Array.isArray(response.data) ? response.data : [];
    }).catch(() => {
      this.showSnackbar('خطا در دریافت لیست کاربران', 'error');
    });
  }
}
</script>

<style scoped>
.v-table {
  width: 100%;
}

.email-field :deep(.v-field) {
  height: 56px;
}

.submit-btn {
  height: 56px;
}

.v-data-table :deep(th),
.v-data-table :deep(td) {
  text-align: center !important;
  vertical-align: middle;
}

.v-data-table :deep(.v-chip) {
  margin: 0 auto;
}
</style>