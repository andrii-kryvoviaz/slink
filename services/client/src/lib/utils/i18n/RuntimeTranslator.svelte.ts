type TranslatorFn = (message: string) => string;

export class RuntimeTranslator {
  _locale = $state('en');
  _translator: TranslatorFn = (message) => message;

  get locale(): string {
    return this._locale;
  }

  set locale(value: string) {
    this._locale = value;
  }

  registerTranslator(fn: TranslatorFn): void {
    this._translator = fn;
  }
}

export const runtimeTranslator = new RuntimeTranslator();

export function t(message: string): string {
  runtimeTranslator._locale;
  return runtimeTranslator._translator(message);
}
