<script setup lang="ts">
import { Head, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import axios from 'axios';

const page = usePage();
const pendingEsimIds = ref<number[]>(page.props.pendingEsimIds || []);
const verificationCode = ref('');
const confirmedSales = ref<any[]>([]);
const isLoading = ref(false);

const confirmSale = async () => {
  if (isLoading.value) return;
  isLoading.value = true;

  if (!pendingEsimIds.value.length) {
    alert('Onaylanacak eSIM bulunamadı.');
    isLoading.value = false;
    return;
  }

  if (!verificationCode.value) {
    alert('Lütfen doğrulama kodunu girin');
    isLoading.value = false;
    return;
  }

  const sales: any[] = [];

  try {
    for (const soldEsimId of pendingEsimIds.value) {
      const confirmPayload = {
        id: soldEsimId,
        kartCvv: '000',
        kartNo: '9792100000000001',
        kartSonKullanmaTarihi: '2028-02-01',
        kartSahibi: 'Tamamliyo Yazılım',
        taksitSayisi: 1,
      };

      try {
        const res = await axios.post('/api/proxy/esim-confirm', confirmPayload);
        if (res.data.status === true) {
          sales.push(res.data.sold_esim);
        } else {
          alert(`Satış onayı başarısız: ${res.data.message}`);
          isLoading.value = false;
          return;
        }
      } catch (error: any) {
        alert(`Satış onayı sırasında hata oluştu: ${error?.response?.data?.message || error.message}`);
        isLoading.value = false;
        return;
      }

      await new Promise(resolve => setTimeout(resolve, 500));
    }

    confirmedSales.value = sales;

    router.get('/payment/qr-codes', {
      sales: confirmedSales.value,
    }, {
      onError: (error) => {
        console.error('Navigation error:', error);
        alert('QR kodları sayfasına yönlendirme başarısız.');
      },
    });
  } catch (error: any) {
    alert('İşlem sırasında beklenmeyen bir hata oluştu: ' + (error?.response?.data?.message || error.message));
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <Head title="Doğrulama" />

  <AppLayout>
    <div class="p-6 max-w-md mx-auto text-center">
      <h1 class="text-2xl font-bold mb-4">Doğrulama Kodu</h1>

      <input
        v-model="verificationCode"
        type="text"
        placeholder="Doğrulama kodunu girin"
        class="border rounded px-4 py-2 mt-4 w-full max-w-xs mx-auto"
      />

      <button
        @click="confirmSale"
        :disabled="isLoading"
        class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center justify-center w-full max-w-xs mx-auto disabled:opacity-50"
      >
        <svg
          v-if="isLoading"
          class="animate-spin h-5 w-5 mr-2 text-white"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"
          />
        </svg>
        <span>{{ isLoading ? 'İşleniyor...' : 'Gönder' }}</span>
      </button>
    </div>
  </AppLayout>
</template>
