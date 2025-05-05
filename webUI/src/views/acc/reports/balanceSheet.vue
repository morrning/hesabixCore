<template>
    <div class="block block-content-full ">
        <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
            <h3 class="block-title text-primary-dark">
                <i class="mx-2 fa fa-table"></i>
                ترازنامه
            </h3>
        </div>
        <div class="block-content pt-1 pb-3 vl-parent">
            <loading color="blue" loader="dots" v-model:active="isLoading" :is-full-page="false" />

            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <div class="block block-rounded block-mode-loading-refresh">
                        <div class="block-header block-header-default bg-success text-light">
                            <h3 class="block-title">دارائی‌ها</h3>
                            <div class="block-options">

                            </div>
                        </div>
                        <div class="block-content p-0">
                            <table
                                class="table table-striped table-hover table-borderless table-vcenter fs-sm text-center">
                                <thead>
                                    <tr class="text-uppercase">
                                        <th>آیتم</th>
                                        <th>مبلغ</th>
                                        <th>وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">بانک‌ها</span>
                                        </td>
                                        <td>
                                            <span class="fs-sm text-muted">{{
                this.$filters.formatNumber(Math.abs(YearInfo.banks.bs -
                    YearInfo.banks.bd)) }}</span>
                                        </td>
                                        <td>
                                            <span v-if="(YearInfo.banks.bs - YearInfo.banks.bd) > 0"
                                                class="fw-semibold text-warning">بدهکار</span>
                                            <span v-if="(YearInfo.banks.bs - YearInfo.banks.bd) < 0"
                                                class="fw-semibold text-success">بستانکار</span>
                                            <span v-if="(YearInfo.banks.bs - YearInfo.banks.bd) == 0"
                                                class="fw-semibold text-dark">تسویه</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">صندوق‌ها</span>
                                        </td>
                                        <td>
                                            <span class="fs-sm text-muted">{{
                this.$filters.formatNumber(Math.abs(YearInfo.cashdesks.bd -
                    YearInfo.cashdesks.bs)) }}</span>
                                        </td>
                                        <td>
                                            <span v-if="(YearInfo.cashdesks.bs - YearInfo.cashdesks.bd) > 0"
                                                class="fw-semibold text-warning">بدهکار</span>
                                            <span v-if="(YearInfo.cashdesks.bs - YearInfo.cashdesks.bd) < 0"
                                                class="fw-semibold text-success">بستانکار</span>
                                            <span v-if="(YearInfo.cashdesks.bs - YearInfo.cashdesks.bd) == 0"
                                                class="fw-semibold text-dark">تسویه</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">تنخواه گردان‌ها</span>
                                        </td>
                                        <td>
                                            <span class="fs-sm text-muted">{{
                this.$filters.formatNumber(Math.abs(YearInfo.salarys.bd -
                    YearInfo.salarys.bs)) }}</span>
                                        </td>
                                        <td>
                                            <span v-if="(YearInfo.salarys.bs - YearInfo.salarys.bd) > 0"
                                                class="fw-semibold text-warning">بدهکار</span>
                                            <span v-if="(YearInfo.salarys.bs - YearInfo.salarys.bd) < 0"
                                                class="fw-semibold text-success">بستانکار</span>
                                            <span v-if="(YearInfo.salarys.bs - YearInfo.salarys.bd) == 0"
                                                class="fw-semibold text-dark">تسویه</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">بدهکاران</span>
                                        </td>
                                        <td>
                                            <span v-if="YearInfo.persons.bs -
                                                YearInfo.persons.bd < 0" class="fs-sm text-muted">{{
                                                this.$filters.formatNumber(Math.abs(YearInfo.persons.bs -
                                                YearInfo.persons.bd)) }}</span>
                                            <span v-else class="text-muted">0</span>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import Loading from 'vue-loading-overlay';

export default {
    name: "balanceSheet",
    components: {
        Loading
    },
    data: () => {
        return {
            isLoading: true,
            YearInfo: {
                banks: {
                    bs: 0,
                    bd: 0
                },
                cashdesks: {
                    bs: 0,
                    bd: 0
                },
                salarys: {
                    bs: 0,
                    bd: 0
                },
                persons: {
                    bs: 0,
                    bd: 0
                },
                year: {
                    label: ''
                }
            }
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        loadData() {
            axios.post('/api/year/lastyear/info').then((Response) => {
                this.YearInfo = Response.data;
                this.isLoading = false;
            })
        }
    }

}
</script>

<style scoped></style>