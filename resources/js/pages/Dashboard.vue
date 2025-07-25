<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';


const countries = ref<{ label: string; code: string }[]>([]);
const selectedCountryCode = ref('');
const selectedDataAmount = ref('');
const selectedDays = ref('');

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const plans = ref<any[]>([]);
const usdEurRate = ref<number | null>(null);
const availableDataAmounts = ref<string[]>([]);
const availableDays = ref<string[]>([]);

const cart = ref<Record<number, any>>({});

const addToCart = (plan: any) => {
    if (!cart.value[plan.id]) {
        cart.value[plan.id] = { ...plan, quantity: 1 };
    } else {
        cart.value[plan.id].quantity++;
    }
};

const removeFromCart = (plan: any) => {
    if (cart.value[plan.id]?.quantity > 1) {
        cart.value[plan.id].quantity--;
    } else {
        delete cart.value[plan.id];
    }
};

const clearCart = () => {
    cart.value = {};
};

const totalItems = computed(() => Object.values(cart.value).reduce((acc: number, item: any) => acc + item.quantity, 0));

const totalAmount = computed(() =>
    Object.values(cart.value)
        .reduce((acc: number, item: any) => acc + item.price * item.quantity, 0)
        .toFixed(2),
);

const goToPayment = () => {
    if (totalItems.value > 0) {
        router.visit('/payment', {
            method: 'post',
            data: {
                cart: cart.value,
                totalItems: totalItems.value,
                totalAmount: totalAmount.value,
            },
        });
    }
};


// Foknsiyonlar

onMounted(async () => {
    try {
        const res = await axios.get('/api/get/countries');
        countries.value = res.data.data
            .map((item: any) => ({
                label: item.ulkeAd,
                code: item.ulkeKodu,
            }))
            .sort((a, b) => a.label.localeCompare(b.label));

        if (countries.value.length > 0) {
            selectedCountryCode.value = countries.value[0].code;
        }
    } catch (err) {
        console.error('Failed to fetch countries', err);
    }
});

watch([selectedCountryCode, selectedDataAmount, selectedDays], async ([newCode, newData, newDays]) => {
    if (!newCode) return;

    try {
        const res = await axios.get(`/api/get/country/coverages/${newCode}`);
        const allPlans = res.data.coverages.map((item: any) => ({
            id: item.id,
            country: item.coverage,
            api_id: item.api_id,
            data: `${parseFloat(item.data_amount).toFixed(0)} GB`,
            days: `${item.validity_period} Gün`,
            price: parseFloat(item.amount),
        }));

        availableDataAmounts.value = [...new Set(allPlans.map((plan: any) => plan.data))].sort(
            (a, b) => parseFloat(a) - parseFloat(b),
        );
        availableDays.value = [...new Set(allPlans.map((plan: any) => plan.days))].sort(
            (a, b) => parseFloat(a) - parseFloat(b),
        );

        let filteredPlans = allPlans;
        if (newData) {
            filteredPlans = filteredPlans.filter((plan: any) => plan.data === newData);
        }
        if (newDays) {
            filteredPlans = filteredPlans.filter((plan: any) => plan.days === newDays);
        }

        plans.value = filteredPlans;
        usdEurRate.value = res.data.usdEurRate;
    } catch (err) {
        console.error('Coverages fetch failed:', err);
        plans.value = [];
    }
}, { immediate: true });




</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">

                <div class="flex flex-col gap-4 p-6 md:flex-row">

                    <div class="w-full space-y-4 md:w-2/3">

                        <div class="mb-2 flex gap-2">
                            <select v-model="selectedCountryCode" class="rounded border px-2 py-1">
                                <option v-for="country in countries" :key="country.code" :value="country.code">
                                    {{ country.label }}
                                </option>
                            </select>

                            <select v-model="selectedDataAmount" class="rounded border px-2 py-1">
                                <option value="">Tüm Paketler</option>
                                <option v-for="data in availableDataAmounts" :key="data" :value="data">
                                    {{ data }}
                                </option>
                            </select>
                            <select v-model="selectedDays" class="rounded border px-2 py-1">
                                <option value="">Tüm Günler</option>
                                <option v-for="days in availableDays" :key="days" :value="days">
                                    {{ days }}
                                </option>
                            </select>
                        </div>

                        <div v-for="plan in plans" :key="plan.id" class="flex items-center justify-between rounded border p-4">
                            <div class="flex items-center gap-4">
                                <div>
                                    <div class="font-bold">{{ plan.country }}</div>
                                    <div class="text-sm text-gray-500">{{ plan.data }} – {{ plan.days }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="text-lg font-bold">${{ plan.price }}</div>
                                <button @click="removeFromCart(plan)" class="rounded border px-2 py-1">−</button>
                                <div>{{ cart[plan.id]?.quantity || 0 }}</div>
                                <button @click="addToCart(plan)" class="rounded border px-2 py-1">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="w-full space-y-2 rounded border p-4 md:w-1/3">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold">Seçilen Planlar</h3>
                            <button @click="clearCart" class="text-sm text-red-600">Sepeti Temizle</button>
                        </div>

                        <div v-if="totalItems === 0" class="text-gray-500">Sepetiniz boş</div>

                        <ul v-else class="space-y-2">
                            <li v-for="item in Object.values(cart)" :key="item.id" class="flex justify-between">
                                <div>{{ item.country }} {{ item.data }} x{{ item.quantity }}</div>
                                <div>${{ (item.price * item.quantity).toFixed(2) }}</div>
                            </li>
                        </ul>

                        <div class="mt-2 border-t pt-2">
                            <div class="flex justify-between font-bold">
                                <span>Toplam Ürün:</span>
                                <span>{{ totalItems }} adet</span>
                            </div>
                            <div class="flex justify-between font-bold text-green-600">
                                <span>Toplam Tutar:</span>
                                <span>${{ totalAmount }}</span>
                            </div>
                            <button @click="goToPayment" class="mt-3 w-full rounded bg-green-500 py-2 font-bold text-white hover:bg-green-600">
                                Satın Al ({{ totalItems }} Plan)
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
