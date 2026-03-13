<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';

  interface Props {
    count?: number;
  }

  let { count = 10 }: Props = $props();
</script>

<div
  class="rounded-xl border border-slate-200/60 dark:border-slate-700/40 bg-white dark:bg-slate-800/30 overflow-hidden"
>
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-800/50">
          <th class="h-10 px-4 w-[40px]"
            ><Skeleton width="16px" height="12px" /></th
          >
          <th class="h-10 px-4 w-[60px]"></th>
          <th class="h-10 px-4 min-w-[150px] text-left"
            ><Skeleton width="60px" height="12px" /></th
          >
          <th class="h-10 px-4 w-[80px] text-left"
            ><Skeleton width="30px" height="12px" /></th
          >
          <th class="h-10 px-4 w-[120px] text-left"
            ><Skeleton width="65px" height="12px" /></th
          >
          <th class="h-10 px-4 w-[90px] text-left"
            ><Skeleton width="28px" height="12px" /></th
          >
          <th class="h-10 px-4 w-[100px] text-left"
            ><Skeleton width="50px" height="12px" /></th
          >
          <th class="h-10 px-4 w-[70px] text-center"
            ><Skeleton width="35px" height="12px" /></th
          >
          <th class="h-10 px-4 w-[140px] text-left"
            ><Skeleton width="48px" height="12px" /></th
          >
          <th class="h-10 px-4 min-w-[150px] text-left"
            ><Skeleton width="28px" height="12px" /></th
          >
          <th class="h-10 px-4 w-[100px] text-right"
            ><Skeleton width="45px" height="12px" /></th
          >
        </tr>
      </thead>
      <tbody>
        {#each Array(count) as _, index}
          <tr
            class="border-t border-slate-200/60 dark:border-slate-700/40"
            style="animation-delay: {index * 50}ms"
          >
            <td class="h-14 pl-4"
              ><Skeleton width="16px" height="16px" rounded="sm" /></td
            >
            <td class="h-14 px-4"
              ><Skeleton width="40px" height="40px" rounded="md" /></td
            >
            <td class="h-14 px-4"><Skeleton width="120px" height="14px" /></td>
            <td class="h-14 px-4"><Skeleton width="50px" height="14px" /></td>
            <td class="h-14 px-4"><Skeleton width="80px" height="14px" /></td>
            <td class="h-14 px-4"><Skeleton width="50px" height="14px" /></td>
            <td class="h-14 px-4"
              ><Skeleton width="60px" height="22px" rounded="full" /></td
            >
            <td class="h-14 px-4 text-center"
              ><Skeleton width="30px" height="14px" /></td
            >
            <td class="h-14 px-4"><Skeleton width="90px" height="14px" /></td>
            <td class="h-14 px-4">
              <div class="flex gap-1.5">
                <Skeleton width="40px" height="20px" rounded="full" />
                <Skeleton width="35px" height="20px" rounded="full" />
              </div>
            </td>
            <td class="h-14 pr-4 text-right">
              <div class="flex gap-1 justify-end">
                <Skeleton width="28px" height="28px" rounded="md" />
                <Skeleton width="28px" height="28px" rounded="md" />
              </div>
            </td>
          </tr>
        {/each}
      </tbody>
    </table>
  </div>
</div>
