<script>
 import { notification, apiData } from "../lib/stores";
 import { createCustomer, updateCustomer } from "../lib/api";
 import { prefillByICO } from "../lib/ico";

 export let isOpen;
 export let onClose;
 export let editData = null; // New prop for edit mode

 let formData = {
  title: "",
  ico: "",
  dic: "",
  street: "",
  city: "",
  psc: "",
  country: "Česká republika",
  email: "",
 };

 // If editData is provided, populate form
 $: if (editData) {
  formData = {
   title: editData.title,
   ico: editData.acf.ico || "",
   dic: editData.acf.dic || "",
   street: editData.acf.street || "",
   city: editData.acf.city || "",
   psc: editData.acf.psc || "",
   country: editData.acf.country || "Česká republika",
   email: editData.acf.email || "",
  };
 }

 let loading = false;
 let saving = false;
 let error = null;

 async function handleICOLookup() {
  if (!formData.ico || formData.ico.length !== 8) {
   error = "IČO musí mít 8 číslic";
   return;
  }

  try {
   loading = true;
   error = null;
   const data = await prefillByICO(formData.ico);
   formData = { ...formData, ...data };
  } catch (e) {
   error = "Nepodařilo se načíst data z ARES";
  } finally {
   loading = false;
  }
 }

 // Format response to match API data structure
 function formatCustomerData(response) {
  const customer = response.customer;
  return {
   ...customer,
   id: Number(customer.id), // Ensure ID is number
   acf: {
    ico: customer.acf.ico || "",
    dic: customer.acf.dic || "",
    street: customer.acf.street || "",
    city: customer.acf.city || "",
    psc: customer.acf.psc || "",
    country: customer.acf.country || "",
    email: customer.acf.email || "",
   },
  };
 }

 async function handleSubmit() {
  try {
   saving = true;
   error = null;

   const response = editData
    ? await updateCustomer(Number(editData.id), formData)
    : await createCustomer(formData);

   const formattedCustomer = formatCustomerData(response);

   // Update customers in store with consistent ID types
   apiData.update((data) => ({
    ...data,
    customers: editData
     ? data.customers.map((c) =>
        Number(c.id) === Number(editData.id) ? formattedCustomer : c,
       )
     : [...data.customers, formattedCustomer],
   }));

   notification.set({
    type: "success",
    message: editData
     ? "Zákazník byl úspěšně upraven"
     : "Zákazník byl úspěšně vytvořen",
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

<!-- Modal backdrop -->
<div
 class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[998]"
 on:click={onClose}
></div>

<!-- Modal -->
<div
 class="fixed inset-4 md:inset-x-1/4 md:inset-y-24 bg-zinc-800 rounded-lg shadow-xl z-[999] overflow-y-auto"
>
 <div class="p-6 h-full flex flex-col">
  <div class="flex justify-between items-center mb-6">
   <h2 class="text-2xl">{editData ? "Upravit" : "Nový"} zákazník</h2>
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
   <div class="flex gap-4">
    <div class="flex-grow">
     <label for="customer_ico" class="block text-base text-zinc-400 mb-1"
      >IČO</label
     >
     <input
      id="customer_ico"
      type="text"
      bind:value={formData.ico}
      class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      placeholder="12345678"
     />
    </div>
    <div class="flex items-end">
     <button
      type="button"
      on:click={handleICOLookup}
      disabled={loading}
      class="cursor-pointer px-4 py-2 bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50"
     >
      {loading ? "Načítám..." : "Načíst"}
     </button>
    </div>
   </div>

   {#if error}
    <p class="text-red-500 text-base">{error}</p>
   {/if}

   <div>
    <label for="title" class="block text-base text-zinc-400 mb-1"
     >Název firmy/Jméno</label
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
    <label for="dic" class="block text-base text-zinc-400 mb-1">DIČ</label>
    <input
     id="dic"
     type="text"
     bind:value={formData.dic}
     class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
    />
   </div>

   <div>
    <label for="street" class="block text-base text-zinc-400 mb-1">Ulice</label>
    <input
     id="street"
     type="text"
     bind:value={formData.street}
     class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
     required
    />
   </div>

   <div class="grid grid-cols-2 gap-4">
    <div>
     <label for="city" class="block text-base text-zinc-400 mb-1">Město</label>
     <input
      id="city"
      type="text"
      bind:value={formData.city}
      class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      required
     />
    </div>
    <div>
     <label for="psc" class="block text-base text-zinc-400 mb-1">PSČ</label>
     <input
      id="psc"
      type="text"
      bind:value={formData.psc}
      class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      required
     />
    </div>
   </div>

   <div>
    <label for="country" class="block text-base text-zinc-400 mb-1">Země</label>
    <input
     id="country"
     type="text"
     bind:value={formData.country}
     class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
     required
    />
   </div>

   <div>
    <label for="email" class="block text-base text-zinc-400 mb-1">Email</label>
    <input
     id="email"
     type="email"
     bind:value={formData.email}
     class="w-full bg-zinc-700 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
    />
   </div>

   <div class="flex gap-4 pt-4 mt-auto">
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
      {editData ? "Uložit změny" : "Vytvořit zákazníka"}
     {/if}
    </button>
   </div>
  </form>
 </div>
</div>
