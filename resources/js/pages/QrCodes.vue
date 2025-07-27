<script setup lang="ts">
import { computed } from 'vue';
import { usePage, Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

// Props'tan gelen sales verisi
const page = usePage();
const sales = page.props.sales as any[] || [];

// Tek bir satış objesi oluşturma mantığı (consolidate)
const consolidateSales = (salesData: any[]) => {
  const consolidated: any[] = [];
  let currentSale: any = {};

  salesData.forEach((item) => {
    if (item.id) {
      // Önceki satış varsa listeye ekle
      if (Object.keys(currentSale).length) consolidated.push(currentSale);
      currentSale = { id: item.id };
    } else {
      // Gelen item'da ilgili alanlar varsa currentSale'a ekle
      if (item.title) currentSale.title = item.title;
      if (item.coverage) currentSale.coverage = item.coverage;
      const qrCode = item.parameters?.data?.[0]?.esimDetail?.[0]?.qr_code;
      if (qrCode) currentSale.qr_code = qrCode;
    }
  });

  if (Object.keys(currentSale).length) consolidated.push(currentSale);

  return consolidated;
};

// Eksik veri olmayan satışları filtrele
const filteredSales = computed(() => {
  return consolidateSales(sales).filter(
    (sale) => sale.coverage && sale.title && sale.qr_code
  );
});

</script>

<template>
  <AppLayout>
    <Head title="Satışlar" />

    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Satış Listesi</h1>

      <table class="min-w-full bg-white border border-gray-200">
        <thead>
          <tr class="bg-gray-100 text-left">
            <th class="py-2 px-4 border-b">Ülke</th>
            <th class="py-2 px-4 border-b">Başlık</th>
            <th class="py-2 px-4 border-b">QR Kod</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="sale in filteredSales" :key="sale.id" class="hover:bg-gray-50">
            <td class="py-2 px-4 border-b">{{ sale.coverage }}</td>
            <td class="py-2 px-4 border-b">{{ sale.title }}</td>
            <td class="py-2 px-4 border-b">
              <a
                v-if="sale.qr_code"
                :href="sale.qr_code"
                class="text-blue-600 underline"
                target="_blank"
                rel="noopener noreferrer"
              >
                QR Kodu Görüntüle
              </a>
              <span v-else>Yok</span>
            </td>
          </tr>

          <tr v-if="filteredSales.length === 0">
            <td colspan="3" class="py-4 px-4 text-center text-gray-500">
              Gösterilecek veri yok.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>
