<script>
 import { apiData, notification } from "../lib/stores";
 import { createInvoice, updateInvoice } from "../lib/api";

 export let isOpen;
 export let onClose;
 export let editData = null;
 export let duplicatingInvoice = null;

 let saving = false;
 let error = null;

 // Get customers for dropdown
 $: customers = $apiData?.customers || [];

 // Helper to get default maturity date
 function getDefaultMaturity() {
  const date = new Date();
  date.setDate(date.getDate() + 14);
  return date.toISOString().split("T")[0];
 }

 function addDaysToToday(days) {
  let date = new Date();

  if(formData.created_date) {
   date = new Date(formData.created_date);
  }

  date.setDate(date.getDate() + days);
  return date.toISOString().split("T")[0];
 }

 function setMaturityDate(days) {
  formData.maturity = addDaysToToday(days);
 }

 function generateTitle() {
  if (!$apiData?.invoices) { return '' }

  const year = new Date().getFullYear();
  const yearInvoices = $apiData.invoices.filter(
   (inv) => new Date(inv.created_date).getFullYear() === year,
  );
   return `${year}${String(yearInvoices.length + 1).padStart(4, "0")}`
 }

 let formData = {
  title: "",
  customer: "",
  maturity: getDefaultMaturity(),
  created_date: new Date().toISOString().split("T")[0],
  items: [],
 };

 // Set next invoice number and reset dates on new form
 if (!editData && $apiData?.invoices) {
  formData = {
   ...formData,
   title: generateTitle(),
   maturity: getDefaultMaturity(),
   created_date: new Date().toISOString().split("T")[0],
  };
 }

 // Add first empty item by default
 $: if (!formData.items.length) {
  formData.items = [createEmptyItem()];
 }

 // If editing, populate form
 $: if (editData) {
  const parsedItems = JSON.parse(editData?.acf?.items)?.items || [];
  formData = {
   title: editData?.title || generateTitle(),
   customer: editData?.acf?.customer,
   maturity: editData?.acf?.maturity || getDefaultMaturity(),
   created_date: editData?.created_date?.split(" ")[0] || new Date().toISOString().split("T")[0],
   items: parsedItems.length ? parsedItems : [createEmptyItem()],
  };
 }

 // Calculate totals
 $: itemTotals = formData.items.map(
  (item) => item.pricing_type === 'total' ? 
    (parseFloat(item.price_per_unit) || 0) : 
    (item.price_per_unit || 0) * (item.quantity || 0)
 );
 $: invoiceTotal = itemTotals.reduce((sum, total) => sum + total, 0);

 function createEmptyItem() {
  return {
   name: "",
   price_per_unit: "",
   units: "h",
   quantity: "1",
   pricing_type: "unit" // Add default pricing type (unit/total)
  };
 }

 // When switching to total pricing, clear the units field
 $: formData.items = formData.items.map(item => ({
  ...item,
  units: item.pricing_type === 'total' ? '' : item.units || 'h'
 }));

 function addItem() {
  formData.items = [...formData.items, createEmptyItem()];
 }

 function removeItem(index) {
  formData.items = formData.items.filter((_, i) => i !== index);
 }

 async function handleSubmit() {
  try {
   if (!formData.customer) {
    error = "Vyberte zákazníka";
    return;
   }

   saving = true;
   error = null;

   const submitData = {
    ...formData,
    items: JSON.stringify({ items: formData.items }),
   };

   const response = editData && !duplicatingInvoice
    ? await updateInvoice(editData.id, submitData)
    : await createInvoice(submitData);

   // Update store - handle duplication as new invoice
   apiData.update((data) => ({
    ...data,
    invoices: editData && !duplicatingInvoice
     ? data.invoices.map((inv) =>
        inv.id === editData?.id && !duplicatingInvoice ? response.invoice : inv
       )
     : [...data.invoices, response.invoice],
   }));

   notification.set({
    type: "success",
    message: editData && !duplicatingInvoice
     ? "Faktura byla upravena"
     : "Faktura byla vytvořena",
   });

   setTimeout(onClose, 500);
  } catch (e) {
   error = e.message;
   notification.set({
    type: "error",
    message: error,
   });
  } finally {
   saving = false;
  }
 }
