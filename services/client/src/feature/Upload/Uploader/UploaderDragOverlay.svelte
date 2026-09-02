<script lang="ts">
  import * as Dropzone from '@slink/ui/components/dropzone';

  import Icon from '@iconify/svelte';

  import { UploaderDragOverlayTheme } from './Uploader.theme';
</script>

<Dropzone.Overlay class={UploaderDragOverlayTheme()}>
  <div class="text-center">
    <Icon
      icon="ph:upload-simple"
      class="h-12 w-12 text-foreground-soft/80 mb-3 mx-auto"
    />
    <p class="text-lg font-light text-foreground-soft/90">Drop to upload</p>
  </div>
</Dropzone.Overlay>
