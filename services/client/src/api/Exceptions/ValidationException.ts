import {
  type ErrorList,
  HttpException,
} from '@slink/api/Exceptions/HttpException';
import type { Violation } from '@slink/api/Response';

import { t } from '@slink/lib/utils/i18n';

export class ValidationException extends HttpException {
  private readonly _violations: Violation[];

  constructor(response?: {
    title?: string;
    message?: string;
    violations?: Violation[];
  }) {
    super(response?.message ?? '', 422);
    this._violations = (response?.violations ?? []).map((v) => ({
      ...v,
      message: t(v.message),
    }));
  }

  get violations(): Violation[] {
    return this._violations;
  }

  get errors(): ErrorList {
    return this._violations.reduce((errors: ErrorList, violation) => {
      if (!violation.property.includes('.')) {
        errors[violation.property] = violation.message;
        return errors;
      }

      const properties = violation.property.split('.');
      const lastProperty = properties.pop()!;
      const nestedErrors = properties.reduce(
        (nestedErrors, property) => (nestedErrors[property] = {}),
        errors,
      );

      nestedErrors[lastProperty] = violation.message;

      return errors;
    }, {});
  }
}
