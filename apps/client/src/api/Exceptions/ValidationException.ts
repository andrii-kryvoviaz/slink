import type { Violation, ViolationResponse } from '@slink/api/Response';

import {
  type ErrorList,
  HttpException,
} from '@slink/api/Exceptions/HttpException';

export class ValidationException extends HttpException {
  private readonly _violations: Violation[];

  constructor(violationResponse: ViolationResponse) {
    super(violationResponse.message, 422);
    this._violations = violationResponse.violations;
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
