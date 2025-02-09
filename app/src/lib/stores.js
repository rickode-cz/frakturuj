import { writable } from 'svelte/store';

export const apiData = writable(null);
export const selectedYear = writable(new Date().getFullYear());
export const profile = writable({
    title: '',
    ico: '',
    dic: '',
    street: '',
    city: '',
    psc: '',
    country: 'Česká republika',
    email: '',
    phone: '',
    bank_account: '',
    bank_code: ''
});
export const isProfileModalOpen = writable(false);
export const notification = writable(null);