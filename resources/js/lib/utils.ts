import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { usePage } from '@inertiajs/vue3';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}
export const formatRupiah = (value: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0, // Tidak menampilkan angka di belakang koma
  }).format(value);
};
export const isAdmin = (): boolean => {
    const user = usePage().props.auth.user as { is_admin: boolean } | null;

    return !!user?.is_admin;
};

export const formatCurrency = (value: number, currency: string) => {
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
