<template>
    <div>
        <v-toolbar flat>
            <v-toolbar-title>{{ $t('updateSoftware.title') }}</v-toolbar-title>
        </v-toolbar>

        <v-card flat>
            <v-tabs v-model="activeTab">
                <v-tab>{{ $t('updateSoftware.infoTab') }}</v-tab>
                <v-tab>{{ $t('updateSoftware.logsTab') }}</v-tab>
                <v-tab>{{ $t('updateSoftware.updateTab') }}</v-tab>
            </v-tabs>

            <v-window v-model="activeTab">
                <!-- تب اطلاعات (بدون تغییر) -->
                <v-window-item>
                    <v-card flat class="mt-1">
                        <v-card-text>
                            <p>{{ $t('updateSoftware.infoDescription') }}</p>
                            <p>{{ $t('updateSoftware.safetyTips') }}</p>
                            <p><span class="font-weight-bold primary--text">{{ $t('updateSoftware.currentCommit') }}:</span> {{ currentCommit }}</p>
                            <p><span class="font-weight-bold primary--text">{{ $t('updateSoftware.targetCommit') }}:</span> {{ targetCommit }}</p>
                            <v-divider class="my-2"></v-divider>
                            <p>{{ $t('updateSoftware.systemInfoTitle') }}</p>
                            <p><span class="font-weight-bold primary--text">{{ $t('updateSoftware.osName') }}:</span> {{ systemInfo.osName }}</p>
                            <p><span class="font-weight-bold primary--text">{{ $t('updateSoftware.osRelease') }}:</span> {{ systemInfo.osRelease }}</p>
                            <p><span class="font-weight-bold primary--text">{{ $t('updateSoftware.osVersion') }}:</span> {{ systemInfo.osVersion }}</p>
                            <p><span class="font-weight-bold primary--text">{{ $t('updateSoftware.osMachine') }}:</span> {{ systemInfo.osMachine }}</p>
                            <p><span class="font-weight-bold primary--text">{{ $t('updateSoftware.cpuInfo') }}:</span> {{ systemInfo.cpuInfo }}</p>
                            <p><span class="font-weight-bold primary--text">{{ $t('updateSoftware.distroName') }}:</span> {{ systemInfo.distroName }}</p>
                            <p><span class="font-weight-bold primary--text">{{ $t('updateSoftware.distroVersion') }}:</span> {{ systemInfo.distroVersion }}</p>
                            <p><span class="font-weight-bold primary--text">{{ $t('updateSoftware.webServer') }}:</span> {{ systemInfo.webServer }}</p>
                            <p><span class="font-weight-bold primary--text">{{ $t('updateSoftware.dbName') }}:</span> {{ systemInfo.dbName }}</p>
                            <p><span class="font-weight-bold primary--text">{{ $t('updateSoftware.dbVersion') }}:</span> {{ systemInfo.dbVersion }}</p>
                            <p><span class="font-weight-bold primary--text">{{ $t('updateSoftware.currentEnv') }}:</span> {{ selectedEnv }}</p>
                        </v-card-text>
                    </v-card>
                </v-window-item>

                <!-- تب لاگ‌ها -->
                <v-window-item>
                    <v-card flat>
                        <v-card-text>
                            <div class="system-logs-container">
                                <v-toolbar flat color="grey-darken-4" density="compact">
                                    <v-spacer></v-spacer>
                                    <v-btn icon size="small" @click="copyLogsToClipboard" color="grey-lighten-1">
                                        <v-icon>mdi-content-copy</v-icon>
                                    </v-btn>
                                </v-toolbar>
                                <pre class="system-logs" v-html="formattedLogs"></pre>
                            </div>
                            <v-btn
                                color="primary"
                                @click="refreshLogs"
                                :loading="isLoadingLogs"
                                class="mt-2 mr-2"
                                size="small"
                            >
                                {{ $t('updateSoftware.refreshLogs') }}
                            </v-btn>
                            <v-btn
                                color="error"
                                @click="clearLogs"
                                :loading="isClearingLogs"
                                class="mt-2"
                                size="small"
                            >
                                {{ $t('updateSoftware.clearLogs') }}
                            </v-btn>
                        </v-card-text>
                    </v-card>
                </v-window-item>

                <!-- تب به‌روزرسانی -->
                <v-window-item>
                    <v-card flat>
                        <v-card-text>
                            <v-row justify="end" class="mb-4">
                                <v-col cols="auto">
                                    <v-btn-group size="small">
                                        <v-btn :color="buttonColor" :loading="isUpdating" 
                                            :disabled="isUpdating || status === 'running'"
                                            @click="startUpdate">
                                            {{ buttonText }}
                                        </v-btn>
                                        <v-btn color="warning" :loading="isClearingCache" 
                                            :disabled="isUpdating || isClearingCache"
                                            @click="clearCache">
                                            {{ $t('updateSoftware.clearCacheButton') }}
                                        </v-btn>
                                        <v-btn color="info" :disabled="isUpdating || isChangingEnv" 
                                            @click="openEnvDialog">
                                            {{ $t('updateSoftware.changeEnvButton') }}
                                        </v-btn>
                                    </v-btn-group>
                                </v-col>
                            </v-row>

                            <v-card v-if="showOutput || output.trim() !== ''" class="mt-1">
                                <v-toolbar flat color="white" density="compact">
                                    <v-toolbar-title>{{ $t('updateSoftware.progressTitle') }}</v-toolbar-title>
                                    <v-spacer></v-spacer>
                                    <v-btn icon size="small" @click="copyToClipboard">
                                        <v-icon>mdi-content-copy</v-icon>
                                    </v-btn>
                                </v-toolbar>
                                <v-card-text>
                                    <pre class="output-pre" ref="outputPre">{{ output }}</pre>
                                </v-card-text>
                            </v-card>
                        </v-card-text>
                    </v-card>
                </v-window-item>
            </v-window>
        </v-card>

        <v-dialog v-model="showEnvDialog" max-width="400">
            <v-card>
                <v-card-title class="info--text">تغییر حالت سیستم</v-card-title>
                <v-card-text>
                    <v-select
                        v-model="tempSelectedEnv"
                        :items="envOptions"
                        label="حالت سیستم"
                        outlined
                        dense
                    ></v-select>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="grey" text @click="showEnvDialog = false">لغو</v-btn>
                    <v-btn color="info" :loading="isChangingEnv" @click="changeEnvironment">تأیید</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="showResultDialog" max-width="400">
            <v-card>
                <v-card-title :class="dialogColor + '--text'">
                    {{ dialogTitle }}
                </v-card-title>
                <v-card-text>
                    {{ dialogMessage }}
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" text @click="showResultDialog = false">
                        {{ $t('updateSoftware.closeDialog') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';

export default {
    name: 'UpdateSoftware',
    setup() {
        axios.defaults.timeout = 3600000; // تنظیم تایم‌اوت پیش‌فرض Axios به 1 ساعت

        const isUpdating = ref(false);
        const isClearingCache = ref(false);
        const isChangingEnv = ref(false);
        const showOutput = ref(false);
        const showEnvDialog = ref(false);
        const output = ref('');
        const buttonText = ref('');
        const buttonColor = ref('primary');
        const status = ref('idle');
        const updateUuid = ref(null);
        const outputPre = ref(null);
        const currentCommit = ref('unknown');
        const targetCommit = ref('unknown');
        const systemInfo = ref({
            osName: 'unknown',
            osRelease: 'unknown',
            osVersion: 'unknown',
            osMachine: 'unknown',
            cpuInfo: 'unknown',
            distroName: 'unknown',
            distroVersion: 'unknown',
            webServer: 'unknown',
            dbName: 'unknown',
            dbVersion: 'unknown',
        });
        const showResultDialog = ref(false);
        const dialogTitle = ref('');
        const dialogMessage = ref('');
        const dialogColor = ref('');
        const selectedEnv = ref('prod');
        const tempSelectedEnv = ref('prod');
        const envOptions = ref(['dev', 'prod']);
        const activeTab = ref(0);
        const systemLogs = ref('');
        const isLoadingLogs = ref(false);
        const isClearingLogs = ref(false);
        const isPolling = ref(false);

        return {
            isUpdating,
            isClearingCache,
            isChangingEnv,
            showOutput,
            showEnvDialog,
            output,
            buttonText,
            buttonColor,
            status,
            updateUuid,
            outputPre,
            currentCommit,
            targetCommit,
            systemInfo,
            showResultDialog,
            dialogTitle,
            dialogMessage,
            dialogColor,
            selectedEnv,
            tempSelectedEnv,
            envOptions,
            activeTab,
            systemLogs,
            isLoadingLogs,
            isClearingLogs,
            isPolling,
        };
    },
    computed: {
        formattedLogs() {
            if (!this.systemLogs) return '';

            const lines = this.systemLogs.split('\n');
            return lines.map(line => {
                const match = line.match(/\[([^\]]+)\] (\w+)\.(\w+): (.+?) (\[.*\]) (\[.*\])/);
                if (!match) return `<span class="log-line">${line}</span>`;

                const [_, timestamp, channel, level, message, context, extra] = match;

                let levelClass = '';
                switch (level.toUpperCase()) {
                    case 'INFO':
                        levelClass = 'log-info';
                        break;
                    case 'DEBUG':
                        levelClass = 'log-debug';
                        break;
                    case 'ERROR':
                        levelClass = 'log-error';
                        break;
                    default:
                        levelClass = 'log-default';
                }

                return `
                    <span class="log-line" dir="ltr">
                        <span class="log-timestamp">[${timestamp}]</span>
                        <span class="log-channel">${channel}</span>.<span class="${levelClass}">${level}</span>:
                        <span class="log-message">${message}</span>
                        <span class="log-context">${context}</span>
                        <span class="log-extra">${extra}</span>
                    </span>
                `;
            }).join('<br>');
        }
    },
    methods: {
        async startUpdate() {
            if (this.isUpdating || this.status === 'running') return;

            this.isUpdating = true;
            this.buttonText = this.$t('updateSoftware.updatingButton');
            this.buttonColor = 'primary';
            this.showOutput = true;
            this.output = this.$t('updateSoftware.startingMessage') + '\n';

            try {
                const response = await axios.post('/api/admin/updatecore/run', {}, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    timeout: 3600000 // تایم‌اوت 1 ساعت
                });

                if (response.data.status === 'started') {
                    this.updateUuid = response.data.uuid;
                    this.output += this.$t('updateSoftware.startedMessage') + '\n';
                    this.startLogStream();
                } else if (response.data.status === 'error') {
                    this.output += '\n' + this.$t('updateSoftware.errorPrefix') + response.data.message;
                    this.buttonColor = 'error';
                    this.buttonText = this.$t('updateSoftware.failedButton');
                    this.isUpdating = false;
                    this.showResultDialog = true;
                    this.dialogTitle = this.$t('updateSoftware.dialogErrorTitle');
                    this.dialogMessage = this.$t('updateSoftware.dialogErrorSimpleMessage');
                    this.dialogColor = 'error';
                }
            } catch (error) {
                this.output += '\n' + this.$t('updateSoftware.errorPrefix') + (error.response?.data?.message || error.message);
                this.buttonColor = 'error';
                this.buttonText = this.$t('updateSoftware.failedButton');
                this.isUpdating = false;
                this.showResultDialog = true;
                this.dialogTitle = this.$t('updateSoftware.dialogErrorTitle');
                this.dialogMessage = this.$t('updateSoftware.dialogErrorSimpleMessage');
                this.dialogColor = 'error';
            }
        },
        async startLogStream() {
            const pollInterval = 1000;
            let isPolling = true;
            this.isUpdating = true;

            const pollStream = async () => {
                if (!isPolling) return;

                try {
                    const response = await axios.get(`/api/admin/updatecore/stream`, {
                        params: { uuid: this.updateUuid },
                        timeout: 3600000 // تایم‌اوت 1 ساعت
                    });

                    const data = response.data;
                    if (typeof data === 'string' && data.startsWith('data: ')) {
                        try {
                            const jsonStr = data.substring(data.indexOf('{'));
                            const parsedData = JSON.parse(jsonStr);
                            
                            if (parsedData.output && parsedData.output !== this.output) {
                                const formattedOutput = parsedData.output
                                    .split('\n')
                                    .filter(line => line.trim())
                                    .map(line => {
                                        if (line.includes('Installing frontend dependencies') || line.includes('Building frontend')) {
                                            return `<span class="log-info">${line}</span>`;
                                        } else if (line.includes('ERROR')) {
                                            return `<span class="log-error">${line}</span>`;
                                        } else if (line.includes('DEBUG')) {
                                            return `<span class="log-debug">${line}</span>`;
                                        }
                                        return line;
                                    })
                                    .join('\n');
                                
                                this.output = formattedOutput;
                            }
                            
                            this.status = parsedData.status;
                        } catch (parseError) {
                            console.error('خطا در پردازش پاسخ:', parseError);
                        }
                    }

                    await nextTick();
                    if (this.$refs.outputPre) {
                        this.$refs.outputPre.scrollTop = this.$refs.outputPre.scrollHeight;
                    }

                    if (this.status === 'success' || this.status === 'error') {
                        isPolling = false;
                        this.isUpdating = false;
                        this.buttonText = this.status === 'success' 
                            ? this.$t('updateSoftware.completedButton') 
                            : this.$t('updateSoftware.failedButton');
                        this.buttonColor = this.status === 'success' ? 'success' : 'error';
                        this.showResultDialog = true;
                        this.dialogTitle = this.status === 'success' 
                            ? this.$t('updateSoftware.dialogSuccessTitle') 
                            : this.$t('updateSoftware.dialogErrorTitle');
                        this.dialogMessage = this.status === 'success' 
                            ? this.$t('updateSoftware.successMessage') 
                            : this.$t('updateSoftware.dialogErrorSimpleMessage');
                        this.dialogColor = this.status === 'success' ? 'success' : 'error';
                        return;
                    }

                    setTimeout(pollStream, pollInterval);

                } catch (error) {
                    console.error('خطا در دریافت جریان داده:', error);
                    isPolling = false;
                    this.output += '\n' + this.$t('updateSoftware.streamError');
                    this.isUpdating = false;
                    this.buttonColor = 'error';
                    this.buttonText = this.$t('updateSoftware.failedButton');
                }
            };

            pollStream();
            this.isPolling = isPolling;
        },
        async clearCache() {
            this.isClearingCache = true;
            this.showOutput = true;
            this.output = this.$t('updateSoftware.clearingCacheMessage') + '\n';

            try {
                const response = await axios.post('/api/admin/updatecore/clear-cache', {}, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    timeout: 3600000 // تایم‌اوت 1 ساعت
                });
                this.output += response.data.output || this.$t('updateSoftware.cacheClearedMessage') + '\n';
                this.showResultDialog = true;
                this.dialogTitle = this.$t('updateSoftware.dialogSuccessTitle');
                this.dialogMessage = this.$t('updateSoftware.cacheClearedMessage');
                this.dialogColor = 'success';
            } catch (error) {
                this.output += this.$t('updateSoftware.errorPrefix') + (error.response?.data?.message || error.message) + '\n';
                this.showResultDialog = true;
                this.dialogTitle = this.$t('updateSoftware.dialogErrorTitle');
                this.dialogMessage = this.$t('updateSoftware.dialogErrorSimpleMessage');
                this.dialogColor = 'error';
            } finally {
                this.isClearingCache = false;
            }
        },
        openEnvDialog() {
            this.tempSelectedEnv = this.selectedEnv;
            this.showEnvDialog = true;
        },
        async changeEnvironment() {
            this.isChangingEnv = true;
            this.showOutput = true;
            this.output = 'در حال تغییر حالت سیستم...\n';

            if (!this.tempSelectedEnv || !['dev', 'prod'].includes(this.tempSelectedEnv)) {
                this.output += 'خطا: لطفاً یک حالت معتبر (dev یا prod) انتخاب کنید\n';
                this.isChangingEnv = false;
                this.showResultDialog = true;
                this.dialogTitle = 'خطا';
                this.dialogMessage = 'لطفاً یک حالت معتبر انتخاب کنید';
                this.dialogColor = 'error';
                return;
            }

            try {
                const response = await axios.post('/api/admin/updatecore/change-env', { env: this.tempSelectedEnv }, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    timeout: 3600000 // تایم‌اوت 1 ساعت
                });
                this.output += response.data.output || response.data.message + '\n';
                this.selectedEnv = this.tempSelectedEnv;
                this.showResultDialog = true;
                this.dialogTitle = 'موفقیت';
                this.dialogMessage = response.data.message;
                this.dialogColor = 'success';
            } catch (error) {
                this.output += 'خطا: ' + (error.response?.data?.message || error.message) + '\n';
                this.showResultDialog = true;
                this.dialogTitle = 'خطا';
                this.dialogMessage = 'خطایی در تغییر حالت رخ داد';
                this.dialogColor = 'error';
            } finally {
                this.isChangingEnv = false;
                this.showEnvDialog = false;
            }
        },
        async fetchCommits() {
            try {
                const response = await axios.get('/api/admin/updatecore/commits', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    timeout: 3600000 // تایم‌اوت 1 ساعت
                });
                this.currentCommit = response.data.currentCommit || 'unknown';
                this.targetCommit = response.data.targetCommit || 'unknown';
            } catch (error) {
                console.error('Failed to fetch commits:', error);
                this.currentCommit = 'خطا در دریافت';
                this.targetCommit = 'خطا در دریافت';
            }
        },
        async fetchSystemInfo() {
            try {
                const response = await axios.get('/api/admin/updatecore/system-info', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    timeout: 3600000 // تایم‌اوت 1 ساعت
                });
                this.systemInfo = {
                    osName: response.data.osName || 'unknown',
                    osRelease: response.data.osRelease || 'unknown',
                    osVersion: response.data.osVersion || 'unknown',
                    osMachine: response.data.osMachine || 'unknown',
                    cpuInfo: response.data.cpuInfo || 'unknown',
                    distroName: response.data.distroName || 'unknown',
                    distroVersion: response.data.distroVersion || 'unknown',
                    webServer: response.data.webServer || 'unknown',
                    dbName: response.data.dbName || 'unknown',
                    dbVersion: response.data.dbVersion || 'unknown',
                };
            } catch (error) {
                console.error('Failed to fetch system info:', error);
                this.systemInfo = {
                    osName: 'خطا در دریافت',
                    osRelease: 'خطا در دریافت',
                    osVersion: 'خطا در دریافت',
                    osMachine: 'خطا در دریافت',
                    cpuInfo: 'خطا در دریافت',
                    distroName: 'خطا در دریافت',
                    distroVersion: 'خطا در دریافت',
                    webServer: 'خطا در دریافت',
                    dbName: 'خطا در دریافت',
                    dbVersion: 'خطا در دریافت',
                };
            }
        },
        async fetchCurrentEnv() {
            try {
                const response = await axios.get('/api/admin/updatecore/current-env', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    timeout: 3600000 // تایم‌اوت 1 ساعت
                });
                this.selectedEnv = response.data.env;
                this.tempSelectedEnv = response.data.env;
            } catch (error) {
                console.error('Failed to fetch current env:', error);
            }
        },
        copyToClipboard() {
            navigator.clipboard.writeText(this.output).then(() => {
                alert(this.$t('updateSoftware.copySuccess'));
            }).catch(err => {
                console.error('Failed to copy: ', err);
                alert(this.$t('updateSoftware.copyFailed'));
            });
        },
        async refreshLogs() {
            this.isLoadingLogs = true;
            tryِ try {
                const response = await axios.get('/api/admin/updatecore/system-logs', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    timeout: 3600000 // تایم‌اوت 1 ساعت
                });
                this.systemLogs = response.data.logs || response.data.message;
            } catch (error) {
                console.error('Failed to fetch system logs:', error);
                this.systemLogs = error.response?.data?.message || 'خطا در دریافت لاگ‌های سیستم';
            } finally {
                this.isLoadingLogs = false;
            }
        },
        async clearLogs() {
            this.isClearingLogs = true;
            try {
                const response = await axios.post('/api/admin/updatecore/clear-logs', {}, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    timeout: 3600000 // تایم‌اوت 1 ساعت
                });
                if (response.data.status === 'success') {
                    this.systemLogs = 'لاگ‌ها پاک شدند';
                    this.showResultDialog = true;
                    this.dialogTitle = 'موفقیت';
                    this.dialogMessage = response.data.message;
                    this.dialogColor = 'success';
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                console.error('Failed to clear logs:', error);
                this.systemLogs = error.response?.data?.message || 'خطا در پاک کردن لاگ‌ها';
                this.showResultDialog = true;
                this.dialogTitle = 'خطا';
                this.dialogMessage = error.response?.data?.message || 'خطایی رخ داد';
                this.dialogColor = 'error';
            } finally {
                this.isClearingLogs = false;
            }
        },
        copyLogsToClipboard() {
            const plainLogs = this.systemLogs.replace(/\[\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d+\+\d{2}:\d{2}\]/g, '\n[$&]')
                .replace(/\s+\[\]/g, ' []')
                .trim();
            
            navigator.clipboard.writeText(plainLogs).then(() => {
                this.showResultDialog = true;
                this.dialogTitle = this.$t('updateSoftware.dialogSuccessTitle');
                this.dialogMessage = this.$t('updateSoftware.copySuccess');
                this.dialogColor = 'success';
            }).catch(err => {
                console.error('Failed to copy logs:', err);
                this.showResultDialog = true;
                this.dialogTitle = this.$t('updateSoftware.dialogErrorTitle');
                this.dialogMessage = this.$t('updateSoftware.copyFailed');
                this.dialogColor = 'error';
            });
        },
    },
    mounted() {
        this.fetchCommits();
        this.fetchSystemInfo();
        this.fetchCurrentEnv();
        this.buttonText = this.$t('updateSoftware.startButton');
        this.refreshLogs();
    },
    beforeUnmount() {
        this.isPolling = false;
    },
};
</script>

