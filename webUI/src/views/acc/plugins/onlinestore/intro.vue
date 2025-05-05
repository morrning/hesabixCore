<script lang="ts">
import {defineComponent} from 'vue'
import {getApiUrl, getSiteName} from '@/hesabixConfig'
export default defineComponent({
  name: "intro",
  data:()=>{return{
    siteName:''
  }},
  created(){
    this.siteName = getSiteName();
  }
})
</script>

<template>
  <main id="main-container p-0 m-0">
    <!-- Hero -->
    <div class="bg-image" style="background-image: url('/img/plugins/onlinestore.webp');">
      <div class="bg-black-75">
        <div class="content content-top content-full text-center">
          <h1 class="text-white"><i class="fa fa-shop"></i></h1>
          <h1 class="fw-bold text-white mt-5 mb-3"> افزونه فروشگاه اینترنتی </h1>
          <h2 class="h3 fw-normal text-white-75 mb-5"> در کمتر از یک دقیقه فروشگاه آنلاین خودتو بساز </h2>
          <RouterLink to="/acc/plugin-center/view-end/onlinestore">
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
                    افزونه فروشگاه آنلاین
                  </h3>
                  <p>
                    امکانات ما در {{ siteName }} به شما کمک می‌کند تا با کمترین دانش فنی و در سریع‌ترین زمان ممکن فروشگاه آنلاین خود را بسازید و وارد دنیای تجارت الکترونیک شوید.
                  </p>
                  <p>
                    افزونه فروشگاه آنلاین {{ siteName }} متناسب با بازار ایران توسعه پیدا کرده و کلیه امکانات با توجه به نیاز بازار ایران بهینه سازی شده تا شما بتوانید به راحت‌ترین حالت ممکن یک فروشگاه با تعداد فروش روزانه بالا را مدیریت کنید.
                  </p>
                  <p>در تمام مسیر تیم {{ siteName }} با پیشتیبانی دائمی و مشاوره‌ حرفه‌ای در کنار شما خواهند بود تا بتوانید فروش خود را آغاز و پیشرفت کنید.</p>
            
                </div>
                <div class="col-sm-12 col-md-6">
                  <img class="img-fluid" src="/img/plugins/onlinestore/intro.webp" />
                </div>
              </div>
              <h2 class="text-primary">امکانات:</h2>
              <ul>
                <li>ایجاد بینهایت دسته‌بندی،کالا و ویژگی</li>
                <li>استفاده از روش‌های پرداخت درگاه پرداختی و کیف پول و حتی سند حسابداری</li>
                <li>قابلیت اتصال دامنه اختصاصی</li>
                <li>قابلیت افزودن کد‌های جاوا اسکریپت به صفحات قالب </li>
                <li>قابلیت ویرایش قالب فروشگاه</li>
                <li>قابلیت اخذ اینماد و درگاه پرداخت اختصاصی</li>
                <li class="text-danger">یکپارچه با {{ siteName }} و ثبت اسناد و پرداخت ها به صورت اتوماتیک</li>
                <li>قابلیت ارسال مطالب خبری و وبلاگ جهت افزایش بازدید و بالا رفتن ویو فروشگاه</li>
                <li>قابلیت افزودن کلید واژه و ویژگی و توضیحات هر کالا</li>
                <li>قابلیت تولید لینک‌های کوتاه جهت نمایش در پست‌های شبکه‌های اجتماعی</li>
                <li>انجام امور حسابداری و انبارداری و ارسال کالا از طریق {{ siteName }}</li>
                <li>یکپارچه با افزونه مدیریت مشتریان</li>
                <li>دارای قابلیت استفاده از بازاریابی پیامکی</li>
                <li>امکان تولید کد تخفیف</li>
                <li>امکان نمایش بینهایت تصویر و ویديو از کالا</li>
                <li>یکپارچه با بخش آرشیو فایل {{ siteName }}</li>
                <li>امکان اخد نظرات و پیشنهادات مشتریان و خریداران</li>
                <li>ابزارهای موجود برای بهینه‌سازی موتور جستجو (SEO) که به شما کمک می‌کنند تا در نتایج جستجوی گوگل بهتر دیده شوید.</li>
                <li>در مقایسه با برنامه نویسی یک وبسایت از ابتدا، استفاده از یک سیستم فروشگاه‌ساز مانند {{ siteName }} همیشه کم هزینه‌تر است. </li>
              </ul>
            </div>
            <div class="block block-bordered js-classic-nav d-none d-sm-block">
              <div class="block-content block-content-full">
                <div class="row g-0 border">
                  <div class="col-sm-4 col-xl-3">
                    <a class="block block-bordered block-link-pop text-center mb-0" href="javascript:void(0)">
                      <div class="block-content block-content-full text-center">
                        <div class="py-2">
                          <i class="far fa-2x fa-compass text-primary d-none d-sm-inline-block mb-3"></i>
                          <div> تعداد نصب فعال </div>
                          <div class="text-primary">3,415</div>
                        </div>
                      </div>
                    </a>
                  </div>
                  <div class="col-sm-4 col-xl-3">
                    <RouterLink to="/acc/plugin-center/view-end/noghre" class="block block-bordered block-link-pop text-center mb-0">
                      <div class="block-content block-content-full text-center">
                        <div class="py-2">
                          <i class="fa fa-2x fa-shopping-basket text-primary d-none d-sm-inline-block mb-3"></i>
                          <div>سفارش</div>
                          <div class="text-primary">فعال سازی آنی</div>
                        </div>
                      </div>
                    </RouterLink>
                  </div>
                  <div class="col-sm-4 col-xl-3">
                    <a class="block block-bordered block-link-pop text-center mb-0" href="https://app.hesabix.ir/profile/support-new">
                      <div class="block-content block-content-full text-center">
                        <div class="py-2">
                          <i class="far fa-2x fa-user-circle text-primary d-none d-sm-inline-block mb-3"></i>
                          <div> مشاوره </div>
                          <div class="text-primary">تیکت پشتیبانی</div>
                        </div>
                      </div>
                    </a>
                  </div>
                
                </div>
              </div>
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