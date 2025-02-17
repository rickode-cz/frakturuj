<script>
 import { selectedYear } from "../lib/stores";
 import InvoiceItem from "./InvoiceItem.svelte";
 import InvoiceForm from "./InvoiceForm.svelte";
 export let data;

 // Filter state
 let selectedCustomer = "";
 let selectedMonth = "";
 let selectedStatus = ""; // all/paid/unpaid/overdue
 let searchQuery = "";

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

 // Enhanced filtering with all filters
 $: filteredInvoices = invoices
  .filter((inv) => new Date(inv.created_date).getFullYear() === $selectedYear)
  .filter((inv) => !selectedCustomer || inv.acf.customer === selectedCustomer)
  .filter((inv) => {
    if (selectedMonth === "") return true;
    return new Date(inv.created_date).getMonth() === parseInt(selectedMonth);
  })
  .filter((inv) => {
    if (!selectedStatus) return true;
    const now = new Date();
    const maturity = new Date(inv.acf.maturity);
    
    switch(selectedStatus) {
      case 'paid': return inv.acf.paid;
      case 'unpaid': return !inv.acf.paid && maturity >= now;
      case 'overdue': return !inv.acf.paid && maturity < now;
      default: return true;
    }
  })
  .filter((inv) => {
    if (!searchQuery) return true;
    const query = searchQuery.toLowerCase();
    // Search in items
    try {
      const items = JSON.parse(inv.acf.items)?.items || [];
      return items.some(item => 
        item.name.toLowerCase().includes(query) ||
        (item.units || "").toLowerCase().includes(query)
      );
    } catch (e) {
      return false;
    }
  })
  .sort((a, b) => new Date(b.created_date) - new Date(a.created_date));

 // Get months for dropdown
 $: months = [...new Set(
   invoices
    .filter(inv => new Date(inv.created_date).getFullYear() === $selectedYear)
    .map(inv => new Date(inv.created_date).getMonth())
 )].sort((a, b) => a - b);

 // Clear month selection when year changes to prevent invalid filtering
 $: if ($selectedYear) {
  selectedMonth = "";
 }

 // Format month number to name
 function getMonthName(monthNumber) {
   return new Date(2000, monthNumber).toLocaleString('cs-CZ', { month: 'long' });
 }

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

 function resetFilters() {
  selectedCustomer = "";
  selectedMonth = "";
  selectedStatus = "";
  searchQuery = "";
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

 <!-- Filters -->
 <div class="flex flex-col sm:flex-row gap-4 mb-6">
  <div class="flex-1 flex flex-col sm:flex-row gap-4">
   <select
    bind:value={selectedCustomer}
    class="bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
   >
    <option value="">Všichni zákazníci</option>
    {#each customers as customer}
     <option value={customer.id}>{customer.title}</option>
    {/each}
   </select>

   <select
    bind:value={selectedMonth}
    class="bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
   >
    <option value="">Všechny měsíce</option>
    {#each months as month}
     <option value={month}>{getMonthName(month)}</option>
    {/each}
   </select>

   <select
    bind:value={selectedStatus}
    class="bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
   >
    <option value="">Všechny stavy</option>
    <option value="paid">Zaplacené</option>
    <option value="unpaid">Vystavené</option>
    <option value="overdue">Po splatnosti</option>
   </select>
  </div>

  {#if selectedCustomer || selectedMonth || selectedStatus || searchQuery}
    <button
      class="px-3 py-2 bg-red-900 text-red-300 rounded flex items-center gap-2 whitespace-nowrap"
      on:click={resetFilters}
      title="Zrušit filtry"
    >
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
      </svg>
    </button>
  {/if}
</div>

<div class="flex gap-4 mb-6">
  <div class="relative flex-1">
    <input
      type="text"
      bind:value={searchQuery}
      placeholder="Hledat v položkách faktury..."
      class="w-full bg-zinc-700 rounded pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
    />
    <svg 
      xmlns="http://www.w3.org/2000/svg" 
      fill="none" 
      viewBox="0 0 24 24" 
      stroke-width="1.5" 
      stroke="currentColor" 
      class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400"
    >
      <path 
        stroke-linecap="round" 
        stroke-linejoin="round" 
        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" 
      />
    </svg>
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