<style scoped>
.output-pre {
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    line-height: 1.5;
    font-size: 14px;
    padding: 15px;
    background-color: #1e1e1e;
    color: #d4d4d4;
    white-space: pre-wrap;
    word-wrap: break-word;
    direction: ltr;
    text-align: left;
}

.system-logs-container {
    background-color: #1e1e1e;
    border: 1px solid #333;
    border-radius: 4px;
    margin-bottom: 16px;
}

.system-logs {
    max-height: 500px;
    overflow-y: auto;
    white-space: pre-wrap;
    direction: ltr;
    margin: 0;
    padding: 12px;
    font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace;
    font-size: 13px;
    line-height: 1.5;
    background-color: #1e1e1e;
    color: #d4d4d4;
}

.log-line {
    display: block;
    margin-bottom: 4px;
}

.log-timestamp {
    color: #569cd6;
    margin-right: 8px;
}

.log-channel {
    color: #4ec9b0;
    margin-right: 4px;
}

.log-info {
    color: #6a9955;
    font-weight: 500;
}

.log-debug {
    color: #9cdcfe;
}

.log-error {
    color: #f44747;
    font-weight: 500;
}

.log-default {
    color: #d4d4d4;
    margin: 0 4px;
}

.log-message {
    color: #ce9178;
    margin: 0 4px;
}

.log-context, .log-extra {
    color: #808080;
    font-style: italic;
    margin: 0 4px;
}

/* اسکرول‌بار سفارشی */
.system-logs::-webkit-scrollbar {
    width: 12px;
}

.system-logs::-webkit-scrollbar-track {
    background: #2d2d2d;
}

.system-logs::-webkit-scrollbar-thumb {
    background-color: #484848;
    border-radius: 6px;
    border: 3px solid #2d2d2d;
}

.system-logs::-webkit-scrollbar-thumb:hover {
    background-color: #555555;
}
</style>