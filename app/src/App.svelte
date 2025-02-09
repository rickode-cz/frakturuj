<script>
  import { onMount } from 'svelte';
  import { apiData, profile, isProfileModalOpen } from './lib/stores';
  import { fetchData, startNonceRefresh } from './lib/api';
  import Summary from './components/Summary.svelte';
  import Invoices from './components/Invoices.svelte';
  import Customers from './components/Customers.svelte';
  import Profile from './components/Profile.svelte';
  import Notification from './components/Notification.svelte';

  onMount(() => { 
    fetchData().then(data => {
      apiData.set(data);      
      if (data.profile) {
        profile.set(data.profile);
      }

      startNonceRefresh();
    });
  });
</script>

<div class="bg-zinc-900 min-h-screen text-white flex flex-col {$isProfileModalOpen ? 'overflow-hidden h-screen' : ''}">
  <Notification />
  <main class="container mx-auto px-4 py-8 flex-grow">
    <h1 class="text-3xl sm:text-4xl md:text-6xl lg:text-8xl font-bold uppercase font-serif tracking-widest text-center whitespace-nowrap">F<span class="inline-block -skew-10">r</span>akturuj</h1>
    
    {#if !$apiData}
      <p class="prose prose-xl md:prose-2xl prose-invert my-4 md:my-8 mx-auto text-center animate-pulse">
        Načítám si svoje serepetičky...
      </p>
    {:else}
      <div class="grid grid-cols-1 lg:grid-cols-3 items-start gap-4 md:gap-6 mb-4 md:mb-6 mt-6 md:mt-10">
        <div class="lg:col-span-2 order-2 lg:order-0">
          <Summary data={$apiData} />
        </div>
        <Profile />
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
        <Invoices data={$apiData} />
        <Customers data={$apiData} />
      </div>
    {/if}
  </main>
  
  <footer class="container mx-auto px-4 py-4 text-center text-zinc-600">
    by <a href="http://rickode.cz" target="_blank" rel="noopener" class="hover:text-zinc-400 transition-colors">Rickode</a>
  </footer>
</div>
