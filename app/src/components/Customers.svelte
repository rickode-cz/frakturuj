<script>
  import { selectedYear } from '../lib/stores';
  import CustomerForm from './CustomerForm.svelte';
  import DeleteConfirmation from './DeleteConfirmation.svelte';
  import CustomerItem from './CustomerItem.svelte';
  export let data;
  
  $: customers = data?.customers || [];
  $: invoices = data?.invoices || [];

  // Track expanded state for each customer
  let expanded = {};

  // Calculate statistics for each customer for selected year
  function getCustomerStats(customerId) {
    // Convert ID to number for consistent comparison
    const customerIdNum = Number(customerId);
    
    const customerInvoices = invoices
      .filter(inv => 
        Number(inv.acf.customer) === customerIdNum && 
        inv.acf.maturity &&
        new Date(inv.acf.maturity).getFullYear() === $selectedYear
      );
    
    const total = customerInvoices.reduce((sum, inv) => {
      try {
        const items = JSON.parse(inv.acf.items)?.items || [];
        return sum + items.reduce((total, item) => 
          total + ((item.price_per_unit || 0) * (item.quantity || 0)), 0);
      } catch (e) {
        console.error('Failed to parse invoice items:', e);
        return sum;
      }
    }, 0);

    return {
      count: customerInvoices.length,
      total,
      invoices: customerInvoices
    };
  }

  function showDetails(id) {
    expanded[id] = !expanded[id];
    expanded = expanded;
  }

  let showCustomerForm = false;
  let editingCustomer = null;
  let deletingCustomer = null;

  function handleEdit(customer) {
    editingCustomer = customer;
    showCustomerForm = true;
  }

  function handleCloseForm() {
    showCustomerForm = false;
    editingCustomer = null;
  }

  function handleDelete(customer) {
    deletingCustomer = customer;
  }
</script>

<div class="bg-zinc-800 p-6 rounded-lg">
  <div class="flex items-center gap-x-4 mb-4">
    <h2 class="text-2xl">Zákazníci</h2>
    <button
      class="cursor-pointer px-4 py-2 bg-green-600 rounded hover:bg-green-700 flex items-center gap-2"
      on:click={() => showCustomerForm = true}
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
      </svg>
      Nový zákazník
    </button>
  </div>
  <div class="space-y-4">
    {#each customers as customer}
      {@const stats = getCustomerStats(customer.id)}
      <CustomerItem
        {customer}
        {stats}
        expanded={expanded[customer.id]}
        onShowDetails={showDetails}
        onEdit={handleEdit}
        onDelete={handleDelete}
      />
    {/each}
  </div>
</div>

{#if showCustomerForm}
  <CustomerForm 
    isOpen={showCustomerForm}
    onClose={handleCloseForm}
    editData={editingCustomer}
  />
{/if}

{#if deletingCustomer}
  <DeleteConfirmation 
    customer={deletingCustomer}
    onClose={() => deletingCustomer = null}
  />
{/if}
