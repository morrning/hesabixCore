<script lang="ts">
import {defineComponent} from 'vue'
import {getApiUrl, getSiteName} from '@/hesabixConfig'
export default defineComponent({
  name: "intro",
  data:()=>{return{
    siteName:''
  }},
  async created(){
    this.siteName = await getSiteName();
  }
})
</script>

<template>
  <main id="main-container pt-0 mt-o">
    <!-- Hero -->
    <div class="bg-image" style="background-image: url('/u/img/plugins/hrm/hrm.jpg');">
      <div class="bg-black-75">
        <div class="content content-top content-full text-center">
          <h1 class="text-white"><i class="fa fa-users"></i></h1>
          <h1 class="fw-bold text-white mt-5 mb-3"> افزونه مدیریت منابع انسانی </h1>
          <h2 class="h3 fw-normal text-white-75 mb-5">مدیریت هوشمند پرسنل، حقوق و دستمزد و حضور و غیاب با یک افزونه قدرتمند</h2>
          <RouterLink to="/acc/plugin-center/view-end/hrm">
                <span class="badge rounded-pill bg-primary fs-base px-3 py-2 me-2 m-1">
                  <i class="fa fa-shopping-cart me-1"></i> خرید نسخه آزمایشی </span>
          </RouterLink>
    
        </div>
      </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content content-full">
      <div class="row justify-content-center">
        <div class="col-sm-11 py-2">
          <!-- Story -->
          <article class="story justify-content-between">
            <div class="alert alert-info">
              <i class="fa fa-info-circle me-2"></i>
              این افزونه در حال توسعه و آزمایشی است. در نسخه اولیه، تنها از سند حقوق کارکنان پشتیبانی می‌کند. خرید شما باعث سرعت بیشتر در توسعه و بهبود قابلیت‌های آن می‌شود.
            </div>
            <p class="justify-content-between">
              مدیریت منابع انسانی یکی از مهمترین بخش‌های هر سازمان است که نیاز به دقت و نظم بالایی دارد. افزونه مدیریت منابع انسانی حسابیکس با ارائه قابلیت‌های متنوع، مدیریت پرسنل، محاسبه حقوق و دستمزد و کنترل حضور و غیاب را به ساده‌ترین شکل ممکن فراهم می‌کند.
            </p>
            <p>
              <strong>قابلیت‌های نسخه اولیه:</strong>
            </p>
            <ul class="list-group list-group-flush mb-4">
              <li class="list-group-item">
                <i class="fa fa-check text-success me-2"></i>
                ثبت سند حقوق کارکنان با در نظر گرفتن موارد قانونی
              </li>
            </ul>
            <p>
              <strong>قابلیت‌های در دست توسعه:</strong>
            </p>
            <ul class="list-group list-group-flush mb-4">
              <li class="list-group-item">
                <i class="fa fa-clock text-warning me-2"></i>
                سیستم حضور و غیاب هوشمند
              </li>
              <li class="list-group-item">
                <i class="fa fa-clock text-warning me-2"></i>
                مدیریت مرخصی‌ها و غیبت‌ها
              </li>
              <li class="list-group-item">
                <i class="fa fa-clock text-warning me-2"></i>
                ثبت مدارک و سوابق کاری
              </li>
            </ul>
            <p>
              این افزونه به صورت کامل با سیستم حسابداری حسابیکس یکپارچه شده و تمامی عملیات مالی مربوط به حقوق و دستمزد را به صورت خودکار در سیستم حسابداری ثبت می‌کند. همچنین امکان صدور فیش حقوقی و گزارش‌های مالی متنوع را فراهم می‌کند.
            </p>
            <p>
              با استفاده از این افزونه، دیگر نیازی به نرم افزارهای جداگانه برای مدیریت منابع انسانی نخواهید داشت. تمامی اطلاعات در یک سیستم یکپارچه ذخیره می‌شود و دسترسی به آن‌ها از هر مکان و در هر زمان امکان‌پذیر است.
            </p>
          </article>
          <!-- END Story -->
        </div>
      </div>
    </div>
    <!-- END Page Content -->
  </main>
</template>

<style scoped>
.bg-image {
  background-size: cover;
  background-position: center;
  min-height: 400px;
  position: relative;
}

.bg-black-75 {
  background-color: rgba(0, 0, 0, 0.75);
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
}

.story {
  line-height: 1.8;
  font-size: 1.1rem;
}

.alert {
  border-radius: 8px;
  margin-bottom: 2rem;
}

.list-group-item {
  border: none;
  padding: 0.75rem 1.25rem;
  background-color: transparent;
}

.list-group-item i {
  width: 20px;
  text-align: center;
}
</style>