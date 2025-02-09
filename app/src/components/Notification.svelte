<script>
  import { notification } from '../lib/stores';
  import { onMount } from 'svelte';

  let timeoutId;

  $: if ($notification) {
    if (timeoutId) clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
      notification.set(null);
    }, 2000);
  }

  onMount(() => {
    return () => {
      if (timeoutId) clearTimeout(timeoutId);
    };
  });
</script>

{#if $notification}
  <div
    class="fixed top-4 right-4 left-4 md:left-auto md:right-4 md:w-96 p-4 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-y-0 opacity-100"
    class:bg-green-500={$notification.type === 'success'}
    class:bg-red-500={$notification.type === 'error'}
    role="alert"
  >
    <div class="flex items-center gap-3">
      {#if $notification.type === 'success'}
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
      {:else}
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
      {/if}
      <p class="text-white font-medium">{$notification.message}</p>
    </div>
  </div>
{/if}
