<template>
  <v-toolbar color="toolbar" title="هوش مصنوعی حسابیکس">
    
  </v-toolbar>
  <div class="page-container">
    <div class="content-container">
      <v-card class="chat-container" elevation="0">
        <div class="chat-box">
          <div class="messages-container" ref="messagesContainer">
            <!-- پیام هوش مصنوعی -->
            <div class="message ai-message" v-if="displayWelcome">
              <v-avatar color="#1a237e" size="36" class="mr-2">
                <v-icon color="white" size="20">mdi-robot</v-icon>
              </v-avatar>
              <div class="message-content">
                <div class="message-text typing-text">{{ displayWelcome }}</div>
              </div>
            </div>

            <div class="message ai-message" v-if="displayThanks">
              <v-avatar color="#1a237e" size="36" class="mr-2">
                <v-icon color="white" size="20">mdi-robot</v-icon>
              </v-avatar>
              <div class="message-content">
                <div class="message-text typing-text">{{ displayThanks }}</div>
              </div>
            </div>

            <div class="message ai-message" v-if="displayCapabilities">
              <v-avatar color="#1a237e" size="36" class="mr-2">
                <v-icon color="white" size="20">mdi-robot</v-icon>
              </v-avatar>
              <div class="message-content">
                <div class="message-text typing-text">{{ displayCapabilities }}</div>
              </div>
            </div>

            <div class="message ai-message" v-for="(capability, index) in displayCapabilitiesList" :key="index">
              <v-avatar color="#1a237e" size="36" class="mr-2">
                <v-icon color="white" size="20">mdi-robot</v-icon>
              </v-avatar>
              <div class="message-content">
                <div class="message-text typing-text">
                  <v-icon color="#1a237e" size="16" class="mr-2">mdi-check-circle</v-icon>
                  {{ capability }}
                </div>
              </div>
            </div>

            <div class="message ai-message" v-if="displayPrompt">
              <v-avatar color="#1a237e" size="36" class="mr-2">
                <v-icon color="white" size="20">mdi-robot</v-icon>
              </v-avatar>
              <div class="message-content">
                <div class="message-text typing-text">{{ displayPrompt }}</div>
              </div>
            </div>

            <!-- پیام‌های کاربر و پاسخ‌های هوش مصنوعی -->
            <template v-for="(message, index) in userMessages" :key="index">
              <!-- پیام کاربر -->
              <div class="message user-message" v-if="typeof message === 'string'">
                <div class="message-content">
                  <div class="message-text">{{ message }}</div>
                </div>
                <v-avatar color="grey lighten-2" size="36" class="ml-2">
                  <v-icon color="grey darken-1" size="20">mdi-account</v-icon>
                </v-avatar>
              </div>

              <!-- پیام هوش مصنوعی -->
              <div class="message ai-message" v-else-if="message && message.isAI">
                <v-avatar color="#1a237e" size="36" class="mr-2">
                  <v-icon color="white" size="20">mdi-robot</v-icon>
                </v-avatar>
                <div class="message-content" :class="{ 'details-message': message.isDetails }">
                  <div class="message-text" v-html="message.text.replace(/\n/g, '<br>')"></div>
                </div>
              </div>
            </template>

            <!-- نشانگر تایپ -->
            <div class="message ai-message" v-if="isTyping">
              <v-avatar color="#1a237e" size="36" class="mr-2">
                <v-icon color="white" size="20">mdi-robot</v-icon>
              </v-avatar>
              <div class="message-content">
                <div class="message-text">
                  <span class="typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- باکس ورودی پیام -->
          <div class="input-container">
            <v-textarea v-model="userMessage" placeholder="پیام خود را اینجا بنویسید..." rows="1" auto-grow hide-details
              variant="plain" class="message-input" @keydown.enter.prevent="sendMessage"></v-textarea>
            <v-btn color="#1a237e" icon :loading="isLoading" @click="sendMessage" class="send-button"
              :disabled="!userMessage.trim()">
              <v-icon>mdi-send</v-icon>
            </v-btn>
          </div>
        </div>
      </v-card>
    </div>
  </div>
</template>

