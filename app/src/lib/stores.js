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
    bank_code: '',
    invoice_text: 'Fyzická osoba zapsaná v živnostenském rejstříku.'
});
export const isProfileModalOpen = writable(false);
export const notification = writable(null);