import {
  type ErrorList,
  HttpException,
} from '@slink/api/Exceptions/HttpException';
import type { Violation, ViolationResponse } from '@slink/api/Response';

export class ValidationException extends HttpException {
  private _violations: Violation[];

  constructor(violationResponse: ViolationResponse) {
    super(violationResponse.message, 422);
    this._violations = violationResponse.violations;
  }

  get violations(): Violation[] {
    return this._violations;
  }

  get errors(): ErrorList {
    return this._violations.reduce((errors: ErrorList, violation) => {
      errors[violation.property] = violation.message;
      return errors;
    }, {});
  }
}
