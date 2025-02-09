<script>
 import { profile, isProfileModalOpen, apiData } from "../lib/stores";
 import ProfileForm from "./ProfileForm.svelte";

 function openProfileModal() {
  isProfileModalOpen.set(true);
 }

 $: hasProfile = $profile.title && $profile.ico;
 $: logoutUrl = $apiData?.wp_logout_url;
</script>

<div class="bg-zinc-800 p-4 sm:p-6 rounded-lg">
 {#if hasProfile}
  <div
   class="flex flex-col sm:flex-row justify-between items-center sm:items-start gap-4 sm:gap-0"
  >
   <div class="text-center sm:text-left">
    <h2 class="text-2xl">{$profile.title}</h2>
    <p class="text-base text-zinc-400 mt-1">IČO: {$profile.ico}</p>
   </div>
   <div class="flex flex-row sm:flex-col items-center sm:items-end gap-2">
    <button
     class="cursor-pointer px-4 py-2 bg-blue-600 rounded hover:bg-blue-700"
     on:click={openProfileModal}
    >
     Upravit profil
    </button>
    {#if logoutUrl}
     <a
      href={logoutUrl}
      class="px-5.5 py-2 bg-red-500 rounded hover:bg-red-700 text-center"
     >
      Odhlásit se
     </a>
    {/if}
   </div>
  </div>
 {:else}
  <div class="text-center">
   <h2 class="text-2xl mb-4">Fakturační údaje</h2>
   <p class="text-zinc-400 mb-4">
    Pro vytváření faktur je potřeba vyplnit vaše fakturační údaje
   </p>

   <div class="flex flex-row justify-center items-center gap-2">
   <button
    class="px-6 py-3 bg-green-600 rounded hover:bg-green-700 font-medium"
    on:click={openProfileModal}
   >
    Vyplnit údaje
   </button>
   {#if logoutUrl}
   <a
    href={logoutUrl}
    class="px-6 py-3 bg-red-500 rounded hover:bg-red-700 text-center"
   >
    Odhlásit se
   </a>
  {/if}
 </div>
  </div>
 {/if}
</div>

{#if $isProfileModalOpen}
 <ProfileForm />
{/if}
