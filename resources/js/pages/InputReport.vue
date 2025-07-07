<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed,ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

// UI Components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Calendar } from '@/components/ui/calendar';

// Date & Formatting Libraries
import { DateFormatter, today, parseDate, getLocalTimeZone, type CalendarDate } from '@internationalized/date';
import { toDate } from 'reka-ui/date';
import { cn ,formatRupiah} from '@/lib/utils';
import { Calendar as CalendarIcon } from 'lucide-vue-next';


// Interface untuk tipe data props
interface ExchangeRates {
    id: number;
    currency: string;
    rates: number;
    created_at: string;
    updated_at: string;
}

// Mendefinisikan props yang diterima dari controller
const props = defineProps<{
    rates: ExchangeRates[];
}>();

// Breadcrumbs untuk layout
const breadcrumbs = [
    {
        title: 'Laporan',
        href: '/laporan',
    },
    {
        title: 'Input Baru',
        href: '/laporan/add',
    },
];

// Inisialisasi form dengan Inertia.js
const form = useForm({
    domain: '',
    tanggal: today(getLocalTimeZone()).toString(), // Tambahkan tanggal, default hari ini dalam format string
    modal: 0,
    revenue: 0,
    tax: 11,
    currency: '',
});

// --- Helper untuk Tanggal ---
const df = new DateFormatter('id-ID', {
  dateStyle: 'long',
});

// Computed property untuk menampilkan tanggal di tombol
const tanggalDisplay = computed(() => {
    if (!form.tanggal) return "Pilih tanggal";
    return df.format(toDate(parseDate(form.tanggal)));
});

// Computed property sebagai model untuk komponen Calendar
const tanggalModel = computed({
    get: () => form.tanggal ? parseDate(form.tanggal) : undefined,
    set: (val: CalendarDate | undefined) => {
        form.tanggal = val ? val.toString() : '';
    }
});
// ----------------------------

// Fungsi untuk menangani submit form
const submitLaporan = () => {
    form.post('/laporan/add', {
        onSuccess: () => {
            form.reset();
        },
    });
};
const formModalHint = ref<string>('Rp. ');
const formRevenueHint = ref<string>('Rp. ');


</script>

<template>
    <Head title="Input Laporan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6">
            <Card class="w-full max-w-2xl mx-auto">
                <CardHeader>
                    <CardTitle>Input Laporan Baru</CardTitle>
                    <CardDescription>
                        Isi semua data yang diperlukan di bawah ini untuk menambahkan laporan keuangan baru.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitLaporan">
                        <div class="grid gap-6">
                            <div class="grid grid-cols-4 items-center gap-4">
                                <label for="domain" class="text-right">Domain</label>
                                <Input id="domain" v-model="form.domain" class="col-span-3" required />
                            </div>

                            <div class="grid grid-cols-4 items-center gap-4">
                                <label for="tanggal" class="text-right">Tanggal</label>
                                <Popover>
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            :class="cn('col-span-3 justify-start text-left font-normal', !form.tanggal && 'text-muted-foreground')"
                                        >
                                            <CalendarIcon class="mr-2 h-4 w-4" />
                                            {{ tanggalDisplay }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0">
                                        <Calendar v-model="tanggalModel" />
                                    </PopoverContent>
                                </Popover>
                            </div>
                            <div class="grid grid-cols-4 items-center gap-4">
                                <label for="currency" class="text-right">Mata Uang</label>
                                <Select v-model="form.currency" required>
                                    <SelectTrigger class="col-span-3">
                                        <SelectValue placeholder="Pilih mata uang" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem v-for="rate in props.rates" :key="rate.id" :value="rate.currency">
                                                {{ rate.currency }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="grid grid-cols-4 items-center gap-4">
                                <label for="modal" class="text-right">Modal</label>
                                <Input id="modal" v-model="form.modal" type="number" class="col-span-2" @keyup="formModalHint = formatRupiah(form.modal)" required />
                                <span class="text-foreground-muted text-sm">{{ formModalHint }}</span>
                            </div>

                            <div class="grid grid-cols-4 items-center gap-4">
                                <label for="revenue" class="text-right">Revenue</label>
                                <Input id="revenue" v-model="form.revenue" type="number" class="col-span-2" @keyup="formRevenueHint = formatRupiah(form.revenue)" required />
                                <span class="text-foreground-muted">{{ formRevenueHint }}</span>

                            </div>

                            <div class="grid grid-cols-4 items-center gap-4">
                                <label for="tax" class="text-right">Pajak (%)</label>
                                <Input id="tax" v-model="form.tax" type="number" class="col-span-3" required />
                            </div>

                            <div class="flex justify-end pt-4">
                                <Button type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Laporan' }}
                                </Button>
                            </div>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>