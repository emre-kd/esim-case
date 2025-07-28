<script setup lang="ts">
import { Head, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import axios from 'axios';
import { route } from 'ziggy-js'; // Laravel rotalarını JS içinde isimleriyle kullanmanı sağlar.


const page = usePage();
const pendingEsimIds = ref<number[]>(page.props.pendingEsimIds || []);
const verificationCode = ref('000');
const confirmedSales = ref<any[]>([]);
const isLoading = ref(false);

const ALERT_MESSAGES = {
  noPendingEsim: 'Onaylanacak eSIM bulunamadı.',
  emptyVerificationCode: 'Lütfen doğrulama kodunu girin',
  confirmationFailed: (msg: string) => `Satış onayı başarısız: ${msg}`,
  confirmationError: (msg: string) => `Satış onayı sırasında hata oluştu: ${msg}`,
  unexpectedError: (msg: string) => `İşlem sırasında beklenmeyen bir hata oluştu: ${msg}`,
  qrNavigationFailed: 'QR kodları sayfasına yönlendirme başarısız.',
};

const confirmSingleSale = async (soldEsimId: number) => {
  const confirmPayload = {
    id: soldEsimId,
    kartCvv: '000',
    kartNo: '9792100000000001',
    kartSonKullanmaTarihi: '2028-02-01',
    kartSahibi: 'Tamamliyo Yazılım',
    taksitSayisi: 1,
  };

  try {
    const res = await axios.post(route('tamamliyo.esim.confirm'), confirmPayload);
    if (res.data.status === true) {
      return res.data.sold_esim;
    } else {
      throw new Error(res.data.message || 'Bilinmeyen hata');
    }
  } catch (error: any) {
    const msg = error?.response?.data?.message || error.message || 'Bilinmeyen hata';
    throw new Error(msg);
  }
};

const confirmSale = async () => {
  if (isLoading.value) return;
  isLoading.value = true;

  try {
    if (!pendingEsimIds.value.length) {
      alert(ALERT_MESSAGES.noPendingEsim);
      return;
    }

    if (!verificationCode.value.trim()) {
      alert(ALERT_MESSAGES.emptyVerificationCode);
      return;
    }

    const sales: any[] = [];

    for (const soldEsimId of pendingEsimIds.value) {
      try {
        const sale = await confirmSingleSale(soldEsimId);
        sales.push(sale);
      } catch (err: any) {
        alert(ALERT_MESSAGES.confirmationFailed(err.message));
        return;
      }

      // API’yi yormamak için ufak delay
      await new Promise((resolve) => setTimeout(resolve, 500));
    }

    confirmedSales.value = sales;

    router.get(
      route('payment.qr'),
      { sales: confirmedSales.value },
      {
        onError: () => alert(ALERT_MESSAGES.qrNavigationFailed),
      },
    );
  } catch (error: any) {
    alert(ALERT_MESSAGES.unexpectedError(error.message));
  } finally {
    isLoading.value = false;
  }
};
</script>


<template>
  <Head title="Doğrulama" />

  <AppLayout>
    <div class="p-6 max-w-md mx-auto text-center">
      <h1 class="text-2xl font-bold mb-4">Doğrulama Kodu <br> (Direkt gönder)</h1>

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
