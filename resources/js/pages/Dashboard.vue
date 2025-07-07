<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { format as formatDate, startOfDay } from 'date-fns';
import AppLayout from '@/layouts/AppLayout.vue';
import { User, type BreadcrumbItem } from '@/types';

// UI Components
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, TableCaption } from '@/components/ui/table';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Calendar } from '@/components/ui/calendar';
import { Button } from '@/components/ui/button';
import { isAdmin } from '@/lib/utils';

// Icons
import { Coins, TrendingUp, BarChart, Target, Calendar as CalendarIcon } from 'lucide-vue-next';

// --- INTERFACES ---
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
    rates: number; // Rate terhadap USD
}

// --- PROPS ---
const props = defineProps<{
    total_modal: number;
    total_revenue: number;
    total_roi: number;
    total_roas: number;
    this_month_modal: number;
    this_month_revenue: number;
    this_month_roas: number;
    this_month_roi: number;
    reports: Laporan[];
    rates: ExchangeRates[];
    users: User[],
}>();

// --- STATE & HELPERS ---
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
const page = usePage();

// State untuk filter tabel ringkasan
const startDateFilter = ref<Date>();
const endDateFilter = ref<Date>();
const currencyFilter = ref('IDR');
const userFilter = ref('all');
const isStartPopoverOpen = ref(false);
const isEndPopoverOpen = ref(false);

// Helper untuk format mata uang
const formatCurrency = (value: number, currency: string = 'IDR') => {
    const options: Intl.NumberFormatOptions = {
        style: 'currency',
        currency,
    };
    if (currency === 'USD') {
        options.minimumFractionDigits = 2;
        options.maximumFractionDigits = 2;
    } else {
        options.minimumFractionDigits = 0;
    }
    return new Intl.NumberFormat('id-ID', options).format(value);
};

const uniqueCurrencies = computed(() => props.rates.map(rate => rate.currency));

