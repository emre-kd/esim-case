<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';


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

const submitPayment = async () => {
  console.log('submitPayment triggered');
  console.log('Form Inputs:', {
    email: email.value,
    phoneNumber: phoneNumber.value,
    cardNumber: cardNumber.value,
    expiryDate: expiryDate.value,
    cvc: cvc.value
  });

  try {
    if (!email.value || !phoneNumber.value || !cvc.value || !expiryDate.value || !cardNumber.value) {
      alert('Lütfen boş alanları doldurunuz.');
      console.log('Validation failed: Missing form fields');
      return;
    }

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
      console.log('No valid apiIds found in cart');
      return;
    }

    const postData = {
      api_id: apiIds,
      gsm_no: phoneNumber.value,
      email: email.value,
    };

    console.log('Sending API data:', postData);

    const response = await axios.post('/api/proxy/esim-create', postData);
    console.log('API Response:', response);
    console.log('Response Data:', response.data);

    // sold_esim hem obje hem array olabilir, normalize et
    const soldEsimRaw = response.data.sold_esim;
    const soldEsimList = Array.isArray(soldEsimRaw) ? soldEsimRaw : soldEsimRaw ? [soldEsimRaw] : [];

    pendingEsimIds.value = soldEsimList.map((item: any) => item.id);
    console.log('Pending eSIM IDs:', pendingEsimIds.value);

    if (!pendingEsimIds.value.length) {
      alert('API yanıtında eSIM ID bulunamadı.');
      console.log('No eSIM IDs returned from API');
      return;
    }

    router.get('/payment/verify', {
      pendingEsimIds: pendingEsimIds.value,
    }, {
      onError: (error) => {
        console.error('Navigation error:', error);
        alert('Doğrulama sayfasına yönlendirme başarısız.');
      },
    });
  } catch (error: any) {
    console.error('Error in submitPayment:', error);
    if (axios.isAxiosError(error)) {
      console.error('Axios error:', error.response?.data || error.message);
      alert(`eSIM oluşturulurken hata oluştu: ${error.response?.data?.message || error.message}`);
    } else {
      console.error('Unknown error:', error.message);
      alert('Beklenmeyen bir hata oluştu.');
    }
  }
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
              <div>{{ item.country }} - {{ item.data }} - {{ item.days }} {{ item.api_id }}</div>
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
            <label class="block text-sm">E-Posta</label>
            <input
              v-model="email"
              type="email"
              placeholder="example@mail.com"
              class="w-full border rounded px-3 py-2 mt-1"
              value="example@mail.com"
              readonly
            />
          </div>
          <div>
            <label class="block text-sm">Telefon</label>
            <input
              v-model="phoneNumber"
              type="tel"
              placeholder="5554443322"
              class="w-full border rounded px-3 py-2 mt-1"
              value="5554443322"
              readonly
            />
          </div>
          <div>
            <label class="block text-sm">Kart Numarası</label>
            <input
              v-model="cardNumber"
              type="text"
              placeholder="1234 5678 9012 3456"
              class="w-full border rounded px-3 py-2 mt-1"
            />
          </div>
          <div class="flex gap-4">
            <div class="w-1/2">
              <label class="block text-sm">Son Kullanma Tarihi</label>
              <input
                v-model="expiryDate"
                type="text"
                placeholder="MM/YY"
                class="w-full border rounded px-3 py-2 mt-1"
              />
            </div>
            <div class="w-1/2">
              <label class="block text-sm">CVC</label>
              <input
                v-model="cvc"
                type="text"
                placeholder="123"
                class="w-full border rounded px-3 py-2 mt-1"
              />
            </div>
          </div>
          <button
            @click="submitPayment"
            class="w-full mt-4 bg-green-600 hover:bg-green-700 text-white py-2 rounded"
          >
            Ödemeyi Tamamla
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
