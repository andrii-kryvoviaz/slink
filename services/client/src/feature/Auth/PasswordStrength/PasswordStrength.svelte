<script lang="ts">
  import { slide } from 'svelte/transition';

  import {
    type StrengthLevel,
    passwordStrengthBarVariants,
    passwordStrengthLabelVariants,
    strengthLabels,
  } from './PasswordStrength.theme';

  interface Props {
    password: string;
  }

  let { password }: Props = $props();

  let strength = $derived.by((): { score: number; level: StrengthLevel } => {
    if (!password) return { score: 0, level: 'weak' };

    let score = 0;
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[^a-zA-Z0-9]/.test(password)) score++;

    const levels: StrengthLevel[] = [
      'weak',
      'fair',
      'good',
      'strong',
      'veryStrong',
    ];
    const level = levels[Math.min(score, 4)] || 'weak';

    return { score: Math.max(score, 1), level };
  });
</script>

{#if password}
  <div
    class="flex items-center gap-2 px-1"
    transition:slide={{ duration: 150 }}
  >
    <div class="flex-1 flex gap-1">
      {#each Array(5) as _, i}
        <div
          class={passwordStrengthBarVariants({
            strength: strength.level,
            active: i < strength.score,
          })}
        ></div>
      {/each}
    </div>
    <span class={passwordStrengthLabelVariants({ strength: strength.level })}>
      {strengthLabels[strength.level]}
    </span>
  </div>
{/if}
