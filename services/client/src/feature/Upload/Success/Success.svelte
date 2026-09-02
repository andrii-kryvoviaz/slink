<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  interface Props {
    isGuestUser?: boolean;
    onUploadAnother?: () => void;
  }

  let { isGuestUser = false, onUploadAnother }: Props = $props();
</script>

<div
  class="flex flex-col items-center justify-center p-8 text-center"
  in:fade={{ duration: 500 }}
>
  <div class="mb-6">
    <div
      class="inline-flex items-center justify-center w-16 h-16 bg-success-wash dark:bg-success-wash/20 rounded-full mb-4"
    >
      <Icon icon="ph:check-circle" class="h-8 w-8 text-success" />
    </div>

    <h2 class="text-2xl font-bold text-foreground mb-2">Upload Successful!</h2>

    <p class="text-lg text-foreground-muted mb-4">
      Your image has been successfully uploaded to Slink.
    </p>

    {#if isGuestUser}
      <div
        class="bg-info-wash dark:bg-info-wash/20 border border-info-border dark:border-info-border/30 rounded-lg p-4 mb-6"
      >
        <div class="flex items-start">
          <Icon icon="ph:info" class="h-5 w-5 text-info mt-0.5 mr-3 shrink-0" />
          <div class="text-left text-sm">
            <h3 class="font-medium text-info-text-strong mb-1">
              Guest Upload Notice
            </h3>
            <p class="text-info-text">
              To manage your images and access additional features, consider
              creating an account.
            </p>
          </div>
        </div>
      </div>
    {/if}
  </div>

  <div class="flex flex-col sm:flex-row gap-3 justify-center w-full max-w-md">
    <Button
      onclick={onUploadAnother}
      variant="primary"
      size="md"
      class="inline-flex items-center justify-center"
    >
      <Icon icon="ph:upload-simple" class="h-4 w-4 mr-2" />
      Upload Another Image
    </Button>

    {#if isGuestUser}
      <Button
        href="/profile/signup"
        variant="outline"
        size="md"
        class="inline-flex items-center justify-center"
      >
        <Icon icon="ph:user-plus" class="h-4 w-4 mr-2" />
        Create Account
      </Button>
    {:else}
      <Button
        href="/history"
        variant="outline"
        size="md"
        class="inline-flex items-center justify-center"
      >
        <Icon icon="ph:clock-clockwise" class="h-4 w-4 mr-2" />
        View History
      </Button>
    {/if}
  </div>
</div>
