<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

// UI Components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';

// Icons
import { PlusCircle, Trash2, FilePenLine } from 'lucide-vue-next';

// Interface
interface ExchangeRates {
    id: number;
    currency: string;
    rates: number;
}

// Props dari controller
const props = defineProps<{
    rates: ExchangeRates[];
}>();

// State untuk membuka/menutup dialog
const isAddDialogOpen = ref(false);
const isEditDialogOpen = ref(false);

// Form untuk menambah data baru
const addForm = useForm({
    currency: '',
    rates: 0,
});

// Form untuk mengedit data
const editForm = useForm({
    id: 0,
    currency: '',
    rates: 0,
});

// Breadcrumbs
const breadcrumbs = [
    {
        title: 'Exchange Rates',
        href: '/rates',
    },
];

// --- FUNGSI-FUNGSI ---

// Fungsi untuk submit data baru
const submitAddRate = () => {
    addForm.post(route('rates.store'), {
        onSuccess: () => {
            isAddDialogOpen.value = false;
            addForm.reset();
        },
    });
};

// Fungsi untuk membuka dialog edit dan mengisi form
const openEditDialog = (rate: ExchangeRates) => {
    editForm.id = rate.id;
    editForm.currency = rate.currency;
    editForm.rates = rate.rates;
    isEditDialogOpen.value = true;
};

// Fungsi untuk submit data yang diedit
const submitEditRate = () => {
    editForm.put(route('rates.update', editForm.id), {
        onSuccess: () => {
            isEditDialogOpen.value = false;
        },
    });
};

// Fungsi untuk menghapus data
const deleteRate = (rateId: number) => {
    router.delete(route('rates.destroy', rateId), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Exchange Rate" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Exchange Rates</h1>
                
                <Dialog v-model:open="isAddDialogOpen">
                    <DialogTrigger as-child>
                        <Button>
                            <PlusCircle class="mr-2 h-4 w-4" />
                            Input Rates
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[425px]">
                        <DialogHeader>
                            <DialogTitle>Input Rate Baru</DialogTitle>
                            <DialogDescription>
                                Masukkan kode mata uang dan nilai tukarnya terhadap 1 USD.
                            </DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="submitAddRate">
                            <div class="grid gap-4 py-4">
                                <div class="grid grid-cols-4 items-center gap-4">
                                    <label for="currency" class="text-right">Currency</label>
                                    <Input id="currency" v-model="addForm.currency" class="col-span-3" placeholder="e.g. IDR" required />
                                </div>
                                <div class="grid grid-cols-4 items-center gap-4">
                                    <label for="rates" class="text-right">Rates / USD</label>
                                    <Input id="rates" v-model="addForm.rates" type="number" step="any" class="col-span-3" required />
                                </div>
                            </div>
                            <DialogFooter>
                                <Button type="submit" :disabled="addForm.processing">
                                    {{ addForm.processing ? 'Menyimpan...' : 'Simpan' }}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card v-for="rate in props.rates" :key="rate.id">
                    <CardHeader>
                        <CardTitle class="text-3xl font-bold">{{ rate.currency }}</CardTitle>
                        <CardDescription>Rate per 1 USD</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl">{{ rate.rates }}</p>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2">
                        <Button variant="outline" size="icon" @click="openEditDialog(rate)">
                            <FilePenLine class="h-4 w-4" />
                        </Button>
                        
                        <AlertDialog>
                            <AlertDialogTrigger as-child>
                                <Button variant="destructive" size="icon">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                                <AlertDialogHeader>
                                    <AlertDialogTitle>Apakah Anda Yakin?</AlertDialogTitle>
                                    <AlertDialogDescription>
                                        Tindakan ini tidak dapat diurungkan. Ini akan menghapus data nilai tukar secara permanen.
                                    </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                    <AlertDialogCancel>Batal</AlertDialogCancel>
                                    <AlertDialogAction @click="deleteRate(rate.id)">Hapus</AlertDialogAction>
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>
                    </CardFooter>
                </Card>
            </div>
        </div>

        <Dialog v-model:open="isEditDialogOpen">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Edit Rate: {{ editForm.currency }}</DialogTitle>
                    <DialogDescription>
                        Perbarui nilai tukar untuk mata uang ini.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitEditRate">
                    <div class="grid gap-4 py-4">
                        <div class="grid grid-cols-4 items-center gap-4">
                            <label for="edit-currency" class="text-right">Currency</label>
                            <Input id="edit-currency" v-model="editForm.currency" class="col-span-3" required />
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <label for="edit-rates" class="text-right">Rates / USD</label>
                            <Input id="edit-rates" v-model="editForm.rates" type="number" step="any" class="col-span-3" required />
                        </div>
                    </div>
                    <DialogFooter>
                         <Button type="submit" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

    </AppLayout>
</template>