<script>
export default {
  name: 'WizardHome',
  data() {
    return {
      userMessage: '',
      isLoading: false,
      isTyping: true,
      userMessages: [],
      aiResponses: [
        {
          message: 'با عرض پوزش، در حال حاضر سخت‌افزار پردازش داده متصل نشده است. لطفاً با پشتیبانی فنی تماس بگیرید تا در اسرع وقت مشکل را برطرف کنیم.',
          details: 'برای اتصال سخت‌افزار پردازش داده، نیاز به تنظیمات خاصی است که باید توسط تیم فنی انجام شود. این تنظیمات شامل:\n- اتصال به سرور پردازش\n- تنظیم پارامترهای امنیتی\n- راه‌اندازی ماژول‌های پردازشی\nمی‌باشد.'
        },
        {
          message: 'متأسفانه در حال حاضر سیستم پردازش داده در دسترس نیست. این مشکل موقت است و به زودی برطرف خواهد شد.',
          details: 'برای فعال‌سازی کامل سیستم، نیاز به انجام مراحل زیر است:\n- تأیید اتصال به سرور مرکزی\n- راه‌اندازی ماژول‌های پردازشی\n- تنظیم پارامترهای امنیتی\nلطفاً با پشتیبانی فنی تماس بگیرید.'
        },
        {
          message: 'با کمال تأسف، سخت‌افزار پردازش داده هنوز آماده بهره‌برداری نیست. این مشکل به زودی برطرف خواهد شد.',
          details: 'برای راه‌اندازی کامل سیستم، تیم فنی در حال انجام مراحل زیر است:\n- نصب و پیکربندی سرور پردازش\n- تنظیم پارامترهای امنیتی\n- راه‌اندازی ماژول‌های پردازشی\nلطفاً با پشتیبانی فنی تماس بگیرید.'
        },
        {
          message: 'در حال حاضر سیستم پردازش داده در حالت تعمیر و نگهداری است. به زودی سرویس‌دهی از سر گرفته خواهد شد.',
          details: 'برای فعال‌سازی مجدد سیستم، نیاز به انجام مراحل زیر است:\n- بررسی اتصال به سرور مرکزی\n- به‌روزرسانی ماژول‌های پردازشی\n- تنظیم مجدد پارامترهای امنیتی\nلطفاً با پشتیبانی فنی تماس بگیرید.'
        },
        {
          message: 'با عرض پوزش، سخت‌افزار پردازش داده در حال حاضر غیرفعال است. تیم فنی در حال بررسی و رفع مشکل است.',
          details: 'برای فعال‌سازی سیستم، نیاز به انجام مراحل زیر است:\n- تأیید اتصال به سرور پردازش\n- راه‌اندازی ماژول‌های پردازشی\n- تنظیم پارامترهای امنیتی\nلطفاً با پشتیبانی فنی تماس بگیرید.'
        }
      ],
      welcomePatterns: [
        {
          welcome: 'سلام! 👋',
          thanks: 'از اینکه از هوش مصنوعی حسابیکس استفاده می‌کنید، بسیار خوشحالم! من یک هوش مصنوعی مستقل هستم که به صورت کامل در سرورهای داخلی حسابیکس میزبانی می‌شوم و نیازی به سرویس‌های خارجی ندارم.'
        },
        {
          welcome: 'درود! 🌟',
          thanks: 'به هوش مصنوعی حسابیکس خوش آمدید! من یک دستیار هوشمند مستقل هستم که به صورت کامل در سرورهای داخلی حسابیکس میزبانی می‌شوم و آماده خدمت‌رسانی به شما هستم.'
        },
        {
          welcome: 'سلام و وقت بخیر! ✨',
          thanks: 'خوشحالم که از هوش مصنوعی حسابیکس استفاده می‌کنید. من یک دستیار هوشمند مستقل هستم که به صورت کامل در سرورهای داخلی حسابیکس میزبانی می‌شوم و می‌توانم در زمینه‌های مختلف به شما کمک کنم.'
        },
        {
          welcome: 'به حسابیکس خوش آمدید! 🚀',
          thanks: 'من هوش مصنوعی مستقل حسابیکس هستم که به صورت کامل در سرورهای داخلی میزبانی می‌شوم. خوشحالم که می‌توانم به شما در استفاده از این نرم‌افزار کمک کنم.'
        },
        {
          welcome: 'سلام! من دستیار هوشمند شما هستم 🤖',
          thanks: 'به عنوان یک هوش مصنوعی مستقل که به صورت کامل در سرورهای داخلی حسابیکس میزبانی می‌شوم، آماده‌ام تا در هر زمینه‌ای که نیاز دارید به شما کمک کنم.'
        }
      ],
      selectedPattern: null,
      capabilities: 'من می‌توانم به شما در موارد زیر کمک کنم:',
      capabilitiesList: [
        'ساخت گزارش‌های سفارشی با استفاده از هوش مصنوعی داخلی',
        'ایجاد ماژول‌های جدید بدون نیاز به کدنویسی',
        'پاسخ به سؤالات شما درباره نرم‌افزار با استفاده از دانش داخلی',
        'راهنمایی در استفاده از امکانات مختلف با هوش مصنوعی مستقل',
        'تجزیه و تحلیل داده‌های مالی با استفاده از الگوریتم‌های داخلی',
        'پیش‌بینی روندهای مالی با استفاده از هوش مصنوعی اختصاصی'
      ],
      prompt: 'لطفاً سؤال یا درخواست خود را در باکس زیر بنویسید. من با استفاده از هوش مصنوعی مستقل خود، به شما کمک خواهم کرد.',
      displayWelcome: '',
      displayThanks: '',
      displayCapabilities: '',
      displayCapabilitiesList: [],
      displayPrompt: ''
    }
  },
  async mounted() {
    this.selectRandomPattern()
    await this.startTypingAnimation()
  },
  watch: {
    userMessages: {
      handler() {
        this.$nextTick(() => {
          this.scrollToBottom()
        })
      },
      deep: true
    },
    displayWelcome() {
      this.$nextTick(() => {
        this.scrollToBottom()
      })
    },
    displayThanks() {
      this.$nextTick(() => {
        this.scrollToBottom()
      })
    },
    displayCapabilities() {
      this.$nextTick(() => {
        this.scrollToBottom()
      })
    },
    displayCapabilitiesList: {
      handler() {
        this.$nextTick(() => {
          this.scrollToBottom()
        })
      },
      deep: true
    },
    displayPrompt() {
      this.$nextTick(() => {
        this.scrollToBottom()
      })
    }
  },
  methods: {
    selectRandomPattern() {
      const randomIndex = Math.floor(Math.random() * this.welcomePatterns.length)
      this.selectedPattern = this.welcomePatterns[randomIndex]
      this.welcome = this.selectedPattern.welcome
      this.thanks = this.selectedPattern.thanks
    },
    async startTypingAnimation() {
      // تایپ پیام خوش‌آمدگویی
      await this.typeText(this.welcome, (text) => {
        this.displayWelcome = text
      }, 15)
      await this.delay(100)

      // تایپ پیام تشکر
      await this.typeText(this.thanks, (text) => {
        this.displayThanks = text
      }, 15)
      await this.delay(100)

      // تایپ معرفی قابلیت‌ها
      await this.typeText(this.capabilities, (text) => {
        this.displayCapabilities = text
      }, 15)
      await this.delay(100)

      // تایپ لیست قابلیت‌ها
      for (const capability of this.capabilitiesList) {
        this.displayCapabilitiesList.push('')
        await this.typeText(capability, (text) => {
          this.displayCapabilitiesList[this.displayCapabilitiesList.length - 1] = text
        }, 15)
        await this.delay(50)
      }

      // تایپ پیام نهایی
      await this.typeText(this.prompt, (text) => {
        this.displayPrompt = text
      }, 15)

      this.isTyping = false
    },
    async typeText(text, callback, speed = 50) {
      let currentText = ''
      for (let i = 0; i < text.length; i++) {
        currentText += text[i]
        callback(currentText)
        await this.delay(speed)
      }
    },
    delay(ms) {
      return new Promise(resolve => setTimeout(resolve, ms))
    },
    async sendMessage() {
      if (!this.userMessage.trim()) return

      const message = this.userMessage.trim()
      this.userMessages.push(message)
      this.userMessage = ''
      this.isLoading = true

      // انتخاب پاسخ رندوم
      const randomResponse = this.aiResponses[Math.floor(Math.random() * this.aiResponses.length)]

      // نمایش پاسخ اصلی
      await this.delay(1000)
      this.userMessages.push({
        text: randomResponse.message,
        isAI: true
      })

      // نمایش جزئیات
      await this.delay(500)
      this.userMessages.push({
        text: randomResponse.details,
        isAI: true,
        isDetails: true
      })

      this.isLoading = false
      this.$nextTick(() => {
        this.scrollToBottom()
      })
    },
    scrollToBottom() {
      const container = this.$refs.messagesContainer
      if (container) {
        container.scrollTo({
          top: container.scrollHeight,
          behavior: 'smooth'
        })
      }
    }
  }
}
</script>

