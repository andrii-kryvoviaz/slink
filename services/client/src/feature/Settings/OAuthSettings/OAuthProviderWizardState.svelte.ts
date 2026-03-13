import { OAuthProviderFormState } from './OAuthProviderFormState.svelte';

type WizardStep = 'select' | 'configure';

export class OAuthProviderWizardState {
  private _step: WizardStep = $state('select');
  readonly formState: OAuthProviderFormState;

  constructor(formState: OAuthProviderFormState) {
    this.formState = formState;
  }

  get step() {
    return this._step;
  }

  selectProvider(slug: string) {
    this.formState.selectProvider(slug);
    this._step = 'configure';
  }

  goBack() {
    this.formState.initialize();
    this._step = 'select';
  }
}
