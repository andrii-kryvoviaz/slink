import { get } from 'svelte/store';

import { ValidationException } from '@slink/api/Exceptions';
import type { RequestState } from '@slink/api/ReactiveState';

import { printErrorsAsToastMessage } from '@slink/lib/utils/ui/printErrorsAsToastMessage';

export abstract class AbstractFormState<TResult = void> {
  private _isOpen: boolean = $state(false);
  private _isSubmitting: boolean = $state(false);
  private _errors: Record<string, string> = $state({});
  private _onSuccess: ((result: TResult) => void) | null = null;
  private _onClose: (() => void) | null = null;

  get isOpen() {
    return this._isOpen;
  }

  get isSubmitting() {
    return this._isSubmitting;
  }

  get errors() {
    return this._errors;
  }

  protected open(onSuccess?: (result: TResult) => void, onClose?: () => void) {
    this._isOpen = true;
    this._errors = {};
    this._onSuccess = onSuccess ?? null;
    this._onClose = onClose ?? null;
  }

  private _reset() {
    this._isOpen = false;
    this._isSubmitting = false;
    this._errors = {};
    this._onSuccess = null;
    this._onClose = null;
  }

  close() {
    const onClose = this._onClose;
    this._reset();
    onClose?.();
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
    const onClose = this._onClose;
    this._reset();
    onSuccess?.(get(action.data) as TResult);
    onClose?.();
    return true;
  }

  protected handleValidationError(error: ValidationException) {
    this._errors = error.errors as Record<string, string>;
  }

  protected setErrors(errors: Record<string, string>) {
    this._errors = errors;
  }
}
