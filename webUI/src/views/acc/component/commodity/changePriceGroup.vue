<script lang="ts">
import axios from "axios";
import Swal from "sweetalert2";
import { Money3 } from "v-money3";
import { defineComponent } from 'vue'
// import the styles
import 'vue3-treeselect/dist/vue3-treeselect.css'
export default defineComponent({
    name: "changePriceGroup",
    props: {
        items: {
            type: Array
        },
        btn: {
            default: true,
            type: Boolean
        },
        windowsState: Object
    },
    components: {
        Money3
    },
    data: () => {
        return {
            dialog: false,
            loading: false,
            priceType: 'sell',
            priceTypes: [
                { code: 'sell', label: 'قیمت فروش' },
                { code: 'buy', label: 'قیمت خرید' }
            ],
            changeType: 'percent',
            changeTypes: [
                { code: 'percent', label: 'درصدی' },
                { code: 'much', label: 'مقداری' }
            ],
            changeArrow: 'up',
            changeArrows: [
                { code: 'up', label: 'افزایش' },
                { code: 'down', label: 'کاهش' }
            ],
            data: {
                name: '',
                percent: 1,
                much: 0,
            },
            currencyConfig: {
                masked: false,
                prefix: '',
                suffix: 'ریال',
                thousands: ',',
                decimal: '.',
                precision: 0,
                disableNegative: false,
                disabled: false,
                min: 0,
                max: null,
                allowBlank: false,
                minimumNumberOfCharacters: 0,
                shouldRound: true,
                focusOnRight: false,
            },
        }
    },
    methods: {
        showDialog() {
            if (this.$props.items?.length === 0) {
                Swal.fire({
                    text: 'هیچ کالایی انتخاب نشده است!',
                    icon: 'error',
                    confirmButtonText: 'قبول'
                });
            }
            else {
                this.dialog = true;
            }
        },
        save() {
            this.loading = true;
            axios.post('/api/commodity/pricegroup/update', {
                changeType: this.changeType,
                priceType: this.priceType,
                arrow: this.changeArrow,
                percent: this.data.percent,
                much: this.data.much,
                items: this.$props.items,
            }).then((response) => {
                this.loading = false;
                Swal.fire({
                    text: 'قیمت‌ها به روز رسانی شد.',
                    icon: 'success',
                    confirmButtonText: 'قبول'
                }).then(() => {
                    this.dialog = false;
                    this.$props.windowsState.submited = true;
                });
            });
        }
    },
    mounted() {
    },
})
</script>

<template>
    <v-tooltip :text="$t('dialog.change_price_group')" location="bottom">
        <template v-slot:activator="{ props }">
            <v-btn v-bind="props" icon="mdi-format-list-group" color="primary" @click="showDialog()"></v-btn>
        </template>
    </v-tooltip>
    <v-dialog v-model="dialog" transition="dialog-bottom-transition">
        <v-card class="bg-white" :loading="loading">
            <v-toolbar class="" color="toolbar" :title="$t('dialog.change_price_group')">
                <template v-slot:prepend>
                    <v-tooltip :text="$t('dialog.back')" location="bottom">
                        <template v-slot:activator="{ props }">
                            <v-btn icon="mdi-close" @click="dialog = false"></v-btn>
                        </template>
                    </v-tooltip>
                </template>
                <v-spacer></v-spacer>
                <v-btn :loading="loading" @click="save()" icon="" color="green">
                    <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
                    <v-icon icon="mdi-content-save"></v-icon>
                </v-btn>
            </v-toolbar>
            <v-card-text>
                <p class="text-danger">
                    {{ $t('dialog.items_changing', { msg: $props.items?.length }) }}
                </p>
                <v-row>
                    <v-col cols="12" sm="12" md="4">
                        <v-select :label="$t('dialog.price_type')" v-model="priceType" :items="priceTypes"
                            item-title="label" item-value="code" variant="outlined"></v-select>
                    </v-col>
                    <v-col cols="12" sm="12" md="4">
                        <v-select :label="$t('dialog.price_change_type')" v-model="changeType" :items="changeTypes"
                            item-title="label" item-value="code" variant="outlined"></v-select>
                    </v-col>
                    <v-col cols="12" sm="12" md="4">
                        <v-select :label="$t('dialog.price_change_type')" v-model="changeArrow" :items="changeArrows"
                            item-title="label" item-value="code" variant="outlined"></v-select>
                    </v-col>
                    <v-col cols="12" sm="12" md="4" v-if="changeType == 'percent'">
                        <v-number-input :min="1" :label="$t('dialog.applay_percent')" :hideInput="false" :inset="false"
                            v-model="data.percent" variant="outlined"></v-number-input>
                    </v-col>
                    <v-col cols="12" sm="12" md="4" v-if="changeType == 'much'">
                        <v-number-input :min="1" :label="$t('dialog.applay_much')" :hideInput="false" :inset="false"
                            v-model="data.much" variant="outlined"></v-number-input>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<style scoped></style>