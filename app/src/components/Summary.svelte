<script>
 export let data;

 const now = new Date();
 const thisYear = now.getFullYear();
 const thisMonth = now.getMonth();
 const lastMonth = thisMonth - 1;

 const DAYS_AHEAD = 7;
 const futureDate = new Date(now.getTime() + DAYS_AHEAD * 24 * 60 * 60 * 1000);

 $: invoices = data?.invoices || [];

 $: unpaidTotal = invoices
  .filter((inv) => !inv.acf.paid)
  .reduce((sum, inv) => {
   const items = JSON.parse(inv.acf.items)?.items || [];
   return (
    sum +
    items.reduce(
     (total, item) => total + (item.price_per_unit || 0) * (item.quantity || 0),
     0,
    )
   );
  }, 0);

 $: unpaidAfterMaturity = invoices
  .filter((inv) => !inv.acf.paid && new Date(inv.acf.maturity) < now)
  .reduce((sum, inv) => {
   const items = JSON.parse(inv.acf.items)?.items || [];
   return (
    sum +
    items.reduce(
     (total, item) => total + (item.price_per_unit || 0) * (item.quantity || 0),
     0,
    )
   );
  }, 0);

 $: paidThisYear = invoices
  .filter(
   (inv) =>
    inv.acf.paid && new Date(inv.created_date).getFullYear() === thisYear,
  )
  .reduce((sum, inv) => {
   const items = JSON.parse(inv.acf.items)?.items || [];
   return (
    sum +
    items.reduce(
     (total, item) => total + (item.price_per_unit || 0) * (item.quantity || 0),
     0,
    )
   );
  }, 0);

 $: paidThisMonth = invoices
  .filter(
   (inv) =>
    inv.acf.paid &&
    new Date(inv.created_date).getMonth() === thisMonth &&
    new Date(inv.created_date).getFullYear() === thisYear,
  )
  .reduce((sum, inv) => {
   const items = JSON.parse(inv.acf.items)?.items || [];
   return (
    sum +
    items.reduce(
     (total, item) => total + (item.price_per_unit || 0) * (item.quantity || 0),
     0,
    )
   );
  }, 0);

 $: paidLastMonth = invoices
  .filter(
   (inv) =>
    inv.acf.paid &&
    new Date(inv.created_date).getMonth() === lastMonth &&
    new Date(inv.created_date).getFullYear() === thisYear,
  )
  .reduce((sum, inv) => {
   const items = JSON.parse(inv.acf.items)?.items || [];
   return (
    sum +
    items.reduce(
     (total, item) => total + (item.price_per_unit || 0) * (item.quantity || 0),
     0,
    )
   );
  }, 0);

 $: upcomingDue = invoices
  .filter(
   (inv) =>
    !inv.acf.paid &&
    new Date(inv.acf.maturity) > now &&
    new Date(inv.acf.maturity) <= futureDate,
  )
  .reduce((sum, inv) => {
   const items = JSON.parse(inv.acf.items)?.items || [];
   return (
    sum +
    items.reduce(
     (total, item) => total + (item.price_per_unit || 0) * (item.quantity || 0),
     0,
    )
   );
  }, 0);

 // Add reactive counts
 $: unpaidCount = invoices.filter((inv) => !inv.acf.paid).length;
 $: unpaidAfterMaturityCount = invoices.filter(
  (inv) => !inv.acf.paid && new Date(inv.acf.maturity) < now,
 ).length;
 $: paidThisYearCount = invoices.filter(
  (inv) =>
   inv.acf.paid && new Date(inv.created_date).getFullYear() === thisYear,
 ).length;
 $: paidThisMonthCount = invoices.filter(
  (inv) =>
   inv.acf.paid &&
   new Date(inv.created_date).getMonth() === thisMonth &&
   new Date(inv.created_date).getFullYear() === thisYear,
 ).length;
 $: paidLastMonthCount = invoices.filter(
  (inv) =>
   inv.acf.paid &&
   new Date(inv.created_date).getMonth() === lastMonth &&
   new Date(inv.created_date).getFullYear() === thisYear,
 ).length;
 $: upcomingDueCount = invoices.filter(
  (inv) =>
   !inv.acf.paid &&
   new Date(inv.acf.maturity) > now &&
   new Date(inv.acf.maturity) <= futureDate,
 ).length;
</script>

<div class="bg-zinc-800 p-4 sm:p-6 rounded-lg">
 <h2 class="text-xl sm:text-2xl mb-4">Přehled</h2>
 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
  <div>
   <p class="text-zinc-400">Vystavené faktury</p>
   <p class="text-2xl sm:text-3xl font-bold">
    {unpaidTotal.toLocaleString("cs-CZ")} Kč
   </p>
   <p class="text-base text-zinc-500">Počet: {unpaidCount}</p>
  </div>
  <div>
   <p class="text-zinc-400">Splatné tento týden</p>
   <p class="text-2xl sm:text-3xl font-bold text-green-500">
    {upcomingDue.toLocaleString("cs-CZ")} Kč
   </p>
   <p class="text-base text-zinc-500">Počet: {upcomingDueCount}</p>
  </div>
  <div>
   <p class="text-zinc-400">Po splatnosti</p>
   <p class="text-2xl sm:text-3xl font-bold text-red-500">
    {unpaidAfterMaturity.toLocaleString("cs-CZ")} Kč
   </p>
   <p class="text-base text-zinc-500">Počet: {unpaidAfterMaturityCount}</p>
  </div>
  <div>
   <p class="text-zinc-400">Zaplaceno tento měsíc</p>
   <p class="text-2xl sm:text-3xl font-bold">
    {paidThisMonth.toLocaleString("cs-CZ")} Kč
   </p>
   <p class="text-base text-zinc-500">Počet: {paidThisMonthCount}</p>
  </div>
  <div>
   <p class="text-zinc-400">Zaplaceno minulý měsíc</p>
   <p class="text-2xl sm:text-3xl font-bold">
    {paidLastMonth.toLocaleString("cs-CZ")} Kč
   </p>
   <p class="text-base text-zinc-500">Počet: {paidLastMonthCount}</p>
  </div>
  <div>
   <p class="text-zinc-400">Zaplaceno tento rok</p>
   <p class="text-2xl sm:text-3xl font-bold">
    {paidThisYear.toLocaleString("cs-CZ")} Kč
   </p>
   <p class="text-base text-zinc-500">Počet: {paidThisYearCount}</p>
  </div>
 </div>
</div>
