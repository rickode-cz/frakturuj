<script>
    import { notification, apiData } from '../lib/stores';
    import { deleteCustomer } from '../lib/api';

    export let customer;
    export let onClose;

    let deleting = false;

    async function handleDelete() {
        try {
            deleting = true;
            const response = await deleteCustomer(customer.id);
            
            // Remove customer from store
            apiData.update(data => ({
                ...data,
                customers: data.customers.filter(c => c.id !== customer.id),
                // Also remove deleted invoices
                invoices: data.invoices.filter(inv => Number(inv.acf.customer) !== Number(customer.id))
            }));

            notification.set({
                type: 'success',
                message: `Zákazník byl smazán včetně ${response.deleted_invoices} faktur`
            });

            onClose();
        } catch (e) {
            notification.set({
                type: 'error',
                message: e.message || 'Nepodařilo se smazat zákazníka'
            });
        } finally {
            deleting = false;
        }
    }
</script>

<div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[998]" on:click={onClose}></div>

<div class="fixed inset-4 md:inset-x-1/3 md:inset-y-1/3 bg-zinc-800 rounded-lg shadow-xl z-[999]">
    <div class="p-6 h-full flex flex-col">
        <div class="flex justify-between items-start">
            <h2 class="text-2xl">Smazat zákazníka</h2>
            <button class="text-zinc-400 hover:text-white" on:click={onClose}>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="my-6 text-zinc-300">
            <p>Opravdu chcete smazat zákazníka <strong>{customer.title}</strong>?</p>
            <p class="mt-2 text-red-400">Budou smazány i všechny faktury tohoto zákazníka!</p>
        </div>

        <div class="flex gap-4 mt-auto">
            <button
                type="button"
                class="cursor-pointer flex-1 px-4 py-2 bg-zinc-600 rounded hover:bg-zinc-700 disabled:opacity-50"
                on:click={onClose}
                disabled={deleting}
            >
                Zrušit
            </button>
            <button
                type="button"
                class="cursor-pointer flex-1 px-4 py-2 bg-red-600 rounded hover:bg-red-700 disabled:opacity-50 flex justify-center items-center gap-2"
                on:click={handleDelete}
                disabled={deleting}
            >
                {#if deleting}
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Mažu...
                {:else}
                    Smazat zákazníka
                {/if}
            </button>
        </div>
    </div>
</div>
