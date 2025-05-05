<script>
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: "plugins",
  data: () => {
    return {
      dialog: false,
      loading: true,
      selected: {
        id: null,
        name: '',
        price: 0,
        time: 0,
      },
      rules: {
        required: [(v) => !!v || "این فیلد الزامی است"],
      },
      items: [],
      headers: [
        { text: "نام افزونه", value: "name" },
        { text: "قیمت(تومان)", value: "price" },
        { text: "پیشفرض فعال", value: "defaultOn" },
        { text: "دوره اعتبار(ثانیه)", value: "time" },
        { text: "برچسب زمان", value: "timeLabel" },
        { text: "ویرایش", value: "operation" },
      ]
    }
  },
  methods: {
    // دریافت لیست افزونه‌ها از سرور
    async loadData() {
      try {
        const response = await axios.post('/api/admin/plugins/list');
        this.items = response.data.map(item => ({
          ...item,
          price: Number(item.price), // تبدیل مقدار قیمت به عدد
          time: Number(item.time)    // تبدیل مقدار زمان به عدد
        }));
        this.loading = false;
      } catch (error) {
        Swal.fire({
          text: "خطا در دریافت لیست افزونه‌ها!",
          icon: "error",
          confirmButtonText: "باشه"
        });
        this.loading = false;
      }
    },

    // ارسال اطلاعات ویرایش شده به سرور
    async editPlugin() {
      const { valid } = await this.$refs.form.validate();
      if (valid) {
        this.loading = true;
        try {
          const response = await axios.post('/api/admin/plugin/update/data', this.selected);

          if (response.data.Success) {
            Swal.fire({
              text: this.$t('dialog.save_ok'),
              icon: 'success',
              confirmButtonText: this.$t('dialog.ok')
            });
            this.loadData();
          } else {
            Swal.fire({
              text: "خطا در به‌روزرسانی افزونه!",
              icon: "warning",
              confirmButtonText: "باشه"
            });
          }
        } catch (error) {
          Swal.fire({
            text: `خطا در ارتباط با سرور: ${error.response?.data?.message || error.message}`,
            icon: "error",
            confirmButtonText: "باشه"
          });
        }
        this.loading = false;
        this.dialog = false;
      }
    }
  },
  beforeMount() {
    this.loadData();
  }
}
</script>

<template>
  <v-toolbar color="toolbar" :title="$t('dialog.plugins') + ' : (' + items.length + ')'">
    <v-spacer></v-spacer>
  </v-toolbar>
  <v-container class="pa-0 ma-0">
    <v-card :loading="loading ? 'red' : null" :disabled="loading">
      <v-card-text class="pa-0">
        <v-row>
          <v-col>
            <EasyDataTable table-class-name="customize-table" show-index alternating :headers="headers" :items="items"
              rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
              rowsOfPageSeparatorMessage="از" theme-color="#1d90ff" header-text-direction="center"
              body-text-direction="center" :loading="loading">
              <template #item-operation="props: any">
                <v-btn variant="text" @click="selected = props; dialog = true;" color="success" icon="mdi-file-edit"
                  v-bind="props"></v-btn>
              </template>
              <template #item-defaultOn="{ defaultOn }">
                <span v-if="defaultOn">بله</span>
                <span v-else>‌خیر</span>
              </template>
              <template #item-time="{ time }">
                <span>{{ $filters.formatNumber(time) }}</span>
              </template>
              <template #item-price="{ price }">
                <span>{{ $filters.formatNumber(price) }}</span>
              </template>
            </EasyDataTable>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
  <v-dialog fullscreen v-model="dialog" width="auto">
    <v-card :loading="loading" min-width="400">
      <v-form :disabled="loading" ref="form" @submit.prevent="editPlugin">
        <v-toolbar class="fixed-top" color="toolbar" :title="$t('dialog.edit_plugin')">
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.save')" location="bottom">
              <template v-slot:activator="{ props }">
                <v-btn color="success" v-bind="props" type="submit" class="d-none d-sm-flex" variant="text"
                  icon="mdi-content-save" />
              </template>
            </v-tooltip>
          </template>
          <template v-slot:prepend>
            <v-tooltip :text="$t('dialog.close')" location="bottom">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" @click="dialog = false" class="d-none d-sm-flex" variant="text"
                  icon="mdi-close" />
              </template>
            </v-tooltip>
          </template>
        </v-toolbar>
        <v-card-text class="">
          <v-row>
            <v-col cols="12">
              <v-text-field :rules="rules.required" v-model="selected.name"
                :label="$t('dialog.plugin_name')"></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-number-input :rules="rules.required" control Variant="stacked" v-model="selected.price"
                :label="$t('dialog.price')" :hideInput="false" inset></v-number-input>
            </v-col>
            <v-col cols="12">
              <v-number-input :rules="rules.required" control Variant="stacked" v-model="selected.time"
                :label="$t('dialog.time')" :hideInput="false" inset></v-number-input>
            </v-col>
            <v-col cols="12">
              <v-text-field control Variant="stacked" v-model="selected.timeLabel" :rules="rules.required"
                :label="$t('dialog.timeLabel')" :hideInput="false" inset></v-text-field>
            </v-col>
            <v-col cols="12 mb-0">
              <v-switch color="primary" v-model="selected.defaultOn" :label="$t('dialog.default_on')" inset></v-switch>
            </v-col>
          </v-row>
        </v-card-text>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<style scoped></style>