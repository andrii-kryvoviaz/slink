<script lang="ts">
  import { HashtagText } from '@slink/feature/Text';

  interface Props {
    text: string;
    onClose?: () => void;
  }

  let { text = '', onClose }: Props = $props();
</script>

<div class="text-sm text-white/80 leading-relaxed">
  <HashtagText {text} variant="glass" onBeforeNavigate={onClose} />
</div>
