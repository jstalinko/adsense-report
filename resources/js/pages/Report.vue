<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { formatDate as formatDateFns, startOfDay } from 'date-fns';

// Layout & Komponen UI
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, TableCaption } from '@/components/ui/table';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    DialogClose
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Calendar } from '@/components/ui/calendar';
import { cn, formatRupiah, isAdmin } from '@/lib/utils';
import { DateFormatter, today, parseDate, type CalendarDate } from '@internationalized/date';
import { toDate } from 'reka-ui/date';

// Ikon
import { PlusCircle, Calendar as CalendarIcon, MoreHorizontal, Pencil, Trash2 } from 'lucide-vue-next';
import { User } from '@/types';

// Tipe data
interface Laporan {
    id: number;
    created_at: string;
    updated_at: string;
    domain: string;
    modal: number;
    revenue: number;
    tax: number;
    user: User;
}
interface ExchangeRates {
    id: number;
    currency: string;
    rates: number;
    created_at: string;
    updated_at: string;
}
interface Domains {
    domain: string;
}

// Props dari controller
const props = defineProps<{
    laporans: Laporan[];
    rates: ExchangeRates[];
    domains: Domains[];
    users: User[]
}>();

// --- State untuk filter ---
const domainFilter = ref('all');
const userFilter = ref('all');
const currencyFilter = ref('IDR');
const startDateFilter = ref<Date>();
const endDateFilter = ref<Date>();
const isStartPopoverOpen = ref(false);
const isEndPopoverOpen = ref(false);

// --- State & Logika untuk Form Input Laporan ---
const df = new DateFormatter('id-ID', {
    dateStyle: 'long',
});

const isCreateDialogOpen = ref(false);

const form = useForm({
    tanggal: '', // Disimpan sebagai string "YYYY-MM-DD"
    currency: 'IDR',
    domain: '',
    modal: 0,
    revenue: 0,
    tax: 0,
});

const setDatex = (date: CalendarDate | undefined) => {
    if (date) {
        form.tanggal = date.toString();
    } else {
        form.tanggal = '';
    }
};

const tanggalDisplay = computed(() => {
    if (!form.tanggal) return "Pilih tanggal";
    const dateObject = parseDate(form.tanggal);
    return df.format(toDate(dateObject));
});

const tanggalModel = computed(() => {
    return form.tanggal ? parseDate(form.tanggal) : undefined;
});

const submitLaporan = () => {
    form.post('/laporan/add', {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            form.reset();
        },
    });
};

// --- Logika untuk Edit & Delete ---
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const reportToDelete = ref<Laporan | null>(null);

const editForm = useForm({
    id: 0,
    tanggal: '',
    currency: 'IDR', // Currency will not be editable, assuming it's fixed on creation.
    domain: '',
    modal: 0,
    revenue: 0,
    tax: 0,
});

// Computed properties for Edit form date picker
const setEditDate = (date: CalendarDate | undefined) => {
    if (date) {
        editForm.tanggal = date.toString();
    } else {
        editForm.tanggal = '';
    }
};
const editTanggalDisplay = computed(() => {
    if (!editForm.tanggal) return "Pilih tanggal";
    const dateObject = parseDate(editForm.tanggal);
    return df.format(toDate(dateObject));
});
const editTanggalModel = computed(() => {
    return editForm.tanggal ? parseDate(editForm.tanggal) : undefined;
});

const openEditDialog = (laporan: Laporan) => {
    // Populate form with existing data
    editForm.id = laporan.id;
    editForm.domain = laporan.domain;
    editForm.modal = laporan.modal;
    editForm.revenue = laporan.revenue;
    editForm.tax = laporan.tax;
    // Format the created_at date to "YYYY-MM-DD" for the form
    editForm.tanggal = formatDateFns(new Date(laporan.created_at), 'yyyy-MM-dd');
    editModalHint.value = formatRupiah(laporan.modal);
    editRevenueHint.value = formatRupiah(laporan.revenue);

    isEditDialogOpen.value = true;
};

const submitEditLaporan = () => {
    editForm.post(`/laporan/${editForm.id}/update`, {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            editForm.reset();
        }
    });
};

const openDeleteDialog = (laporan: Laporan) => {
    reportToDelete.value = laporan;
    isDeleteDialogOpen.value = true;
};

const confirmDelete = () => {
    if (reportToDelete.value) {
        const deleteForm = useForm({});
        deleteForm.get(`/laporan/${reportToDelete.value.id}/delete`, {
            onSuccess: () => {
                isDeleteDialogOpen.value = false;
                reportToDelete.value = null;
            }
        });
    }
};


