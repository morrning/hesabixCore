<template>
  <div class="block block-content-full">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <router-link class="text-warning mx-2 px-2" to="/acc/business/users">
          <i class="fa fw-bold fa-arrow-right"></i>
        </router-link>
        تنظیمات دسترسی
      </h3>
      <div class="block-options">

      </div>
    </div>
    <div class="block-content p-0">
      <div class="alert alert-primary">
        ویرایش دسترسی‌های کاربر با نام
        <span class="fw-bold">{{ this.info.user }}</span>
        با پست الکترونیکی
        <span class="fw-bold">{{ this.info.email }}</span>
      </div>
      <div class="container-fluid">
        <div class="row">
          <label class="form-label">دسترسی ها</label>
          <div class="col-sm-12 col-md-4">
            <div class="mb-2">
              <div class="space-y-2">
                <div class="form-check form-switch">
                  <input v-model="info.persons" @change="savePerms()" class="form-check-input" type="checkbox">
                  <label class="form-check-label">اشخاص</label>
                </div>
              </div>
              <div class="space-y-2">
                <div class="form-check form-switch">
                  <input v-model="info.getpay" @change="savePerms()" class="form-check-input" type="checkbox">
                  <label class="form-check-label">دریافت و پرداخت</label>
                </div>
              </div>
              <div class="space-y-2">
                <div class="form-check form-switch">
                  <input v-model="info.commodity" @change="savePerms()" class="form-check-input" type="checkbox">
                  <label class="form-check-label">کالا و خدمات</label>
                </div>
              </div>
              <div class="space-y-2">
                <div class="form-check form-switch">
                  <input v-model="info.bank" @change="savePerms()" class="form-check-input" type="checkbox">
                  <label class="form-check-label">حساب‌های بانکی</label>
                </div>
              </div>
              <div class="space-y-2">
                <div class="form-check form-switch">
                  <input v-model="info.salary" @change="savePerms()" class="form-check-input" type="checkbox">
                  <label class="form-check-label">تنخواه گردان‌ها</label>
                </div>
              </div>
              <div class="space-y-2">
                <div class="form-check form-switch">
                  <input v-model="info.bankTransfer" @change="savePerms()" class="form-check-input" type="checkbox">
                  <label class="form-check-label">انتقال بین بانکی</label>
                </div>
              </div>
              <div class="space-y-2">
                <div class="form-check form-switch">
                  <input v-model="info.archiveUpload" @change="savePerms()" class="form-check-input" type="checkbox">
                  <label class="form-check-label">افزودن فایل به آرشیو</label>
                </div>
              </div>
              <div class="space-y-2">
                <div class="form-check form-switch">
                  <input v-model="info.archiveView" @change="savePerms()" class="form-check-input" type="checkbox">
                  <label class="form-check-label">مشاهده فایل های آرشیو</label>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-4">
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.buy" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">فاکتورهای خرید</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.sell" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">فاکتورهای فروش</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.cost" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">هزینه‌ها</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.income" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">درآمدها</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.report" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">گزارشات</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.cashdesk" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">صندوق‌ها</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.shareholder" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">سهامداران</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.archiveMod" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">ویرایش فایل های آرشیو</label>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-4">
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.cheque" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">مدیریت چک‌ها</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.accounting" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">اسناد حسابداری</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.settings" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">تنظیمات کسب و کار</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.log" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">تاریخچه کسب‌و‌کار</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.permission" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">تغییر سطوح دسترسی</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.store" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">انبارداری</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.wallet" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">کیف پول</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.archiveDelete" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">حذف فایل های آرشیو</label>
              </div>
            </div>
          </div>
        </div>
        <div v-show="this.isPluginActive('accpro')" class="row mt-2">
          <b>بسته حسابداری پیشرفته</b>
          <div class="col-sm-12 col-md-4">
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.plugAccproRfbuy" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">فاکتورهای برگشت از خرید</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.plugAccproCloseYear" @change="savePerms()" class="form-check-input"
                  type="checkbox">
                <label class="form-check-label">بستن سال مالی</label>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-4">
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.plugAccproRfsell" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">فاکتورهای برگشت از فروش</label>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-4">
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.plugAccproAccounting" @change="savePerms()" class="form-check-input"
                  type="checkbox">
                <label class="form-check-label">صدور و ویرایش اسناد حسابداری</label>
              </div>
            </div>
          </div>
        </div>
        <div v-show="this.isPluginActive('repservice')" class="row mt-2">
          <b>افزونه تعمیرکاران</b>
          <div class="col-sm-12 col-md-4">
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.plugRepservice" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">ثبت و ویرایش</label>
              </div>
            </div>
          </div>
        </div>
        <div class="row mt-2">
          <div v-show="this.isPluginActive('noghre')" class="col-sm-12 col-md-4">
            <b>افزونه کارگاه نقره سازی</b>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.plugNoghreAdmin" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">مدیر</label>
              </div>
            </div>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.plugNoghreSell" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">صندوق / فروش</label>
              </div>
            </div>
          </div>
          <div v-show="this.isPluginActive('cc')" class="col-sm-12 col-md-4">
            <b>افزونه باشگاه مشتریان</b>
            <div class="space-y-2">
              <div class="form-check form-switch">
                <input v-model="info.plugCCAdmin" @change="savePerms()" class="form-check-input" type="checkbox">
                <label class="form-check-label">مدیر</label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


</template>

<script>
import axios from "axios";

export default {
  name: "user_perm_edit",
  data: () => {
    return {
      info: {},
      plugins: {}
    }
  },
  methods: {
    isPluginActive(plugName) {
      return this.plugins[plugName] !== undefined;
    },
    getData(id) {
      axios.post('/api/business/get/user/permissions',
        {
          'bid': localStorage.getItem('activeBid'),
          'email': id
        }
      ).then((response) => {
        this.info = response.data;
        this.info.bid = localStorage.getItem('activeBid');
      });
      //get active plugins
      axios.post('/api/plugin/get/actives',).then((response) => {
        this.plugins = response.data;
      });
    },
    savePerms() {
      axios.post('/api/business/save/user/permissions', this.info)
    }
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.getData(to.params.email);
    })
  },
}
</script>

<style scoped></style>