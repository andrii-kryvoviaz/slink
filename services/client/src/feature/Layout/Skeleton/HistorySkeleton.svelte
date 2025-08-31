<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';

  interface Props {
    count?: number;
    class?: string;
  }

  let { count = 4, class: customClass = '' }: Props = $props();

  const skeletonItems = Array(count).fill(null);
</script>

<div class="flex flex-col items-center gap-8 px-4 sm:px-0 {customClass}">
  {#each skeletonItems as _, index}
    <div
      class="group relative w-full max-w-full overflow-hidden rounded-xl border border-gray-200/50 bg-white/80 backdrop-blur-sm dark:border-gray-700/50 dark:bg-gray-900/80 sm:w-4xl"
      style="animation-delay: {index * 150}ms"
    >
      <div class="sm:p-6">
        <div
          class="flex max-w-full grow flex-col items-center gap-0 sm:flex-row sm:items-start sm:gap-8"
        >
          <div
            class="flex w-full shrink-0 overflow-hidden rounded-t-xl max-w-full sm:rounded-lg sm:w-60 sm:max-w-none"
          >
            <Skeleton
              width="100%"
              height="240px"
              rounded="none"
              class="sm:w-60 sm:h-60 rounded-t-xl sm:rounded-lg"
            />
          </div>

          <div class="flex w-full grow flex-col gap-4 p-6 sm:p-0">
            <div class="hidden sm:block">
              <div class="flex items-center gap-2">
                <Skeleton width="200px" height="20px" />
                <Skeleton width="16px" height="16px" rounded="sm" />
              </div>
            </div>

            <div class="mb-2">
              <div class="flex flex-wrap items-center gap-2">
                <Skeleton
                  width="100px"
                  height="36px"
                  rounded="lg"
                  class="sm:h-8"
                />
                <Skeleton
                  width="36px"
                  height="36px"
                  rounded="lg"
                  class="sm:h-8 sm:w-8"
                />
                <Skeleton
                  width="36px"
                  height="36px"
                  rounded="lg"
                  class="sm:h-8 sm:w-8"
                />
                <Skeleton
                  width="36px"
                  height="36px"
                  rounded="lg"
                  class="sm:h-8 sm:w-8"
                />
              </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
              <div
                class="flex items-center gap-3 rounded-md bg-gray-50/30 px-3 py-2 dark:bg-gray-800/20"
              >
                <div
                  class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100/50 dark:bg-gray-700/30"
                >
                  <Skeleton width="12px" height="12px" rounded="sm" />
                </div>
                <div class="flex flex-col min-w-0 gap-1">
                  <Skeleton width="24px" height="10px" />
                  <Skeleton width="60px" height="12px" />
                </div>
              </div>

              <div
                class="flex items-center gap-3 rounded-md bg-gray-50/30 px-3 py-2 dark:bg-gray-800/20"
              >
                <div
                  class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100/50 dark:bg-gray-700/30"
                >
                  <Skeleton width="12px" height="12px" rounded="sm" />
                </div>
                <div class="flex flex-col min-w-0 gap-1">
                  <Skeleton width="48px" height="10px" />
                  <Skeleton width="55px" height="12px" />
                </div>
              </div>

              <div
                class="flex items-center gap-3 rounded-md bg-gray-50/30 px-3 py-2 dark:bg-gray-800/20"
              >
                <div
                  class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100/50 dark:bg-gray-700/30"
                >
                  <Skeleton width="12px" height="12px" rounded="sm" />
                </div>
                <div class="flex flex-col min-w-0 gap-1">
                  <Skeleton width="20px" height="10px" />
                  <Skeleton width="40px" height="12px" />
                </div>
              </div>

              <div
                class="flex items-center gap-3 rounded-md bg-gray-50/30 px-3 py-2 dark:bg-gray-800/20"
              >
                <div
                  class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100/50 dark:bg-gray-700/30"
                >
                  <Skeleton width="12px" height="12px" rounded="sm" />
                </div>
                <div class="flex flex-col min-w-0 gap-1">
                  <Skeleton width="40px" height="10px" />
                  <Skeleton width="70px" height="12px" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  {/each}
</div>
