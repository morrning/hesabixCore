<template>
<v-toolbar title="جادوگر">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
</v-toolbar>

<v-container class="mt-8 px-2">
  <v-row justify="center" class="wizard-content">
    <v-col cols="12" md="10">
      <v-card class="elevation-0 main-card">
        <v-card-title class="text-h4 text-center mb-4">
          <v-icon color="primary" size="40" class="mr-2">mdi-wizard-hat</v-icon>
          جادوگر هوش مصنوعی
        </v-card-title>
        
        <v-card-text class="text-body-1 text-center">
          <p class="mb-4">
            به زودی با جادوگر هوش مصنوعی آشنا خواهید شد! این ابزار قدرتمند به شما امکان می‌دهد تا با استفاده از هوش مصنوعی، ماژول‌های جدید را به صورت پویا به نرم‌افزار اضافه کنید. دوران انحصار نرم‌افزارهای حسابداری به پایان رسیده است و شما می‌توانید نرم‌افزار خود را به‌روز کرده و آن را با نیازهای خاص کسب‌وکار خود تطبیق دهید، بدون نیاز به انتظار برای به‌روزرسانی‌های طولانی مدت یا پرداخت هزینه‌های گزاف برای سفارشی‌سازی.
          </p>
        </v-card-text>

        <v-row class="mt-4">
          <v-col cols="12" md="4">
            <v-card class="elevation-0 pa-4 text-center feature-card">
              <v-icon color="success" size="40" class="mb-2">mdi-robot</v-icon>
              <h3 class="text-h6 mb-2">هوش مصنوعی پیشرفته</h3>
              <p class="text-body-2">استفاده از آخرین تکنولوژی‌های هوش مصنوعی برای ایجاد ماژول‌های سفارشی</p>
            </v-card>
          </v-col>

          <v-col cols="12" md="4">
            <v-card class="elevation-0 pa-4 text-center feature-card">
              <v-icon color="info" size="40" class="mb-2">mdi-puzzle</v-icon>
              <h3 class="text-h6 mb-2">ماژول‌های پویا</h3>
              <p class="text-body-2">ایجاد و اضافه کردن ماژول‌های جدید بدون نیاز به کدنویسی</p>
            </v-card>
          </v-col>

          <v-col cols="12" md="4">
            <v-card class="elevation-0 pa-4 text-center feature-card">
              <v-icon color="warning" size="40" class="mb-2">mdi-lightning-bolt</v-icon>
              <h3 class="text-h6 mb-2">سرعت بالا</h3>
              <p class="text-body-2">توسعه سریع و کارآمد با استفاده از ابزارهای هوشمند</p>
            </v-card>
          </v-col>
        </v-row>

        <v-card-actions class="justify-center mt-6">
          <v-btn color="primary" size="large" prepend-icon="mdi-clock-outline" disabled>
            به زودی...
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-col>
  </v-row>
</v-container>
</template>

<script>
export default {
  name: 'WizardHome',
  data() {
    return {
      
    }
  },
  methods: {
    
  }
}
</script>

<style scoped>
.fixed-toolbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 2000;
  background-color: #ffffff !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
}

.wizard-content {
  position: relative;
  z-index: 1;
}

.main-card {
  background-color: #ffffff !important;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05) !important;
}

.feature-card {
  background-color: #f8f9fa !important;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.feature-card:hover {
  background-color: #ffffff !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
  transform: translateY(-4px);
}

.v-icon {
  transition: transform 0.3s ease;
}

.feature-card:hover .v-icon {
  transform: scale(1.1);
}

.v-card-title {
  color: #2c3e50 !important;
}

.v-card-text {
  color: #34495e !important;
}

.text-body-2 {
  color: #7f8c8d !important;
}
</style>
