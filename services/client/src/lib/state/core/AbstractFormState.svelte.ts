import { get } from 'svelte/store';

import { ValidationException } from '@slink/api/Exceptions';
import type { RequestState } from '@slink/api/ReactiveState';

import { printErrorsAsToastMessage } from '@slink/lib/utils/ui/printErrorsAsToastMessage';

export abstract class AbstractFormState<TResult = void> {
  private _isOpen: boolean = $state(false);
  private _isSubmitting: boolean = $state(false);
  private _errors: Record<string, string> = $state({});
  private _onSuccess: ((result: TResult) => void) | null = null;

  get isOpen() {
    return this._isOpen;
  }

  get isSubmitting() {
    return this._isSubmitting;
  }

  get errors() {
    return this._errors;
  }

  protected open(onSuccess?: (result: TResult) => void) {
    this._isOpen = true;
    this._errors = {};
    this._onSuccess = onSuccess ?? null;
  }

  close() {
    this._isOpen = false;
    this._errors = {};
    this._onSuccess = null;
  }

  protected async runSubmit(
    action: RequestState<TResult>,
    ...args: unknown[]
  ): Promise<boolean> {
    this._isSubmitting = true;
    this._errors = {};

    await action.run(...args);

    this._isSubmitting = false;

    const error = get(action.error);

    if (error) {
      if (error instanceof ValidationException) {
        this.handleValidationError(error);
      } else {
        printErrorsAsToastMessage(error);
      }
      return false;
    }

    const onSuccess = this._onSuccess;
    this.close();
    onSuccess?.(get(action.data) as TResult);
    return true;
  }

  protected handleValidationError(error: ValidationException) {
    this._errors = error.errors as Record<string, string>;
  }

  protected setErrors(errors: Record<string, string>) {
    this._errors = errors;
  }
}
