<template>
    <v-toolbar color="toolbar" :title="$t('drawer.print_queue')">
        <template v-slot:prepend>
            <v-tooltip :text="$t('dialog.back')" location="bottom">
                <template v-slot:activator="{ props }">
                    <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
                        icon="mdi-arrow-right" />
                </template>
            </v-tooltip>
        </template>
        <v-spacer></v-spacer>
        <v-tooltip :text="$t('dialog.clean_queue')" location="bottom">
            <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-trash-can" color="danger" @click="cleanQueue()"></v-btn>
            </template>
        </v-tooltip>
    </v-toolbar>
    <v-text-field :loading="loading" color="green" class="mb-0 pt-0 rounded-0" hide-details="auto" density="compact"
        :placeholder="$t('dialog.search_txt')" v-model="searchValue" type="text" clearable>
        <template v-slot:prepend-inner>
            <v-tooltip location="bottom" :text="$t('dialog.search')">
                <template v-slot:activator="{ props }">
                    <v-icon v-bind="props" color="danger" icon="mdi-magnify"></v-icon>
                </template>
            </v-tooltip>
        </template>
    </v-text-field>
    <EasyDataTable table-class-name="customize-table" :table-class-name="tableClassName"
        v-model:items-selected="itemsSelected" multi-sort show-index alternating :search-value="searchValue"
        :headers="headers" :items="items" theme-color="#1d90ff" header-text-direction="center"
        body-text-direction="center" rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
        rowsOfPageSeparatorMessage="از" :loading="loading">
        <template #item-operation="{ code }">
            <v-menu>
                <template v-slot:activator="{ props }">
                    <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
                </template>
                <v-list>
                    <v-list-item class="text-dark" :title="$t('dialog.view')" @click="print(code)">
                        <template v-slot:prepend>
                            <v-icon color="green-darken-4" icon="mdi-eye"></v-icon>
                        </template>
                    </v-list-item>
                    <v-list-item class="text-dark" :title="$t('dialog.delete')" @click="deleteItem(code)">
                        <template v-slot:prepend>
                            <v-icon color="deep-orange-accent-4" icon="mdi-trash-can"></v-icon>
                        </template>
                    </v-list-item>
                </v-list>
            </v-menu>
        </template>
    </EasyDataTable>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";

import HelpBtn from "../component/helpBtn.vue";
import importExcel from "../component/importModal/person-import-excel.vue";

export default {
    name: "list",
    components: {
        HelpBtn,
        importExcel: importExcel
    },
    watch: {
        'importWindowsState.submited'(newValue, oldValue) {
            this.importWindowsState.submited = false;
            if (newValue) {
                this.loadData();
            }
        }
    },
    data: () => {
        return {
            importWindowsState: {
                submited: false
            },
            tableClassName: 'extable',
            searchValue: '',
            loading: true,
            orgItems: [],
            types: [],
            typesSelected: [],
            items: [],
            itemsSelected: [],
            headers: [
                { text: "عملیات", value: "operation" },
                { text: "کاربر", value: "submitter" },
                { text: "تاریخ ایجاد", value: "dateSubmit" },
                { text: "کد", value: "code" },
            ]
        }
    },
    methods: {
        loadData() {
            this.loading = true;
            axios.post('/api/printers/exist/' + this.$route.params.id)
                .then((response) => {
                    if (response.data.Success != true) {
                        this.$router.push({ 'name': 'acc_home' })
                    }
                });
            axios.post('/api/printers/queue/' + this.$route.params.id)
                .then((response) => {
                    if (response.data.Success == true) {
                        this.items = response.data.data;
                        this.loading = false;
                    }
                });
        },
        deleteItem(code) {
            Swal.fire({
                text: 'آیا برای حذف این برگه مطمئن هستید؟',
                showCancelButton: true,
                confirmButtonText: 'بله',
                cancelButtonText: `خیر`,
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    axios.post('/api/printers/queue_doc_delete/' + code).then((response) => {
                        if (response.data.Success == true) {
                            let index = 0;
                            for (let z = 0; z < this.items.length; z++) {
                                index++;
                                if (this.items[z]['code'] == code) {
                                    this.items.splice(index - 1, 1);
                                }
                            }
                            Swal.fire({
                                text: 'برگه با موفقیت حذف شد.',
                                icon: 'success',
                                confirmButtonText: 'قبول'
                            });
                        }
                    })
                }
            })
        },
        print(code) {
            window.open(this.$API_URL + '/front/print/' + code, '_blank', 'noreferrer');
        },
        cleanQueue() {
            Swal.fire({
                text: 'آیا برای حذف کل برگه‌ها مطمئن هستید؟',
                showCancelButton: true,
                confirmButtonText: 'بله',
                cancelButtonText: `خیر`,
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    axios.post('/api/printers/queue_doc_delete_all').then((response) => {
                        if (response.data.Success == true) {
                            this.items = [];
                            Swal.fire({
                                text: 'صف چاپ پاکسازی شد.',
                                icon: 'success',
                                confirmButtonText: 'قبول'
                            });
                        }
                    })
                }
            })
        }
    },
    beforeMount() {
        this.loadData();
    }
}
</script>

<style scoped></style>