<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
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
const email = ref('example@mail.com');
const phoneNumber = ref('5554443322');
const pendingEsimIds = ref<number[]>([]);

const onlyDigits = (event: Event, field: 'cardNumber' | 'cvc', maxLength: number) => {
    const input = event.target as HTMLInputElement;
    input.value = input.value.replace(/\D/g, '').slice(0, maxLength);
    if (field === 'cardNumber') cardNumber.value = input.value;
    if (field === 'cvc') cvc.value = input.value;
};


const formatExpiryDate = (event: Event) => {
    let input = (event.target as HTMLInputElement).value.replace(/[^\d]/g, '');
    if (input.length >= 3) {
        input = input.slice(0, 2) + '/' + input.slice(2, 4);
    }
    expiryDate.value = input.slice(0, 5);
};

const submitPayment = async () => {
    console.log('submitPayment triggered');
    console.log('Form Inputs:', {
        email: email.value,
        phoneNumber: phoneNumber.value,
        cardNumber: cardNumber.value,
        expiryDate: expiryDate.value,
        cvc: cvc.value,
    });


    if (!email.value || !phoneNumber.value || !cardNumber.value || !expiryDate.value || !cvc.value) {
        alert('Lütfen boş alanları doldurunuz.');
        return;
    }

    if (!/^\d{16}$/.test(cardNumber.value)) {
        alert('Kart numarası 16 haneli olmalıdır.');
        return;
    }

    if (!/^\d{3}$/.test(cvc.value)) {
        alert('CVC 3 haneli olmalıdır.');
        return;
    }

    if (!/^\d{2}\/\d{2}$/.test(expiryDate.value)) {
        alert('Son kullanma tarihi MM/YY formatında olmalıdır.');
        return;
    }

    try {
        const apiIds: string[] = [];
        Object.values(props.cart).forEach((item: any) => {
            for (let i = 0; i < item.quantity; i++) {
                if (Array.isArray(item.api_id)) {
                    item.api_id.forEach((id: any) => {
                        if (id) apiIds.push(String(id));
                    });
                } else if (item.api_id) {
                    apiIds.push(String(item.api_id));
                }
            }
        });

        if (!apiIds.length) {
            alert('Sepette geçerli eSIM ID bulunamadı.');
            return;
        }

        const postData = {
            api_id: apiIds,
            gsm_no: phoneNumber.value,
            email: email.value,
        };

        const response = await axios.post('/api/proxy/esim-create', postData);

        const soldEsimRaw = response.data.sold_esim;
        const soldEsimList = Array.isArray(soldEsimRaw) ? soldEsimRaw : soldEsimRaw ? [soldEsimRaw] : [];

        pendingEsimIds.value = soldEsimList.map((item: any) => item.id);

        if (!pendingEsimIds.value.length) {
            alert('API yanıtında eSIM ID bulunamadı.');
            return;
        }

        router.get(
            '/payment/verify',
            {
                pendingEsimIds: pendingEsimIds.value,
            },
            {
                onError: (error) => {
                    console.error('Navigation error:', error);
                    alert('Doğrulama sayfasına yönlendirme başarısız.');
                },
            },
        );
    } catch (error: any) {
        if (axios.isAxiosError(error)) {
            alert(`eSIM oluşturulurken hata oluştu: ${error.response?.data?.message || error.message}`);
        } else {
            alert('Beklenmeyen bir hata oluştu.');
        }
    }
};
</script>

<template>
    <Head title="Ödeme Sayfası" />

    <AppLayout>
        <div class="mx-auto max-w-4xl p-6">
            <h1 class="mb-4 text-2xl font-bold">Ödeme</h1>

            <div class="grid gap-8 md:grid-cols-2">

                <div class="rounded border p-4 shadow">
                    <h2 class="mb-2 text-lg font-semibold">Seçilen Planlar</h2>
                    <ul class="space-y-2">
                        <li v-for="item in Object.values(props.cart)" :key="item.id" class="flex justify-between">
                            <div>{{ item.country }} - {{ item.data }} - {{ item.days }} gün</div>
                            <div>x{{ item.quantity }} – ${{ (item.price * item.quantity).toFixed(2) }}</div>
                        </li>
                    </ul>
                    <div class="mt-4 flex justify-between border-t pt-2 font-bold">
                        <span>Toplam Tutar:</span>
                        <span class="text-green-600">${{ props.totalAmount }}</span>
                    </div>
                </div>


                <div class="space-y-4 rounded border p-4 shadow">
                    <h2 class="text-lg font-semibold">Kart Bilgileri</h2>

                    <div>
                        <label class="block text-sm">E-Posta</label>
                        <input v-model="email" type="email" class="mt-1 w-full rounded border px-3 py-2" readonly />
                    </div>

                    <div>
                        <label class="block text-sm">Telefon</label>
                        <input v-model="phoneNumber" type="tel" class="mt-1 w-full rounded border px-3 py-2" readonly />
                    </div>

                    <div>
                        <label class="block text-sm">Kart Numarası</label>
                        <input
                            v-model="cardNumber"
                            type="text"
                            placeholder="1234567890123456"
                            class="mt-1 w-full rounded border px-3 py-2"
                            maxlength="16"
                            @input="onlyDigits($event, 'cardNumber', 16)"
                        />
                    </div>

                    <div class="flex gap-4">
                        <div class="w-1/2">
                            <label class="block text-sm">Son Kullanma Tarihi</label>
                            <input
                                v-model="expiryDate"
                                type="text"
                                placeholder="MM/YY"
                                class="mt-1 w-full rounded border px-3 py-2"
                                maxlength="5"
                                @input="formatExpiryDate"
                            />
                        </div>

                        <div class="w-1/2">
                            <label class="block text-sm">CVC</label>
                            <input
                                v-model="cvc"
                                type="text"
                                placeholder="123"
                                class="mt-1 w-full rounded border px-3 py-2"
                                maxlength="3"
                                @input="onlyDigits($event, 'cvc', 3)"
                            />
                        </div>
                    </div>

                    <button @click="submitPayment" class="mt-4 w-full rounded bg-green-600 py-2 text-white hover:bg-green-700">
                        Ödemeyi Tamamla
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
