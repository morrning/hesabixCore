<!-- SecretDialog.vue -->
<template>
  <div>
    <v-dialog v-model="showDialog" :max-width="dialogWidth" persistent>
      <v-card>
        <v-card-title>به جنگ هلوها برو ...</v-card-title>
        <v-card-text>
          <canvas ref="gameCanvas"></canvas>
          <div class="mt-2">
            <strong>کنترل‌ها:</strong> H برای پرش (حداکثر ۳ بار) | D برای حالت قهرمان ({{ superModeCount }} باقی‌مانده)
          </div>
          <div class="mt-2">
            <strong>امتیاز کنونی:</strong> {{ Math.floor(score) }} | 
            <strong>بهترین رکورد:</strong> {{ bestScore }}
          </div>
          <div class="mt-2 text-caption">
            هلو در حسابداری نمادی از انحصارگری نرم‌افزارهای حسابداری است که باید شکستش داد!
          </div>
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="resetGame">
            {{ gameRunning ? 'شروع مجدد' : 'شروع' }}
          </v-btn>
          <v-btn color="error" @click="closeDialog">بستن</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import dinoImage from '@/assets/dino.png';
import peachImage from '@/assets/peach.png';
import heroImage from '@/assets/hero.png';

export default {
  name: 'SecretDialog',
  data() {
    return {
      showDialog: false,
      keySequence: [],
      timeoutId: null,
      canvas: null,
      ctx: null,
      dino: {
        x: 0.1, // نسبت به عرض
        y: 0.75, // نسبت به ارتفاع
        width: 0.08,
        height: 0.2,
        dy: 0,
        gravity: 0.0015,
        jump: -0.06,
        jumpCount: 0,
        maxJumps: 3
      },
      obstacles: [],
      score: 0,
      bestScore: 0,
      gameRunning: false,
      animationFrameId: null,
      dinoImg: null,
      peachImg: null,
      heroImg: null,
      isQPressed: false,
      superMode: false,
      superModeCount: 3,
      superModeTimeout: null,
      hero: { x: 0.5, y: 0.5, width: 0.1, height: 0.3, visible: false },
      lastObstacleTime: 0,
      canvasWidth: 0,
      canvasHeight: 0
    };
  },
  computed: {
    dialogWidth() {
      return window.innerWidth > 600 ? '600px' : '90%';
    }
  },
  mounted() {
    window.addEventListener('keydown', this.handleKeyDown);
    window.addEventListener('keyup', this.handleKeyUp);
    window.addEventListener('resize', this.handleResize);
    this.loadImages();
    this.bestScore = parseInt(localStorage.getItem('bestScore') || '0', 10);
  },
  watch: {
    showDialog(newVal) {
      if (newVal) {
        this.$nextTick(() => {
          this.updateCanvasSize();
        });
      }
    }
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.handleKeyDown);
    window.removeEventListener('keyup', this.handleKeyUp);
    window.removeEventListener('resize', this.handleResize);
    if (this.animationFrameId) cancelAnimationFrame(this.animationFrameId);
    if (this.superModeTimeout) clearTimeout(this.superModeTimeout);
  },
  methods: {
    loadImages() {
      this.dinoImg = new Image();
      this.dinoImg.src = dinoImage;
      this.dinoImg.onerror = () => console.error('Failed to load dino image:', dinoImage);

      this.peachImg = new Image();
      this.peachImg.src = peachImage;
      this.peachImg.onerror = () => console.error('Failed to load peach image:', peachImage);

      this.heroImg = new Image();
      this.heroImg.src = heroImage;
      this.heroImg.onerror = () => console.error('Failed to load hero image:', heroImage);
    },
    updateCanvasSize() {
      if (!this.$refs.gameCanvas) return;

      this.canvas = this.$refs.gameCanvas;
      this.ctx = this.canvas.getContext('2d');
      this.canvasWidth = this.canvas.parentElement.clientWidth;
      this.canvasHeight = this.canvasWidth * 0.333;
      this.canvas.width = this.canvasWidth;
      this.canvas.height = this.canvasHeight;
    },
    handleResize() {
      if (this.showDialog) {
        this.updateCanvasSize();
      }
    },
    handleKeyDown(event) {
      const gameKeys = [72, 68, 81]; // H, D, Q
      if (this.showDialog && gameKeys.includes(event.keyCode)) {
        event.preventDefault(); // فقط وقتی دیالوگ باز است
      }

      if (event.keyCode === 81) {
        this.isQPressed = true;
      }
      if (this.showDialog && event.keyCode === 72 && this.dino.jumpCount < this.dino.maxJumps) {
        this.dino.dy = this.dino.jump - (this.dino.jumpCount * 0.015);
        this.dino.jumpCount++;
      }
      if (this.showDialog && event.keyCode === 68 && this.superModeCount > 0 && !this.superMode) {
        this.activateSuperMode();
      }
      if (this.isQPressed) {
        this.checkSequence(event);
      }
    },
    handleKeyUp(event) {
      if (event.keyCode === 81) {
        this.isQPressed = false;
        this.keySequence = [];
        if (this.timeoutId) clearTimeout(this.timeoutId);
      }
    },
    checkSequence(event) {
      const targetKeyCodes = [72, 69, 83, 65, 66, 73, 88]; // H E S A B I X
      const currentKeyCode = event.keyCode;

      if (this.timeoutId) clearTimeout(this.timeoutId);
      this.timeoutId = setTimeout(() => {
        this.keySequence = [];
      }, 2000);

      if (targetKeyCodes.includes(currentKeyCode)) {
        this.keySequence.push(currentKeyCode);
      }

      if (this.keySequence.length >= targetKeyCodes.length) {
        const sequenceMatch = targetKeyCodes.every(
          (code, index) => this.keySequence[index] === code
        );
        if (sequenceMatch) {
          this.showDialog = true;
          this.keySequence = [];
        } else {
          this.keySequence = [];
        }
      }
    },
    startGame() {
      if (!this.gameRunning) {
        this.updateCanvasSize();
        this.gameRunning = true;
        this.score = 0;
        this.obstacles = [];
        this.lastObstacleTime = Date.now();
        this.gameLoop();
      }
    },
    gameLoop() {
      try {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        this.drawBackground();
        this.ctx.fillStyle = '#d3d3d3';
        this.ctx.fillRect(0, this.canvas.height * 0.95, this.canvas.width, this.canvas.height * 0.05);

        this.dino.dy += this.dino.gravity;
        this.dino.y += this.dino.dy;
        if (this.dino.y >= 0.75) {
          this.dino.y = 0.75;
          this.dino.dy = 0;
          this.dino.jumpCount = 0;
        }
        if (this.dino.y < 0) this.dino.y = 0;

        const dinoX = this.dino.x * this.canvasWidth;
        const dinoY = this.dino.y * this.canvasHeight;
        const dinoWidth = this.dino.width * this.canvasWidth;
        const dinoHeight = this.dino.height * this.canvasHeight;

        if (this.dinoImg && this.dinoImg.complete) {
          this.ctx.drawImage(this.dinoImg, dinoX, dinoY, dinoWidth, dinoHeight);
        } else {
          this.ctx.fillStyle = 'green';
          this.ctx.fillRect(dinoX, dinoY, dinoWidth, dinoHeight);
          console.warn('Dino image not loaded, using fallback');
        }

        const heroX = this.hero.x * this.canvasWidth;
        const heroY = this.hero.y * this.canvasHeight;
        const heroWidth = this.hero.width * this.canvasWidth;
        const heroHeight = this.hero.height * this.canvasHeight;

        if (this.hero.visible) {
          if (this.heroImg && this.heroImg.complete) {
            this.ctx.drawImage(this.heroImg, heroX, heroY, heroWidth, heroHeight);
          } else {
            this.ctx.fillStyle = 'blue';
            this.ctx.fillRect(heroX, heroY, heroWidth, heroHeight);
            console.warn('Hero image not loaded, using fallback');
          }
        }

        const now = Date.now();
        const baseInterval = 2500;
        const minInterval = 1000;
        const obstacleInterval = Math.max(minInterval, baseInterval - Math.floor(this.score / 10) * 50);
        const obstacleProbability = Math.min(0.05, 0.01 + this.score * 0.0002);

        if (!this.superMode && now - this.lastObstacleTime > obstacleInterval && Math.random() < obstacleProbability) {
          const baseSpeed = 0.004;
          const speedIncrease = Math.floor(this.score / 100) * 0.0005;
          const obstacleSpeed = Math.min(baseSpeed + speedIncrease, 0.01);

          this.obstacles.push({
            x: 1,
            y: 0.75,
            width: 0.05,
            height: 0.1,
            speed: obstacleSpeed,
            dx: 0,
            dy: 0
          });
          this.lastObstacleTime = now;
        }

        this.obstacles = this.obstacles.filter(obs => obs.x > -obs.width && obs.x < 1 + obs.width && obs.y > -obs.height);
        this.obstacles.forEach(obs => {
          if (this.superMode) {
            obs.x += obs.dx;
            obs.y += obs.dy;
            obs.dy += 0.001;
            if (obs.y < 0) obs.y = 0;
          } else {
            obs.x -= obs.speed;
          }

          const obsX = obs.x * this.canvasWidth;
          const obsY = obs.y * this.canvasHeight;
          const obsWidth = obs.width * this.canvasWidth;
          const obsHeight = obs.height * this.canvasHeight;

          if (this.peachImg && this.peachImg.complete) {
            this.ctx.drawImage(this.peachImg, obsX, obsY, obsWidth, obsHeight);
          } else {
            this.ctx.fillStyle = '#ff9999';
            this.ctx.beginPath();
            this.ctx.arc(obsX + obsWidth / 2, obsY + obsHeight / 2, obsWidth / 2, 0, Math.PI * 2);
            this.ctx.fill();
            console.warn('Peach image not loaded, using fallback');
          }

          if (!this.superMode && this.checkCollision(obs)) {
            this.gameOver();
            return;
          }
        });

        this.score += 0.05;

        if (this.gameRunning) {
          this.animationFrameId = requestAnimationFrame(this.gameLoop);
        }
      } catch (error) {
        console.error('Error in game loop:', error);
        this.gameOver();
      }
    },
    checkCollision(obs) {
      return (
        this.dino.x < obs.x + obs.width &&
        this.dino.x + this.dino.width > obs.x &&
        this.dino.y < obs.y + obs.height &&
        this.dino.y + this.dino.height > obs.y
      );
    },
    gameOver() {
      this.gameRunning = false;
      this.hero.visible = false;
      cancelAnimationFrame(this.animationFrameId);
      if (Math.floor(this.score) > this.bestScore) {
        this.bestScore = Math.floor(this.score);
        localStorage.setItem('bestScore', this.bestScore);
      }
    },
    resetGame() {
      this.gameOver();
      this.score = 0;
      this.obstacles = [];
      this.dino.y = 0.75;
      this.dino.dy = 0;
      this.dino.jumpCount = 0;
      this.superMode = false;
      this.hero.visible = false;
      this.startGame();
    },
    closeDialog() {
      this.showDialog = false;
      this.gameOver();
    },
    activateSuperMode() {
      if (this.superModeCount <= 0) return;

      this.superMode = true;
      this.superModeCount -= 1;
      this.hero.visible = true;

      this.obstacles.forEach(obs => {
        const direction = obs.x < this.hero.x ? -1 : 1;
        obs.dx = direction * (Math.random() * 0.01 + 0.005);
        obs.dy = -(Math.random() * 0.01 + 0.005);
      });

      this.superModeTimeout = setTimeout(() => {
        this.superMode = false;
        this.hero.visible = false;
        this.obstacles = [];
      }, 15000);
    },
    drawBackground() {
      this.ctx.fillStyle = '#e0e0e0';
      this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);

      this.ctx.font = `${this.canvasWidth * 0.06}px Arial`;
      this.ctx.fillStyle = 'rgba(0, 0, 0, 0.2)';
      this.ctx.textAlign = 'center';
      this.ctx.fillText('Hesabix', this.canvas.width / 2, this.canvas.height * 0.2);
    }
  }
};
</script>

<style scoped>
canvas {
  border: 1px solid #ccc;
  display: block;
  width: 100%;
}
</style>