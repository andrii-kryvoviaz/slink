import Title from './card-title.svelte';
import Root from './card.svelte';

export {
  Root,
  Title,
  //
  Root as CardRoot,
  Title as CardTitle,
};

export { cardTheme, cardTitleTheme } from './card.theme';

export type { CardVariants, CardTitleVariants } from './card.theme';
