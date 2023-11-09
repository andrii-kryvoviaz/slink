import type {
  Violation,
  ViolationResponse,
} from '@slink/api/Response/Error/ViolationResponse';

export class ValidationException extends Error {
  private _violations: Violation[];

  constructor(violationResponse: ViolationResponse) {
    super(violationResponse.message);
    this._violations = violationResponse.violations;
  }

  get violations(): Violation[] {
    return this._violations;
  }
}
