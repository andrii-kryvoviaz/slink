import { ValidationException } from '@slink/api/Exceptions';

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

  protected async handleSubmit(
    action: () => Promise<TResult>,
  ): Promise<boolean> {
    this._isSubmitting = true;
    this._errors = {};

    try {
      const result = await action();
      const onSuccess = this._onSuccess;
      this.close();
      onSuccess?.(result);
      return true;
    } catch (error) {
      if (error instanceof ValidationException) {
        this.handleValidationError(error);
      } else {
        printErrorsAsToastMessage(error as Error);
      }
      return false;
    } finally {
      this._isSubmitting = false;
    }
  }

  protected handleValidationError(error: ValidationException) {
    this._errors = error.errors as Record<string, string>;
  }

  protected setErrors(errors: Record<string, string>) {
    this._errors = errors;
  }
}