// --- Logika untuk Tampilan Tabel ---
const breadcrumbs = [
    {
        title: 'Laporan',
        href: '/laporan',
    },
];

const formatCurrency = (value: number, currency: string) => {
    const options: Intl.NumberFormatOptions = {
        style: 'currency',
        currency,
    };
    if (currency === 'USD') {
        options.minimumFractionDigits = 2;
        options.maximumFractionDigits = 2;
    }
    return new Intl.NumberFormat('id-ID', options).format(value);
};

const uniqueDomains = computed(() => {
    const domains = new Set(props.laporans.map(item => item.domain));
    return ['all', ...Array.from(domains)];
});

const uniqueCurrencies = computed(() => {
    return props.rates.map(rate => rate.currency);
});

const computedLaporans = computed(() => {
    const idrRate = props.rates.find(r => r.currency === 'IDR')?.rates;
    const targetRate = props.rates.find(r => r.currency === currencyFilter.value)?.rates;

    if (!idrRate || !targetRate) {
        return [];
    }

    return props.laporans
        .filter(item => {
            const domainMatch = domainFilter.value === 'all' || item.domain === domainFilter.value;
            const userMatch = userFilter.value == 'all' || item.user.name === userFilter.value;

            let dateMatch = true;
            const itemDate = startOfDay(new Date(item.created_at));
            if (startDateFilter.value) {
                dateMatch &&= (itemDate >= startOfDay(startDateFilter.value));
            }
            if (endDateFilter.value) {
                dateMatch &&= (itemDate <= startOfDay(endDateFilter.value));
            }

            return domainMatch && dateMatch && userMatch;
        })
        .map(item => {
            const convertFromIdr = (idrAmount: number) => {
                const amountInUsd = idrAmount / idrRate;
                return amountInUsd * targetRate;
            };

            const displayModal = convertFromIdr(item.modal);
            const displayRevenue = convertFromIdr(item.revenue);
            const displayPajakAmount = (displayModal * item.tax) / 100;
            const displayNetProfit = displayRevenue - displayModal - displayPajakAmount;

            const originalPajakAmount = (item.revenue * item.tax) / 100;
            const originalNetProfit = item.revenue - item.modal - originalPajakAmount;
            const roi = item.modal > 0 ? (originalNetProfit / item.modal) * 100 : 0;
            const roas = item.modal > 0 ? originalNetProfit / item.modal : 0;

            return {
                ...item,
                created_atFormatted: formatDateFns(new Date(item.created_at), 'dd MMM yyyy'),
                displayModal,
                displayRevenue,
                displayPajakAmount,
                displayNetProfit,
                roi,
                roas,
            };
        });
});
const formModalHint = ref<string>('Rp. 0');
const formRevenueHint = ref<string>('Rp. 0');
const editModalHint = ref<string>('Rp. 0');
const editRevenueHint = ref<string>('Rp. 0');
</script>

