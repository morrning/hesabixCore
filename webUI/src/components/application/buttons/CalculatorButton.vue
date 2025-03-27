<template>
    <div>
      <!-- دکمه برای باز کردن ماشین حساب -->
      <v-btn class="d-none d-sm-flex" stacked @click="dialog = true" @keyup.enter="dialog = true" tabindex="0">
        <v-icon>mdi-calculator</v-icon>
      </v-btn>
  
      <!-- دیالوگ پاپ‌آپ ماشین حساب -->
      <v-dialog v-model="dialog" max-width="300" @keydown="handleKeydown">
        <v-card>
          <!-- نوار ابزار در بالای ماشین حساب -->
          <v-toolbar color="primary" dark>
            <v-toolbar-title>ماشین حساب</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn icon @click="dialog = false" @keyup.enter="dialog = false" tabindex="0">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-toolbar>
  
          <v-card-text>
            <!-- نمایشگر ماشین حساب -->
            <v-text-field
              :value="formattedDisplay"
              readonly
              class="display"
              variant="outlined"
              tabindex="-1"
            ></v-text-field>
  
            <!-- دکمه‌های ماشین حساب -->
            <v-row>
              <v-col cols="3" v-for="btn in buttons" :key="btn">
                <v-btn
                  :class="{ 'active-btn': activeButton === btn }"
                  :color="getButtonColor(btn)"
                  flat
                  block
                  @click="handleButtonClick(btn)"
                  @keyup.enter="handleButtonClick(btn)"
                  tabindex="0"
                >
                  {{ btn }}
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-dialog>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        dialog: false,
        display: "0",
        current: "",
        previous: "",
        operation: null,
        activeButton: null,
        numberButtons: ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "."], // دکمه‌های اعداد
        operatorButtons: ["+", "-", "*", "/", "%"], // دکمه‌های عملگر
        actionButtons: ["C", "="], // دکمه‌های عملیاتی
        buttons: ["7", "8", "9", "/", "4", "5", "6", "*", "1", "2", "3", "-", "0", ".", "%", "+", "C", "="],
      };
    },
    computed: {
      formattedDisplay() {
        if (this.display === "خطا") return this.display;
        const num = parseFloat(this.display);
        return isNaN(num) ? this.display : num.toLocaleString("fa-IR");
      },
    },
    methods: {
      handleButtonClick(btn) {
        this.handleInput(btn);
        this.activeButton = btn;
        setTimeout(() => {
          this.activeButton = null;
        }, 100);
      },
      handleInput(btn) {
        if (btn === "C") {
          this.clear();
        } else if (btn === "=") {
          this.calculate();
        } else if (this.isOperator(btn)) {
          this.setOperation(btn);
        } else {
          this.appendNumber(btn);
        }
      },
      isOperator(btn) {
        return this.operatorButtons.includes(btn);
      },
      appendNumber(btn) {
        if (this.current === "0" && btn !== ".") {
          this.current = btn;
        } else {
          this.current += btn;
        }
        this.display = this.current;
      },
      setOperation(op) {
        if (this.current === "") return;
        if (this.previous !== "") {
          this.calculate();
        }
        this.operation = op;
        this.previous = this.current;
        this.current = "";
      },
      calculate() {
        if (this.current === "" || this.previous === "") return;
  
        const prev = parseFloat(this.previous);
        const curr = parseFloat(this.current);
        let result = 0;
  
        switch (this.operation) {
          case "+":
            result = prev + curr;
            break;
          case "-":
            result = prev - curr;
            break;
          case "*":
            result = prev * curr;
            break;
          case "/":
            result = curr !== 0 ? prev / curr : "خطا";
            break;
          case "%":
            result = (prev * curr) / 100;
            break;
        }
  
        this.display = result.toString();
        this.current = result.toString();
        this.previous = "";
        this.operation = null;
      },
      clear() {
        this.display = "0";
        this.current = "";
        this.previous = "";
        this.operation = null;
      },
      handleKeydown(event) {
        const key = event.key;
        if (this.buttons.includes(key)) {
          this.handleButtonClick(key);
          event.preventDefault();
        } else if (key === "Enter" || key === "=") {
          this.handleButtonClick("=");
          event.preventDefault();
        } else if (key === "Escape") {
          this.dialog = false;
          event.preventDefault();
        } else if (key === "Backspace") {
          this.current = this.current.slice(0, -1) || "0";
          this.display = this.current;
          event.preventDefault();
        }
      },
      // تعیین رنگ دکمه‌ها بر اساس نوع
      getButtonColor(btn) {
        if (this.numberButtons.includes(btn)) return "blue darken-5"; // رنگ اعداد
        if (this.operatorButtons.includes(btn)) return "gray darken-1"; // رنگ عملگرها
        if (this.actionButtons.includes(btn)) return "red lighten-1"; // رنگ دکمه‌های عملیاتی
        return "grey lighten-4"; // رنگ پیش‌فرض
      },
    },
  };
  </script>
  
  <style scoped>
  .display {
    margin-bottom: 20px;
    text-align: right;
  }
  .v-btn {
    min-width: 0 !important;
    border-radius: 4px;
    box-shadow: none !important;
  }
  
  /* تغییر رنگ لحظه‌ای دکمه */
  .active-btn {
    background-color: #0288d1 !important; /* آبی تیره‌تر برای حالت فعال */
    color: white !important;
    transition: background-color 0.1s ease;
  }
  </style>