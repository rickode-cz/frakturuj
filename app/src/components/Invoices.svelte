<script>
 import { selectedYear } from "../lib/stores";
 import InvoiceItem from "./InvoiceItem.svelte";
 import InvoiceForm from "./InvoiceForm.svelte";
 export let data;

 $: invoices = data?.invoices || [];
 $: customers = data?.customers || [];

 // Get unique years from invoices
 $: years = [
  ...new Set(invoices.map((inv) => new Date(inv.created_date).getFullYear())),
 ].sort((a, b) => b - a); // Sort descending

 // Set initial year when years are available
 $: if (years.length && !years.includes($selectedYear)) {
  selectedYear.set(years[0]);
 }

 // Filter invoices by selected year and sort by created date desc
 $: filteredInvoices = invoices
  .filter((inv) => new Date(inv.created_date).getFullYear() === $selectedYear)
  .sort((a, b) => new Date(b.created_date) - new Date(a.created_date));

 // Calculate yearly totals
 $: yearlyStats = {
  count: filteredInvoices.length,
  total: filteredInvoices.reduce((sum, inv) => {
   const items = JSON.parse(inv.acf.items)?.items || [];
   return (
    sum +
    items.reduce(
     (total, item) => total + (item.price_per_unit || 0) * (item.quantity || 0),
     0,
    )
   );
  }, 0),
 };

 function getCustomerName(customerId) {
  return customers.find((c) => c.id === customerId)?.title || "Unknown";
 }

 let showInvoiceForm = false;
 let editingInvoice = null;
 let duplicatingInvoice = null;

 function handleEdit(invoice) {
  editingInvoice = invoice;
  showInvoiceForm = true;
 }

 function handleCloseForm() {
  showInvoiceForm = false;
  editingInvoice = null;
  duplicatingInvoice = null;
 }

 // Track expanded state for each invoice
 let expanded = {};

 function showDetails(id) {
  expanded[id] = !expanded[id];
  expanded = expanded;
 }

 function handleDuplicate(invoice) {
  const duplicateData = {
   acf: {
    customer: invoice.acf.customer,
    items: invoice.acf.items
   }
  };
  editingInvoice = duplicateData;
  duplicatingInvoice = true;
  showInvoiceForm = true;
 }
</script>

<div class="bg-zinc-800 p-4 sm:p-6 rounded-lg">
 <div
  class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-0 mb-6"
 >
  <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
   <h2 class="text-xl sm:text-2xl">Faktury</h2>
   <button
    class="cursor-pointer px-4 py-2 bg-green-600 rounded hover:bg-green-700 flex items-center gap-2"
    on:click={() => (showInvoiceForm = true)}
   >
    <svg
     xmlns="http://www.w3.org/2000/svg"
     class="h-5 w-5"
     viewBox="0 0 20 20"
     fill="currentColor"
    >
     <path
      fill-rule="evenodd"
      d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
      clip-rule="evenodd"
     />
    </svg>
    Nová faktura
   </button>
  </div>
  <div class="flex flex-wrap gap-2 w-full sm:w-auto">
   {#each years as year}
    <button
     class="flex-1 sm:flex-none px-3 sm:px-4 py-2 rounded cursor-pointer text-base sm:text-base
          {$selectedYear === year
      ? 'bg-zinc-600 text-white'
      : 'text-zinc-400 hover:bg-zinc-700'}"
     on:click={() => selectedYear.set(year)}
    >
     {year}
    </button>
   {/each}
  </div>
 </div>

 <div class="space-y-4">
  {#each filteredInvoices as invoice}
   <InvoiceItem
    {invoice}
    customerName={getCustomerName(invoice.acf.customer)}
    expanded={expanded[invoice.id]}
    onShowDetails={showDetails}
    onEdit={handleEdit}
    onDuplicate={() => {handleDuplicate(invoice)}}
   />
  {/each}
 </div>

 <div class="bg-zinc-700/50 p-4 rounded-lg mt-6">
  <div class="flex justify-between items-center">
   <p class="text-zinc-400">Celkem za rok {$selectedYear}</p>
   <div class="text-right">
    <p class="text-2xl sm:text-3xl font-bold">
     {yearlyStats.total.toLocaleString("cs-CZ")} Kč
    </p>
    <p class="text-base text-zinc-400">Počet faktur: {yearlyStats.count}</p>
   </div>
  </div>
 </div>

 {#if showInvoiceForm}
  <InvoiceForm
   isOpen={showInvoiceForm}
   onClose={handleCloseForm}
   editData={editingInvoice}
   duplicatingInvoice={duplicatingInvoice}
  />
 {/if}
</div>