<style scoped>
.page-container {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.content-container {
  flex: 1;
  height: 100vh;
}

.chat-container {
  height: 100%;
  background-color: #f5f5f5;
}

.chat-box {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.message {
  display: flex;
  align-items: flex-start;
  max-width: 80%;
}

.ai-message {
  align-self: flex-start;
}

.user-message {
  align-self: flex-end;
  flex-direction: row-reverse;
}

.message-content {
  background-color: white;
  padding: 12px 16px;
  border-radius: 12px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.ai-message .message-content {
  background-color: #e3f2fd;
  border-bottom-right-radius: 4px;
}

.user-message .message-content {
  background-color: #1a237e;
  color: white;
  border-bottom-left-radius: 4px;
}

.message-text {
  font-size: 1rem;
  line-height: 1.5;
}

.input-container {
  padding: 16px;
  background-color: white;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 12px;
}

.message-input {
  background-color: #f5f5f5 !important;
  border-radius: 24px;
  padding: 8px 16px !important;
}

.message-input :deep(.v-field__input) {
  padding: 8px !important;
  font-size: 1rem;
  color: #424242;
}

.send-button {
  transition: all 0.3s ease;
}

.send-button:hover {
  transform: scale(1.1);
}

.typing-indicator {
  display: flex;
  gap: 4px;
  align-items: center;
  height: 24px;
}

.typing-indicator span {
  width: 8px;
  height: 8px;
  background-color: #1a237e;
  border-radius: 50%;
  animation: typing 1s infinite ease-in-out;
}

.typing-indicator span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typing {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}

.details-message {
  background-color: #f5f5f5 !important;
  border: 1px solid #e0e0e0;
  font-size: 0.9rem;
  color: #616161;
}

.details-message .message-text {
  white-space: pre-line;
}

.messages-container::-webkit-scrollbar {
  width: 6px;
}

.messages-container::-webkit-scrollbar-track {
  background: transparent;
}

.messages-container::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 3px;
}

.messages-container::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.3);
}
</style>