<template>

    <Head title="Laporan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6">
            <div class="flex flex-col items-start gap-4 md:flex-row md:items-center md:justify-between">
                <h1 class="text-2xl font-bold">Laporan Keuangan</h1>
                <div class="flex w-full flex-col items-stretch gap-2 md:w-auto md:flex-row md:items-center">
                    <Select v-model="userFilter" v-if="isAdmin()">
                        <SelectTrigger class="w-full md:w-[180px]">
                            <SelectValue placeholder="Filter By User" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem :value="'all'">
                                    Semua User
                                </SelectItem>

                                <SelectItem v-for="us in users" :key="us.id" :value="us.name">
                                    {{ us.name }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <Select v-model="domainFilter">
                        <SelectTrigger class="w-full md:w-[180px]">
                            <SelectValue placeholder="Filter by Domain" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem v-for="domain in uniqueDomains" :key="domain" :value="domain">
                                    {{ domain === 'all' ? 'Semua Domain' : domain }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>

                    <Select v-model="currencyFilter">
                        <SelectTrigger class="w-full md:w-[120px]">
                            <SelectValue placeholder="Currency" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem v-for="currency in uniqueCurrencies" :key="currency" :value="currency">
                                    {{ currency }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>

                    <Popover v-model:open="isStartPopoverOpen">
                        <PopoverTrigger as-child>
                            <Button variant="outline" class="w-full justify-start text-left font-normal md:w-[180px]"
                                :class="!startDateFilter && 'text-muted-foreground'">
                                <CalendarIcon class="mr-2 h-4 w-4" />
                                <span>{{ startDateFilter ? formatDateFns(startDateFilter, 'dd MMM yyyy') : 'Start Date'
                                }}</span>
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0" align="start">
                            <Calendar v-model="startDateFilter" @select="isStartPopoverOpen = false" />
                        </PopoverContent>
                    </Popover>

                    <Popover v-model:open="isEndPopoverOpen">
                        <PopoverTrigger as-child>
                            <Button variant="outline" class="w-full justify-start text-left font-normal md:w-[180px]"
                                :class="!endDateFilter && 'text-muted-foreground'">
                                <CalendarIcon class="mr-2 h-4 w-4" />
                                <span>{{ endDateFilter ? formatDateFns(endDateFilter, 'dd MMM yyyy') : 'End Date'
                                }}</span>
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0" align="start">
                            <Calendar v-model="endDateFilter" @select="isEndPopoverOpen = false" />
                        </PopoverContent>
                    </Popover>

                    <Dialog v-model:open="isCreateDialogOpen">
                        <DialogTrigger as-child>
                            <Button>
                                <PlusCircle class="mr-2 h-4 w-4" />
                                Input
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-[800px]">
                            <DialogHeader>
                                <DialogTitle>Input Laporan Baru</DialogTitle>
                                <DialogDescription>Isi semua data yang diperlukan. Klik simpan jika sudah selesai.
                                </DialogDescription>
                            </DialogHeader>
                            <form @submit.prevent="submitLaporan">
                                <div class="grid gap-4 py-4">
                                    <div class="grid grid-cols-4 items-center gap-4">
                                        <label for="domain" class="text-right">Domain</label>
                                        <Select v-model="form.domain" required>
                                            <SelectTrigger class="col-span-3">
                                                <SelectValue placeholder="Pilih domain" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectItem v-for="(domain, index) in props.domains" :key="index"
                                                        :value="domain.domain">
                                                        {{ domain.domain }}
                                                    </SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="grid grid-cols-4 items-center gap-4">
                                        <label for="tanggal" class="text-right">Tanggal</label>
                                        <Popover>
                                            <PopoverTrigger as-child>
                                                <Button :variant="'outline'" :class="cn(
                                                    'col-span-3 justify-start text-left font-normal',
                                                    !form.tanggal && 'text-muted-foreground',
                                                )">
                                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                                    {{ tanggalDisplay }}
                                                </Button>
                                            </PopoverTrigger>
                                            <PopoverContent class="w-auto p-0">
                                                <Calendar :model-value="tanggalModel" @update:model-value="setDatex" />
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
                                                    <SelectItem v-for="rate in props.rates" :key="rate.id"
                                                        :value="rate.currency">
                                                        {{ rate.currency }}
                                                    </SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="grid grid-cols-4 items-center gap-4">
                                        <label for="modal" class="text-right">Modal</label>
                                        <Input id="modal" v-model="form.modal" type="number" class="col-span-2" required
                                            @keyup="formModalHint = formatRupiah(form.modal)" />
                                        <span class="text-foreground-muted">{{ formModalHint }}</span>
                                    </div>
                                    <div class="grid grid-cols-4 items-center gap-4">
                                        <label for="revenue" class="text-right">Revenue</label>
                                        <Input id="revenue" v-model="form.revenue" type="number" class="col-span-2"
                                            required @keyup="formRevenueHint = formatRupiah(form.revenue)" />
                                        <span class="text-foreground-muted">{{ formRevenueHint }}</span>

                                    </div>
                                    <div class="grid grid-cols-4 items-center gap-4">
                                        <label for="tax" class="text-right">Pajak (%)</label>
                                        <Input id="tax" v-model="form.tax" type="number" class="col-span-3" required />
                                    </div>
                                </div>
                                <DialogFooter>
                                    <Button type="submit" :disabled="form.processing">Simpan</Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <div class="rounded-lg border">
                <Table>
                    <TableCaption v-if="computedLaporans.length === 0">Tidak ada data untuk ditampilkan.</TableCaption>
                    <TableHeader>
                        <TableRow>
                            <TableHead v-if="isAdmin()">UserName</TableHead>
                            <TableHead>Created At</TableHead>
                            <TableHead>Domain</TableHead>
                            <TableHead class="text-right">Modal</TableHead>
                            <TableHead class="text-right">Revenue</TableHead>
                            <TableHead class="text-right">Pajak</TableHead>
                            <TableHead class="text-right">Net Profit</TableHead>
                            <TableHead class="text-right">ROI</TableHead>
                            <TableHead class="text-right">ROAS</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="item in computedLaporans" :key="item.id">
                            <TableCell v-if="isAdmin()">{{ item.user.name }}</TableCell>
                            <TableCell>{{ item.created_atFormatted }}</TableCell>
                            <TableCell class="font-medium">{{ item.domain }}</TableCell>
                            <TableCell class="text-right">{{ formatCurrency(item.displayModal, currencyFilter) }}
                            </TableCell>
                            <TableCell class="text-right">{{ formatCurrency(item.displayRevenue, currencyFilter) }}
                            </TableCell>
                            <TableCell class="text-right">{{ formatCurrency(item.displayPajakAmount, currencyFilter) }}
                            </TableCell>
                            <TableCell class="text-right font-semibold"
                                :class="item.displayNetProfit < 0 ? 'text-destructive' : 'text-green-600'">
                                {{ formatCurrency(item.displayNetProfit, currencyFilter) }}
                            </TableCell>
                            <TableCell class="text-right" :class="item.roi < 0 ? 'text-destructive' : 'text-green-600'">
                                {{ item.roi.toFixed(2) }}%
                            </TableCell>
                            <TableCell class="text-right">{{ item.roas.toFixed(2) }}x</TableCell>
                            <TableCell class="text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" class="h-8 w-8 p-0">
                                            <span class="sr-only">Open menu</span>
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                        <DropdownMenuItem @click="openEditDialog(item)">
                                            <Pencil class="mr-2 h-4 w-4" />
                                            <span>Edit</span>
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem @click="openDeleteDialog(item)"
                                            class="text-destructive focus:text-destructive focus:bg-destructive/10">
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            <span>Delete</span>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <Dialog v-model:open="isEditDialogOpen">
            <DialogContent class="sm:max-w-[800px]">
                <DialogHeader>
                    <DialogTitle>Edit Laporan</DialogTitle>
                    <DialogDescription>Ubah data yang diperlukan. Klik simpan jika sudah selesai.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitEditLaporan">
                    <div class="grid gap-4 py-4">
                        <div class="grid grid-cols-4 items-center gap-4">
                            <label for="edit-domain" class="text-right">Domain</label>
                            <Select v-model="editForm.domain" required>
                                <SelectTrigger class="col-span-3">
                                    <SelectValue placeholder="Pilih domain" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="(domain, index) in props.domains" :key="index"
                                            :value="domain.domain">
                                            {{ domain.domain }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <label for="edit-tanggal" class="text-right">Tanggal</label>
                            <Popover>
                                <PopoverTrigger as-child>
                                    <Button :variant="'outline'" :class="cn(
                                        'col-span-3 justify-start text-left font-normal',
                                        !editForm.tanggal && 'text-muted-foreground',
                                    )">
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{ editTanggalDisplay }}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto p-0">
                                    <Calendar :model-value="editTanggalModel" @update:model-value="setEditDate" />
                                </PopoverContent>
                            </Popover>
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <label for="edit-modal" class="text-right">Modal</label>
                            <Input id="edit-modal" v-model="editForm.modal" type="number" class="col-span-2" required
                                @keyup="editModalHint = formatRupiah(editForm.modal)" />
                            <span class="text-foreground-muted">{{ editModalHint }}</span>

                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <label for="edit-revenue" class="text-right">Revenue</label>
                            <Input id="edit-revenue" v-model="editForm.revenue" type="number" class="col-span-2"
                                required @keyup="editRevenueHint = formatRupiah(editForm.revenue)" />
                            <span class="text-foreground-muted">{{ editRevenueHint }}</span>

                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <label for="edit-tax" class="text-right">Pajak (%)</label>
                            <Input id="edit-tax" v-model="editForm.tax" type="number" class="col-span-3" required />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="submit" :disabled="editForm.processing">Simpan Perubahan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Konfirmasi Hapus</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus laporan untuk domain
                        <span class="font-semibold">{{ reportToDelete?.domain }}</span>
                        pada tanggal
                        <span class="font-semibold">{{ reportToDelete ? formatDateFns(new
                            Date(reportToDelete.created_at), 'dd MMM yyyy') : '' }}</span>?
                        Tindakan ini tidak bisa dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="sm:justify-end">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary">Batal</Button>
                    </DialogClose>
                    <Button type="button" variant="destructive" @click="confirmDelete">Hapus</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </AppLayout>
</template>