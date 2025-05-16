<script lang="ts">
import {defineComponent} from 'vue'
import {getApiUrl, getSiteName} from '@/hesabixConfig'
export default defineComponent({
  name: "intro",
  data:()=>{return{
    siteName:''
  }},
  created(){
    getSiteName().then((name) => {
      this.siteName = name;
    });
  }
})
</script>

<template>
  <main id="main-container p-0 m-0">
    <!-- Hero -->
    <div class="bg-image" style="background-image: url('/u/img/plugins/accpro/intro.png');">
      <div class="bg-black-75">
        <div class="content content-top content-full text-center">
          <h1 class="text-white"><i class="fa fa-shop"></i></h1>
          <h1 class="fw-bold text-white mt-5 mb-3">بسته حسابداری پیشرفته</h1>
          <h2 class="h3 fw-normal text-white-75 mb-5">افزایش امکانات  {{ siteName }} برای کسب وکار‌های متوسط و بزرگ</h2>
          <RouterLink to="/acc/plugin-center/view-end/accpro">
                <span class="badge rounded-pill bg-primary fs-base px-3 py-2 me-2 m-1">
                  <i class="fa fa-user-circle me-1"></i> خرید </span>
          </RouterLink>
          <br>
          <i class="fa fa-arrow-down text-white"></i>
        </div>
      </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-sm-11 py-2">
          <!-- Story -->
          <article class="story justify-content-between">
            <div class="rounded-3 bg-white p-3 mb-3">
              <div class="row">
                <div class="col-sm-12 col-md-6">
                  <h3 class="text-primary mt-0">
                    بسته حسابداری پیشرفته 
                  </h3>
                  <p>
                    به وسیله این افزونه 
                    {{ siteName }}
                    خود را به یک سطح بالاتر ارتقا دهید. به وسیله این افزونه یک سری از ابزارهای گزارش گیری و امکانات پیشرفته که بیشتر مورد استفاده کسب و کارهای متوسط و بزرگ است برای شما فعال می‌شود.
                  </p>
                 
            
                </div>
                <div class="col-sm-12 col-md-6">
                  <img class="img-fluid" src="/img/plugins/accpro/intro.png" />
                </div>
              </div>
              <div class="alert alert-info mt-2">این افزونه در حال توسعه است و ممکن است برخی از قابلیت‌های آن در حال حاضر در دسترس نباشد</div>
              <h2 class="text-primary">امکانات:</h2>
              <ul>
                <li>قابلیت صدور و مدیریت فاکتورهای برگشت از فروش و برگشت از خرید</li>
                <li>قابلیت بستن سال مالی و ایجاد سال مالی جدید</li>
                <li>قابلیت صدور سند حسابداری به صورت مستقیم</li>
                <li>افزودن قابلیت گزارش از دفتر روزنامه</li>
                <li>افزودن قابلیت گزارش از ترازنامه به صورت بازه زمانی</li>
                <li>قابلیت ویرایش جدول حساب و  تعریف آیتم‌های جدید به صورت درختی</li>
                <li>افزودن گزارش سود و زیان</li>
                <li>افزودن گزارش دفتر کل</li>
                <li>قابلیت اضافه،ویرایش و حذف  جدول حساب ها</li>
                <li>افزودن گزارش مرور حساب‌ها</li>
                <li>افزودن گزارش مالیات</li>
                <li>قابلیت صدور حواله انبار جهت فاکتور برگشت از خرید و برگشت از فروش</li>
                <li>قابلیت صدور حواله انتقال بین انبارها</li>
                <li>افزودن اطلاعات فروش امروز و سود امروز به داشبورد کسب‌و‌کار</li>
                <li>قابلیت تعریف نمایه کسب و کار و مهر فروشگاه در اسناد حسابداری</li>
                <li>قابلیت تغییر گروهی قیمت کالاها و خدمات به صورت درصدی و مقداری</li>
                <li>قابلیت افزودن نمایه و لوگو کسب وکار و نمایش در اسناد حسابداری</li>
                <li>ارسال پیامک به مشتری برای ارسال کالا شامل کد رهگیری و نام باربری ، نحوه ارسال و ...</li>
              </ul>
            </div>
          </article>
          <!-- END Story -->
        </div>
      </div>
    </div>
    <!-- END Page Content -->
  </main>
</template>

<style scoped>

</style>