<script>
 export let customer;
 export let stats;
 export let expanded;
 export let onShowDetails;
 export let onEdit;
 export let onDelete;

 function handleShowDetails() {
  onShowDetails(customer.id);
 }
</script>

<div class="bg-zinc-700 rounded-lg overflow-hidden">
 <div class="p-4">
  <div class="flex flex-col sm:flex-row justify-between items-start gap-1">
   <div>
    <h3 class="font-bold text-xl">{customer.title}</h3>
    <p class="text-base text-zinc-400">Počet faktur: {stats.count}</p>
   </div>
   <p class="font-bold text-xl">{stats.total.toLocaleString("cs-CZ")} Kč</p>
  </div>

  <div class="flex justify-end gap-2 mt-4">
   <button
    class="cursor-pointer p-1.5 text-zinc-400 hover:text-white hover:bg-zinc-600 rounded-lg transition-colors"
    on:click={handleShowDetails}
    title="Zobrazit detail"
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
    title="Upravit zákazníka"
    on:click={() => onEdit(customer)}
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
    title="Smazat zákazníka"
    on:click={() => onDelete(customer)}
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
  <div class="p-4 pt-0 border-t border-zinc-600">
   <div class="mt-2 text-base text-zinc-400 space-y-1">
    <p>IČO: {customer.acf.ico}</p>
    <p>DIČ: {customer.acf.dic}</p>
    <p>{customer.acf.street}</p>
    <p>{customer.acf.psc} {customer.acf.city}</p>
    <p>{customer.acf.country}</p>
    {#if customer.acf.email}
     <p class="text-blue-400">{customer.acf.email}</p>
    {/if}
   </div>
  </div>
 {/if}
</div>
