<script>
 import { apiData, notification } from "../lib/stores";
 import { toggleInvoicePaid, downloadInvoicePdf } from "../lib/api";
 import DeleteInvoiceConfirmation from "./DeleteInvoiceConfirmation.svelte";

 export let invoice;
 export let customerName;
 export let onEdit;
 export let expanded = false; // Add expanded prop
 export let onShowDetails; // Add show details callback
 export let onDuplicate; // Add new prop

 let showDeleteConfirmation = false;
 let items = []; // Store parsed items

 // Parse items when invoice changes
 $: try {
  items = JSON.parse(invoice.acf.items)?.items || [];
 } catch (e) {
  console.error("Failed to parse invoice items:", e);
  items = [];
 }

 function getInvoiceTotal(items) {
  const parsed = JSON.parse(items)?.items || [];
  return parsed.reduce(
   (sum, item) => sum + item.price_per_unit * item.quantity,
   0,
  );
 }

 function handleDownloadPdf() {
  const userId = $apiData.profile.user_id;
  downloadInvoicePdf(invoice.id, userId);
 }

 async function handleTogglePaid() {
  try {
   const response = await toggleInvoicePaid(invoice.id);

   // Update invoice in store
   apiData.update((data) => ({
    ...data,
    invoices: data.invoices.map((inv) =>
     inv.id === invoice.id ? response.invoice : inv,
    ),
   }));

   // Show success notification
   notification.set({
    type: "success",
    message: response.invoice.acf.paid
     ? "Faktura byla označena jako zaplacená"
     : "Faktura byla označena jako nezaplacená",
   });
  } catch (e) {
   notification.set({
    type: "error",
    message: "Nepodařilo se změnit stav faktury",
   });
  }
 }

 function handleDelete() {
  showDeleteConfirmation = true;
 }

 function handleShowDetails() {
  if (onShowDetails) {
   onShowDetails(invoice.id);
  }
 }

 function handleDuplicate() {
  if (onDuplicate) {
   onDuplicate(invoice);
  }
 }
</script>

<div class="bg-zinc-700 p-3 sm:p-4 rounded-lg">
 <div
  class="flex flex-col sm:flex-row justify-between items-start gap-2 sm:gap-0"
 >
  <div>
   <h3 class="font-bold text-xl">{invoice.title}</h3>
   <p class="text-base text-zinc-400">
    Pro: {customerName}
   </p>
  </div>
  <div class="text-left sm:text-right w-full sm:w-auto">
   <p class="font-bold text-xl">
    {getInvoiceTotal(invoice.acf.items).toLocaleString("cs-CZ")} Kč
   </p>
   <p class="text-base text-zinc-400">
    Splatnost: {new Date(invoice.acf.maturity).toLocaleDateString("cs-CZ")}
   </p>
  </div>
 </div>
 <div
  class="mt-3 sm:mt-2 flex flex-wrap sm:flex-nowrap justify-between items-center gap-2"
 >
  <div class="flex items-center gap-2">
   {#if invoice.acf.paid}
    <span class="text-xs bg-green-900 text-green-300 px-2 py-1 rounded"
     >Zaplaceno</span
    >
   {:else if new Date(invoice.acf.maturity) < new Date()}
    <span class="text-xs bg-red-900 text-red-300 px-2 py-1 rounded"
     >Po splatnosti</span
    >
   {:else}
    <span class="text-xs bg-yellow-900 text-yellow-300 px-2 py-1 rounded"
     >Čeká na platbu</span
    >
   {/if}

   <button
    class="cursor-pointer p-1.5 text-zinc-400 hover:text-white hover:bg-zinc-600 rounded-lg transition-colors"
    title={invoice.acf.paid
     ? "Označit jako nezaplacené"
     : "Označit jako zaplacené"}
    on:click={handleTogglePaid}
   >
    {#if !invoice.acf.paid}
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
     <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
   </svg>
   
    {:else}
     <svg
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke-width="1.5"
      stroke="currentColor"
      class="w-5 h-5"
     >
      <path
       stroke-linecap="round"
       stroke-linejoin="round"
       d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
      />
     </svg>
    {/if}
   </button>
  </div>

  <div class="flex gap-2">
   <button
    class="cursor-pointer p-1.5 text-zinc-400 hover:text-white hover:bg-zinc-600 rounded-lg transition-colors"
    title="Stáhnout PDF"
    on:click={handleDownloadPdf}
   >
    <svg
     xmlns="http://www.w3.org/2000/svg"
     fill="none"
     viewBox="0 0 24 24"
     stroke-width="1.5"
     stroke="currentColor"
     class="w-5 h-5"
    >
     <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"
     />
    </svg>
   </button>
   <button
    class="cursor-pointer p-1.5 text-zinc-400 hover:text-white hover:bg-zinc-600 rounded-lg transition-colors"
    title="Duplikovat fakturu"
    on:click={handleDuplicate}
   >
    <svg
     xmlns="http://www.w3.org/2000/svg"
     fill="none"
     viewBox="0 0 24 24"
     stroke-width="1.5"
     stroke="currentColor"
     class="w-5 h-5"
    >
     <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"
     />
    </svg>
   </button>   
   <button
    class="cursor-pointer p-1.5 text-zinc-400 hover:text-white hover:bg-zinc-600 rounded-lg transition-colors"
    title="Zobrazit detail"
    on:click={handleShowDetails}
   >
    <svg
     xmlns="http://www.w3.org/2000/svg"
     fill="none"
     viewBox="0 0 24 24"
     stroke-width="1.5"
     stroke="currentColor"
     class="w-5 h-5"
    >
     <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"
     />
     <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
     />
    </svg>
   </button>
   <button
    class="cursor-pointer p-1.5 text-zinc-400 hover:text-white hover:bg-zinc-600 rounded-lg transition-colors"
    title="Upravit fakturu"
    on:click={() => onEdit(invoice)}
   >
    <svg
     xmlns="http://www.w3.org/2000/svg"
     fill="none"
     viewBox="0 0 24 24"
     stroke-width="1.5"
     stroke="currentColor"
     class="w-5 h-5"
    >
     <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"
     />
    </svg>
   </button>
   <button
    class="cursor-pointer p-1.5 text-zinc-400 hover:text-red-500 hover:bg-zinc-600 rounded-lg transition-colors"
    title="Smazat fakturu"
    on:click={handleDelete}
   >
    <svg
     xmlns="http://www.w3.org/2000/svg"
     fill="none"
     viewBox="0 0 24 24"
     stroke-width="1.5"
     stroke="currentColor"
     class="w-5 h-5"
    >
     <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
     />
    </svg>
   </button>
  </div>
 </div>

 {#if expanded}
  <div class="mt-3 sm:mt-4 border-t border-zinc-600 pt-3 sm:pt-4">
   <div class="space-y-2">
    {#each items as item}
     <div
      class="flex flex-col sm:flex-row justify-between text-xs sm:text-base gap-1"
     >
      <div>
       <span class="text-zinc-300">{item.name}</span>
       <span class="text-zinc-500 ml-2">
        {item.quantity}
        {item.units} × {item.price_per_unit.toLocaleString("cs-CZ")} Kč
       </span>
      </div>
      <div class="text-zinc-300">
       {(item.quantity * item.price_per_unit).toLocaleString("cs-CZ")} Kč
      </div>
     </div>
    {/each}
   </div>
  </div>
 {/if}
</div>

{#if showDeleteConfirmation}
 <DeleteInvoiceConfirmation
  {invoice}
  onClose={() => (showDeleteConfirmation = false)}
 />
{/if}
