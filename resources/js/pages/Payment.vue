<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

const page = usePage();
const props = page.props as {
    cart: Record<number, any>;
    totalItems: number;
    totalAmount: string;
};

const cardNumber = ref('');
const expiryDate = ref('');
const cvc = ref('');

const submitPayment = () => {
    alert('Payment processed (not implemented)');
};
</script>

<template>
    <Head title="Ödeme Sayfası" />

    <AppLayout>
        <div class="p-6 max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold mb-4">Ödeme</h1>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Selected Plans -->
                <div class="border p-4 rounded shadow">
                    <h2 class="text-lg font-semibold mb-2">Seçilen Planlar</h2>
                    <ul class="space-y-2">
                        <li v-for="item in Object.values(props.cart)" :key="item.id" class="flex justify-between">
                            <div>{{ item.country }} - {{ item.data }} - {{ item.days }}</div>
                            <div>x{{ item.quantity }} – ${{ (item.price * item.quantity).toFixed(2) }}</div>
                        </li>
                    </ul>
                    <div class="mt-4 border-t pt-2 font-bold flex justify-between">
                        <span>Toplam Tutar:</span>
                        <span class="text-green-600">${{ props.totalAmount }}</span>
                    </div>
                </div>

                <!-- Credit Card Form -->
                <div class="border p-4 rounded shadow space-y-4">
                    <h2 class="text-lg font-semibold">Kart Bilgileri</h2>
                    <div>
                        <label class="block text-sm">Kart Numarası</label>
                        <input v-model="cardNumber" type="text" placeholder="1234 5678 9012 3456"
                               class="w-full border rounded px-3 py-2 mt-1" />
                    </div>
                    <div class="flex gap-4">
                        <div class="w-1/2">
                            <label class="block text-sm">Son Kullanma Tarihi</label>
                            <input v-model="expiryDate" type="text" placeholder="MM/YY"
                                   class="w-full border rounded px-3 py-2 mt-1" />
                        </div>
                        <div class="w-1/2">
                            <label class="block text-sm">CVC</label>
                            <input v-model="cvc" type="text" placeholder="123"
                                   class="w-full border rounded px-3 py-2 mt-1" />
                        </div>
                    </div>
                    <button @click="submitPayment" class="w-full mt-4 bg-green-600 hover:bg-green-700 text-white py-2 rounded">
                        Ödemeyi Tamamla
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