</script>

<div
 class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[998]"
 on:click={onClose}
></div>

<div
 class="fixed inset-4 md:inset-x-1/4 md:inset-y-16 bg-zinc-800 rounded-lg shadow-xl z-[999] overflow-y-auto"
>
 <div class="p-6 flex flex-col h-full">
  <div class="flex justify-between items-center mb-6">
   <h2 class="text-2xl">{editData && !duplicatingInvoice ? "Upravit fakturu" : "Nová faktura"}</h2>
   <button
    class="cursor-pointer text-zinc-400 hover:text-white"
    on:click={onClose}
   >
    <svg
     xmlns="http://www.w3.org/2000/svg"
     fill="none"
     viewBox="0 0 24 24"
     stroke-width="1.5"
     stroke="currentColor"
     class="w-6 h-6"
    >
     <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M6 18L18 6M6 6l12 12"
     />
    </svg>
   </button>
  </div>

  <form
   on:submit|preventDefault={handleSubmit}
   class="space-y-4 flex-1 flex flex-col"
  >
   <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div>
     <label for="title" class="block text-base text-zinc-400 mb-1"
      >Číslo faktury</label
     >
     <input
      id="title"
      type="text"
      bind:value={formData.title}
      class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      required
     />
    </div>
    <div>
     <label for="created_date" class="block text-base text-zinc-400 mb-1">Datum vystavení</label>
     <input
      id="created_date"
      type="date"
      bind:value={formData.created_date}
      class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      required
     />
    </div>
    <div>
     <label for="customer" class="block text-base text-zinc-400 mb-1"
      >Zákazník</label
     >
     <select
      id="customer"
      bind:value={formData.customer}
      class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      required
     >
      <option value="">Vyberte zákazníka</option>
      {#each customers as customer}
       <option value={customer.id}>{customer.title}</option>
      {/each}
     </select>
    </div>
    <div>
     <label for="maturity" class="block text-base text-zinc-400 mb-1"
      >Datum splatnosti</label
     >
     {#if !editData || duplicatingInvoice}
      <div class="flex gap-2 mb-2">
       <button
        type="button"
        class="px-3 py-1 text-base bg-zinc-700 hover:bg-zinc-600 rounded"
        on:click={() => setMaturityDate(0)}
       >
        Dnes
       </button>
       <button
        type="button"
        class="px-3 py-1 text-base bg-zinc-700 hover:bg-zinc-600 rounded"
        on:click={() => setMaturityDate(10)}
       >
        10 dní
       </button>
       <button
        type="button"
        class="px-3 py-1 text-base bg-zinc-700 hover:bg-zinc-600 rounded"
        on:click={() => setMaturityDate(14)}
       >
        14 dní
       </button>
       <button
        type="button"
        class="px-3 py-1 text-base bg-zinc-700 hover:bg-zinc-600 rounded"
        on:click={() => setMaturityDate(30)}
       >
        Měsíc
       </button>
      </div>
     {/if}
     <input
      id="maturity"
      type="date"
      bind:value={formData.maturity}
      class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      required
     />
    </div>    
   </div>

   {#if error}
    <p class="text-red-500 text-base">{error}</p>
   {/if}

   <div class="border border-zinc-700 rounded-lg p-4">
    <h3 class="text-lg mb-4">Položky faktury</h3>

    <div class="space-y-6">
     {#each formData.items as item, i (i)}
      <div class="space-y-4">
       <!-- Name field - full width on mobile -->
       <div>
        <label class="block text-base text-zinc-400 mb-1">Název položky</label>
        <input
         type="text"
         bind:value={item.name}
         class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
         required
        />
       </div>

       <!-- Pricing type switcher -->
       <div class="flex gap-2 mb-2">
        <button
         type="button"
         class="px-3 py-1 text-base {item.pricing_type === 'unit' ? 'bg-blue-600' : 'bg-zinc-700 hover:bg-zinc-600'} rounded"
         on:click={() => item.pricing_type = 'unit'}
        >
         Cena za jednotku
        </button>
        <button
         type="button"
         class="px-3 py-1 text-base {item.pricing_type === 'total' ? 'bg-blue-600' : 'bg-zinc-700 hover:bg-zinc-600'} rounded"
         on:click={() => item.pricing_type = 'total'}
        >
         Cena souhrnně
        </button>
       </div>

       <!-- Units, Price, Quantity in grid -->
       <div class="grid grid-cols-2 sm:grid-cols-6 gap-4">
        {#if item.pricing_type === 'unit'}
         <div class="col-span-1 sm:col-span-2">
          <label class="block text-base text-zinc-400 mb-1">Cena/j.</label>
          <input
           type="number"
           bind:value={item.price_per_unit}
           class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
           min="0"
           step="1"
           required
          />
         </div>
         <div class="col-span-2 sm:col-span-2">
          <label class="block text-base text-zinc-400 mb-1">Počet</label>
          <input
           type="number"
           bind:value={item.quantity}
           class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
           min="0"
           step="0.1"
           required
          />
         </div>
         <div class="col-span-1 sm:col-span-2">
          <div class="flex items-end gap-2 h-full">
           <div class="flex-1">
            <label class="block text-base text-zinc-400 mb-1">Jednotka</label>
            <input
             type="text"
             bind:value={item.units}
             class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
             required
            />
           </div>
           <button
            type="button"
            class="cursor-pointer p-2 text-zinc-400 hover:text-red-500 hover:bg-zinc-600 rounded"
            on:click={() => removeItem(i)}
            disabled={formData.items.length === 1}
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
              d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"
             />
            </svg>
           </button>
          </div>
         </div>
        {:else}
         <div class="col-span-5 sm:col-span-5">
          <label class="block text-base text-zinc-400 mb-1">Celková cena</label>
          <input
           type="number"
           bind:value={item.price_per_unit}
           class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
           min="0"
           step="1"
           required
          />
         </div>
         <div class="col-span-1 sm:col-span-1 flex items-end">
          <button
           type="button"
           class="cursor-pointer p-2 text-zinc-400 hover:text-red-500 hover:bg-zinc-600 rounded"
           on:click={() => removeItem(i)}
           disabled={formData.items.length === 1}
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
             d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"
            />
           </svg>
          </button>
         </div>
        {/if}
       </div>

       <p class="text-right text-base text-zinc-400">
        Celkem: {itemTotals[i].toLocaleString("cs-CZ")} Kč
       </p>

       {#if i < formData.items.length - 1}
        <hr class="border-zinc-700 my-4" />
       {/if}
      </div>
     {/each}
    </div>

    <button
     type="button"
     class="cursor-pointer mt-4 w-full px-4 py-2 bg-zinc-700 rounded hover:bg-zinc-600 flex items-center justify-center gap-2"
     on:click={addItem}
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
       d="M12 4.5v15m7.5-7.5h-15"
      />
     </svg>
     Přidat položku
    </button>
   </div>

   <div class="flex justify-between items-center py-4 border-t border-zinc-700">
    <span class="text-lg">Celková částka:</span>
    <span class="text-2xl font-bold"
     >{invoiceTotal.toLocaleString("cs-CZ")} Kč</span
    >
   </div>

   <div class="flex gap-4 mt-auto">
    <button
     type="button"
     class="cursor-pointer flex-1 px-4 py-2 bg-zinc-600 rounded hover:bg-zinc-700 disabled:opacity-50"
     on:click={onClose}
     disabled={saving}
    >
     Zrušit
    </button>
    <button
     type="submit"
     class="cursor-pointer flex-1 px-4 py-2 bg-green-600 rounded hover:bg-green-700 disabled:opacity-50 flex justify-center items-center gap-2"
     disabled={saving}
    >
     {#if saving}
      <svg
       class="animate-spin h-5 w-5"
       xmlns="http://www.w3.org/2000/svg"
       fill="none"
       viewBox="0 0 24 24"
      >
       <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
       ></circle>
       <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
       ></path>
      </svg>
      Ukládám...
     {:else}
      {editData ? "Uložit změny" : "Vytvořit fakturu"}
     {/if}
    </button>
   </div>
  </form>
 </div>
</div>