// --- LOGIKA UTAMA: RINGKASAN ---
const domainSummary = computed(() => {
    // 1. Dapatkan rate yang relevan untuk konversi
    const idrRate = props.rates.find(r => r.currency === 'IDR')?.rates;
    const targetRate = props.rates.find(r => r.currency === currencyFilter.value)?.rates;
    if (!idrRate || !targetRate) return [];

    const convertFromIdr = (idrAmount: number) => {
        const amountInUsd = idrAmount / idrRate;
        return amountInUsd * targetRate;
    };

    // 2. Filter laporan berdasarkan tanggal DAN user
    const filteredReports = props.reports.filter(report => {
        // Filter Tanggal
        const itemDate = startOfDay(new Date(report.created_at));
        let dateMatch = true;
        if (startDateFilter.value) {
            dateMatch &&= (itemDate >= startOfDay(startDateFilter.value));
        }
        if (endDateFilter.value) {
            dateMatch &&= (itemDate <= startOfDay(endDateFilter.value));
        }

     
        const userMatch = userFilter.value === 'all' || report.user.name === userFilter.value;

        return dateMatch && userMatch;
    });

    // Tipe data untuk akumulator
    type SummaryAccumulator = Record<string, {
        user: User;
        domain: string;
        modal: number;
        revenue: number;
        taxAmount: number;
    }>;

    // 3. Kelompokkan dan hitung total per kombinasi USER-DOMAIN
    const summary = filteredReports.reduce((acc, report) => {
        const key = `${report.user.id}-${report.domain}`;

        if (!acc[key]) {
            acc[key] = {
                user: report.user,
                domain: report.domain,
                modal: 0,
                revenue: 0,
                taxAmount: 0,
            };
        }

        acc[key].modal += Number(report.modal);
        acc[key].revenue += Number(report.revenue);
        acc[key].taxAmount += (Number(report.modal) * Number(report.tax)) / 100;

        return acc;
    }, {} as SummaryAccumulator);

    // 4. Ubah format hasil, hitung profit, dan metrik lainnya
    return Object.values(summary).map(data => {
        const netProfit = data.revenue - data.modal - data.taxAmount;
        const roi = data.modal > 0 ? (netProfit / data.modal) * 100 : 0;
        const roas = data.modal > 0 ? netProfit / data.modal : 0;

        return {
            ...data,
            netProfit: netProfit,
            roi: roi,
            roas: roas,
            displayModal: convertFromIdr(data.modal),
            displayRevenue: convertFromIdr(data.revenue),
            displayTaxAmount: convertFromIdr(data.taxAmount),
            displayNetProfit: convertFromIdr(netProfit),
        };
    }).sort((a, b) => a.user.name.localeCompare(b.user.name) || a.domain.localeCompare(b.domain));
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <div class="flex flex-col gap-4">
                <h2 class="text-xl font-bold tracking-tight">Statistik Total</h2>
                <span class="text-md font-bold tracking-tight">{{isAdmin() ? 'Total dari Semua User Dan Semua Domain' : 'Total Dari semua domain '+page.props?.auth?.user?.name }} </span>

                <div class="grid auto-rows-min gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Total Modal</CardTitle>
                            <Coins class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ formatCurrency(props.total_modal, 'IDR') }}</div>
                        </CardContent>
                    </Card>
                     <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
                            <TrendingUp class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ formatCurrency(props.total_revenue, 'IDR') }}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Total ROI</CardTitle>
                            <BarChart class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ props.total_roi }}%</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Total ROAS</CardTitle>
                            <Target class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ props.total_roas.toFixed(2) }}x</div>
                        </CardContent>
                    </Card>
                </div>
            </div>
            <div class="flex flex-col gap-4">
                <h2 class="text-xl font-bold tracking-tight">Statistik Bulan Ini</h2>
                <span class="text-md font-bold tracking-tight">{{isAdmin() ? 'Total dari Semua User Dan Semua Domain' : 'Total Dari semua domain '+page.props?.auth?.user?.name }} </span>
                <div class="grid auto-rows-min gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Modal Bulan Ini</CardTitle>
                            <Coins class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ formatCurrency(props.this_month_modal, 'IDR') }}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Revenue Bulan Ini</CardTitle>
                            <TrendingUp class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ formatCurrency(props.this_month_revenue, 'IDR') }}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">ROI Bulan Ini</CardTitle>
                            <BarChart class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ props.this_month_roi }}%</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">ROAS Bulan Ini</CardTitle>
                            <Target class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ props.this_month_roas.toFixed(2) }}x</div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <div class="rounded-xl border border-border/70">
                <div class="p-4">
                    <h2 class="text-xl font-bold tracking-tight">Ringkasan Performa</h2>
                    <p class="text-sm text-muted-foreground">Total performa yang dikelompokkan berdasarkan user dan domain.</p>
                </div>
                <div class="flex flex-col items-stretch gap-2 border-b p-4 md:flex-row md:items-center">
                    <Select v-model="userFilter" v-if="isAdmin()">
                        <SelectTrigger class="w-full md:w-[180px]">
                            <SelectValue placeholder="Filter by User" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="all">Semua User</SelectItem>
                                <SelectItem v-for="user in props.users" :key="user.id" :value="user.name">
                                    {{ user.name }}
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
                            <Button variant="outline" class="w-full justify-start text-left font-normal md:w-[180px]" :class="!startDateFilter && 'text-muted-foreground'">
                                <CalendarIcon class="mr-2 h-4 w-4" />
                                <span>{{ startDateFilter ? formatDate(startDateFilter, 'dd MMM yyyy') : 'Start Date' }}</span>
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0" align="start">
                            <Calendar v-model="startDateFilter" @select="isStartPopoverOpen = false" />
                        </PopoverContent>
                    </Popover>
                    <Popover v-model:open="isEndPopoverOpen">
                         <PopoverTrigger as-child>
                            <Button variant="outline" class="w-full justify-start text-left font-normal md:w-[180px]" :class="!endDateFilter && 'text-muted-foreground'">
                                <CalendarIcon class="mr-2 h-4 w-4" />
                                <span>{{ endDateFilter ? formatDate(endDateFilter, 'dd MMM yyyy') : 'End Date' }}</span>
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0" align="start">
                            <Calendar v-model="endDateFilter" @select="isEndPopoverOpen = false" />
                        </PopoverContent>
                    </Popover>
                </div>
                <Table>
                    <TableCaption v-if="domainSummary.length === 0">Tidak ada data untuk ditampilkan dengan filter saat ini.</TableCaption>
                    <TableHeader>
                        <TableRow>
                            <TableHead v-if="isAdmin()">UserName</TableHead>
                            <TableHead>Domain</TableHead>
                            <TableHead class="text-right">Modal</TableHead>
                            <TableHead class="text-right">Revenue</TableHead>
                            <TableHead class="text-right">Pajak</TableHead>
                            <TableHead class="text-right">Net Profit</TableHead>
                            <TableHead class="text-right">ROI</TableHead>
                            <TableHead class="text-right">ROAS</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="summary in domainSummary" :key="`${summary.user.id}-${summary.domain}`">
                            <TableCell class="font-medium" v-if="isAdmin()">{{ summary.user.name }}</TableCell>
                            <TableCell>{{ summary.domain }}</TableCell>
                            <TableCell class="text-right">{{ formatCurrency(summary.displayModal, currencyFilter) }}</TableCell>
                            <TableCell class="text-right">{{ formatCurrency(summary.displayRevenue, currencyFilter) }}</TableCell>
                            <TableCell class="text-right">{{ formatCurrency(summary.displayTaxAmount, currencyFilter) }}</TableCell>
                            <TableCell class="text-right font-semibold" :class="summary.displayNetProfit < 0 ? 'text-destructive' : 'text-green-600'">
                                {{ formatCurrency(summary.displayNetProfit, currencyFilter) }}
                            </TableCell>
                            <TableCell class="text-right" :class="summary.roi < 0 ? 'text-destructive' : 'text-green-600'">
                                {{ summary.roi.toFixed(2) }}%
                            </TableCell>
                             <TableCell class="text-right">{{ summary.roas.toFixed(2) }}x</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>